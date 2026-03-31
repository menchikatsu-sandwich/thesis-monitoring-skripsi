<?php

namespace App\Http\Controllers;

use App\Models\Thesis;
use App\Models\Logbook;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\SupabaseStorageService;

class DashboardController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseStorageService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function index()
    {
        $user = Auth::user();
        if ($user->isStudent()) return $this->studentDashboard($user);
        if ($user->isLecturer()) return $this->lecturerDashboard($user);
        return $this->adminDashboard();
    }

    private function studentDashboard($user)
    {
        $thesis = Thesis::where('student_id', $user->id)->first();
        
        // Jika belum punya data thesis, buat dummy untuk form awal
        if (!$thesis) {
            $thesis = new Thesis(['student_id' => $user->id, 'status' => 'pengajuan_awal']);
        }

        // Ambil history logbook
        $logbooks = Logbook::where('thesis_id', $thesis->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.student', compact('user', 'thesis', 'logbooks'));
    }

    private function lecturerDashboard($user)
    {
        // Ambil mahasiswa bimbingan (baik sebagai dospem 1 atau 2)
        $theses = Thesis::with(['student', 'lecturer1', 'lecturer2', 'logbooks'])
            ->where('lecturer_1_id', $user->id)
            ->orWhere('lecturer_2_id', $user->id)
            ->latest()
            ->get();

        return view('dashboard.lecturer', compact('user', 'theses'));
    }

    private function adminDashboard()
    {
        // 1. Menunggu Plotting (Pengajuan Awal)
        $waitingForPlotting = Thesis::with('student')
            ->whereIn('status', ['pengajuan_awal', 'menunggu_plotting'])
            ->latest()
            ->get();

        // 2. Sedang Bimbingan (Sudah ada dosen, belum ACC)
        $activeGuidance = Thesis::with(['student', 'lecturer1', 'lecturer2'])
            ->whereIn('status', ['pengerjaan_skripsi', 'bimbingan_aktif', 'perlu_revisi'])
            ->latest()
            ->get();

        // 3. Antrean Sidang Akhir (Sudah ACC, belum dijadwalkan/diluluskan)
        $queueForExam = Thesis::with(['student', 'lecturer1'])
            ->where('status', 'acc_pembimbing')
            ->latest()
            ->get();

        // 4. Menunggu Kelulusan (Sudah dijadwalkan/Siap Sidang, belum Lulus)
        $waitingForGraduation = Thesis::with(['student'])
            ->where('status', 'siap_sidang')
            ->orderBy('final_exam_date', 'asc')
            ->get();

        // Stats
        $stats = [
            'students' => User::where('role', 'student')->whereHas('Thesis', function ($query) {
                $query->WhereNotIn('status', ['lulus']);
            })->count(),
            'lecturers' => User::where('role', 'lecturer')->count(),
            'active_theses' => Thesis::whereNotIn('status', ['lulus', 'pengajuan_awal', 'acc_pembimbing', 'siap_sidang'])->count(),
            'graduated' => Thesis::where('status', 'lulus')->count(),
        ];

        $lecturers = User::where('role', 'lecturer')->get();

        return view('dashboard.admin', compact(
            'stats', 
            'waitingForPlotting', 
            'activeGuidance', 
            'queueForExam', 
            'waitingForGraduation', 
            'lecturers'
        ));
    }

    // --- ACTIONS ---

    // 1. Mahasiswa Submit Judul Awal
    public function submitProposal(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'abstract' => 'required|string',
        ]);

        $thesis = Thesis::updateOrCreate(
            ['student_id' => Auth::id()],
            [
                'title' => $request->title,
                'abstract' => $request->abstract,
                'status' => 'menunggu_plotting', // Langsung ganti status
                'proposal_date' => now() // Catat tanggal pengajuan
            ]
        );

        return back()->with('success', 'Proposal berhasil dikirim menunggu plotting dosen.');
    }

    // 2. Upload Draft (PDF) - Otomatis catat logbook
    public function uploadDraft(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf|max:10240',
            'thesis_id' => 'required|exists:theses,id'
        ]);

        $thesis = Thesis::findOrFail($request->thesis_id);
        
        // Upload ke Supabase
        $fileName = 'draft_' . time() . '_' . $request->file('file')->getClientOriginalName();
        $filePath = $this->supabase->uploadFile($request->file('file'), $fileName);

        if (!$filePath) {
            return back()->withErrors(['file' => 'Gagal upload ke storage.']);
        }

        // Update Thesis
        $newStatus = ($thesis->status === 'perlu_revisi') ? 'bimbingan_aktif' : $thesis->status;
        if (in_array($thesis->status, ['pengerjaan_skripsi'])) {
            $newStatus = 'bimbingan_aktif';
        }

        $thesis->update([
            'draft_file_path' => $filePath,
            'status' => $newStatus,
            'lecturer_notes' => null // Reset catatan lama saat upload baru
        ]);

        // CATAT OTOMATIS KE LOGBOOK (History Upload)
        Logbook::create([
            'thesis_id' => $thesis->id,
            'activity_type' => 'upload_draft',
            'file_path' => $filePath,
            'feedback' => null,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Draft berhasil diunggah & tercatat di logbook.');
    }

    // 3. Dosen Review (Revisi / ACC) - Otomatis catat logbook
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
                'lecturer_notes' => $request->notes ?? 'Alhamdulillah, ACC.',
                'proposal_date' => $thesis->proposal_date ?? now() // Ensure date exists
            ]);

            // Catat Logbook ACC
            Logbook::create([
                'thesis_id' => $thesis->id,
                'activity_type' => 'acc',
                'file_path' => $thesis->draft_file_path,
                'feedback' => $request->notes ?? 'ACC oleh Dosen Pembimbing',
                'status' => 'approved'
            ]);

            return back()->with('success', 'Skripsi telah di-ACC! Masuk antrean sidang.');
        } else {
            // Revisi
            $thesis->update([
                'status' => 'perlu_revisi', // JANGAN mundur ke pengajuan_awal
                'lecturer_notes' => $request->notes
            ]);

            // Catat Logbook Revisi (Feedback disimpan permanen disini)
            Logbook::create([
                'thesis_id' => $thesis->id,
                'activity_type' => 'revisi_dosen',
                'file_path' => $thesis->draft_file_path,
                'feedback' => $request->notes, // Simpan komentar dosen ke history
                'status' => 'rejected'
            ]);

            return back()->with('info', 'Draf dikembalikan untuk revisi. Cek logbook.');
        }
    }

    // 4. Admin Assign Dosen
    public function assignLecturers(Request $request)
    {
        $request->validate([
            'thesis_id' => 'required|exists:theses,id',
            'lecturer_1_id' => 'required|exists:users,id',
            'lecturer_2_id' => 'required|exists:users,id|different:lecturer_1_id', // Validasi beda dosen
        ]);

        $thesis = Thesis::findOrFail($request->thesis_id);
        $thesis->update([
            'lecturer_1_id' => $request->lecturer_1_id,
            'lecturer_2_id' => $request->lecturer_2_id,
            'status' => 'pengerjaan_skripsi', // Lanjut ke tahap pengerjaan
            'proposal_date' => now() // Set tanggal disetujui
        ]);

        return back()->with('success', 'Dosen pembimbing berhasil ditetapkan.');
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

    // 6. Admin Luluskan (New Feature)
    public function markAsGraduated(Request $request)
    {
        $request->validate([
            'thesis_id' => 'required|exists:theses,id',
        ]);

        $thesis = Thesis::findOrFail($request->thesis_id);
        $thesis->update([
            'status' => 'lulus'
        ]);

        return back()->with('success', 'Mahasiswa dinyatakan LULUS!');
    }
}