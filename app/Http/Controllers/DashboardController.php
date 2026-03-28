<?php

namespace App\Http\Controllers;

use App\Models\Thesis;
use App\Models\Logbook;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http; // Untuk Supabase Storage (jika pakai API langsung)

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isStudent()) {
            return $this->studentDashboard($user);
        } elseif ($user->isLecturer()) {
            return $this->lecturerDashboard($user);
        } else {
            return $this->adminDashboard();
        }
    }

    private function studentDashboard($user)
    {
        $thesis = $user->thesis;
        
        // Jika belum punya thesis, buat instance kosong
        if (!$thesis) {
            $thesis = new Thesis(['student_id' => $user->id, 'status' => 'pengajuan_awal']);
        }

        $logbooks = Logbook::where('thesis_id', $thesis->id)->latest()->take(5)->get();
        
        return view('dashboard.student', compact('user', 'thesis', 'logbooks'));
    }

    private function lecturerDashboard($user)
    {
        $theses = Thesis::with(['student', 'logbooks'])
            ->where('lecturer_1_id', $user->id)
            ->orWhere('lecturer_2_id', $user->id)
            ->get();

        $pendingLogbooks = Logbook::whereIn('thesis_id', $theses->pluck('id'))
            ->where('status', 'pending')
            ->count();

        return view('dashboard.lecturer', compact('user', 'theses', 'pendingLogbooks'));
    }

    private function adminDashboard()
    {
        $stats = [
            'students' => User::where('role', 'student')->count(),
            'lecturers' => User::where('role', 'lecturer')->count(),
            'active_theses' => Thesis::whereNotIn('status', ['lulus', 'mengunggu_plotting'])->count(),
            'graduated' => Thesis::where('status', 'lulus')->count(),
        ];

        // Ambil yang statusnya menunggu plotting dosen
        $unassignedTheses = Thesis::where('status', 'menunggu_plotting')
            ->with('student')
            ->get();

        // Ambil yang sudah ACC untuk antrean sidang
        $queueForExam = Thesis::with(['student', 'lecturer1'])
            ->where('status', 'acc_pembimbing')
            ->orderBy('updated_at', 'desc')
            ->get();

        $lecturers = User::where('role', 'lecturer')->get();

        return view('dashboard.admin', compact('stats', 'unassignedTheses', 'queueForExam', 'lecturers'));
    }

    // 1. Mahasiswa Submit Judul & Abstrak (Proposal Awal)
    public function submitProposal(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'abstract' => 'required|string',
        ]);

        $user = Auth::user();
        
        Thesis::updateOrCreate(
            ['student_id' => $user->id],
            [
                'title' => $request->title,
                'abstract' => $request->abstract,
                'status' => 'menunggu_plotting', // Status awal setelah submit
                'lecturer_1_id' => null,
                'lecturer_2_id' => null,
            ]
        );

        return back()->with('success', 'Proposal berhasil dikirim! Menunggu plotting dosen dari Admin.');
    }

    // 2. Upload Draft PDF (Saat Bimbingan)
    public function uploadDraft(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf|max:10240', // Max 10MB
            'thesis_id' => 'required|exists:theses,id'
        ]);

        $thesis = Thesis::findOrFail($request->thesis_id);
        
        // Hapus file lama jika ada (opsional, bisa juga disimpan versi sebelumnya)
        if ($thesis->draft_file_path) {
            Storage::disk('public')->delete($thesis->draft_file_path);
        }

        // Simpan ke storage/public/drafts
        $path = $request->file('file')->store('drafts', 'public');
        
        // Update status ke bimbingan_aktif atau perlu_revisi tergantung kondisi sebelumnya
        $newStatus = ($thesis->status == 'perlu_revisi') ? 'bimbingan_aktif' : $thesis->status;
        if ($newStatus == 'pengajuan_awal' || $newStatus == 'menunggu_plotting') {
             $newStatus = 'bimbingan_aktif';
        }

        $thesis->update([
            'draft_file_path' => $path,
            'status' => $newStatus,
            'lecturer_notes' => null // Reset catatan lama
        ]);

        return back()->with('success', 'Draft PDF berhasil diunggah! Menunggu review dosen.');
    }

    // 3. Dosen Review (Revisi / ACC)
    public function reviewThesis(Request $request)
    {
        $request->validate([
            'thesis_id' => 'required|exists:theses,id',
            'action' => 'required|in:revisi,acc',
            'notes' => 'nullable|string'
        ]);

        $thesis = Thesis::findOrFail($request->thesis_id);
        
        if ($request->action === 'acc') {
            $thesis->update([
                'status' => 'acc_pembimbing',
                'lecturer_notes' => $request->notes ?? 'Alhamdulillah, ACC untuk sidang.',
                'final_exam_date' => null, // Reset jadwal jika ada
            ]);
            return back()->with('success', 'Skripsi telah di-ACC! Masuk antrean sidang akhir.');
        } else {
            $thesis->update([
                'status' => 'perlu_revisi',
                'lecturer_notes' => $request->notes
            ]);
            return back()->with('info', 'Draf dikembalikan untuk revisi.');
        }
    }

    // 4. Admin Plotting Dosen
    public function assignLecturers(Request $request)
    {
        $request->validate([
            'thesis_id' => 'required|exists:theses,id',
            'lecturer_1_id' => 'required|exists:users,id',
            'lecturer_2_id' => 'nullable|exists:users,id',
        ]);

        $thesis = Thesis::findOrFail($request->thesis_id);
        $thesis->update([
            'lecturer_1_id' => $request->lecturer_1_id,
            'lecturer_2_id' => $request->lecturer_2_id,
            'status' => 'bimbingan_aktif', // Setelah di-plot, langsung aktif bimbingan
        ]);

        return back()->with('success', 'Dosen pembimbing berhasil ditetapkan!');
    }

    // 5. Admin Jadwal Sidang
    public function scheduleExam(Request $request)
    {
        $request->validate([
            'thesis_id' => 'required|exists:theses,id',
            'date' => 'required|date',
            'time' => 'required',
            'room' => 'required|string'
        ]);

        $thesis = Thesis::findOrFail($request->thesis_id);
        $thesis->update([
            'final_exam_date' => $request->date,
            'exam_time' => $request->time,
            'exam_room' => $request->room,
            'status' => 'siap_sidang'
        ]);

        return back()->with('success', 'Jadwal sidang berhasil ditetapkan!');
    }
}