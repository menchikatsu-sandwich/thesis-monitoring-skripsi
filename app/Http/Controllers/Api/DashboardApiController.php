<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Thesis;
use App\Models\Logbook;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\SupabaseStorageService;

class DashboardApiController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseStorageService $supabase)
    {
        $this->supabase = $supabase;
    }

    // =========================
    // DASHBOARD
    // =========================
    public function dashboard()
    {
        $user = Auth::user();

        if ($user->isStudent()) return $this->studentDashboard($user);
        if ($user->isLecturer()) return $this->lecturerDashboard($user);
        return $this->adminDashboard();
    }

    private function studentDashboard($user)
    {
        $thesis = Thesis::where('student_id', $user->id)->first();

        if (!$thesis) {
            $thesis = new Thesis([
                'student_id' => $user->id,
                'status' => 'pengajuan_awal'
            ]);
        }

        $logbooks = Logbook::where('thesis_id', $thesis->id)
            ->latest()->get();

        if (request()->wantsJson()) {
            return response()->json(compact('user','thesis','logbooks'));
        }

        return view('dashboard.student', compact('user','thesis','logbooks'));
    }

    private function lecturerDashboard($user)
    {
        $theses = Thesis::with(['student','lecturer1','lecturer2','logbooks'])
            ->where('lecturer_1_id', $user->id)
            ->orWhere('lecturer_2_id', $user->id)
            ->latest()->get();

        if (request()->wantsJson()) {
            return response()->json(compact('user','theses'));
        }

        return view('dashboard.lecturer', compact('user','theses'));
    }

    private function adminDashboard()
    {
        $waitingForPlotting = Thesis::with('student')
            ->whereIn('status',['pengajuan_awal','menunggu_plotting'])
            ->latest()->get();

        $activeGuidance = Thesis::with(['student','lecturer1','lecturer2'])
            ->whereIn('status',['pengerjaan_skripsi','bimbingan_aktif','perlu_revisi'])
            ->latest()->get();

        $queueForExam = Thesis::with(['student','lecturer1'])
            ->where('status','acc_pembimbing')
            ->latest()->get();

        $waitingForGraduation = Thesis::with('student')
            ->where('status','siap_sidang')
            ->orderBy('final_exam_date','asc')->get();

        $stats = [
            'students' => User::where('role','student')
                ->whereHas('thesis', fn($q)=>$q->whereNotIn('status',['lulus']))->count(),
            'lecturers' => User::where('role','lecturer')->count(),
            'active_theses' => Thesis::whereNotIn('status',[
                'lulus','pengajuan_awal','acc_pembimbing','siap_sidang'
            ])->count(),
            'graduated' => Thesis::where('status','lulus')->count(),
        ];

        $lecturers = User::where('role','lecturer')->get();

        if (request()->wantsJson()) {
            return response()->json(compact(
                'stats','waitingForPlotting','activeGuidance',
                'queueForExam','waitingForGraduation','lecturers'
            ));
        }

        return view('dashboard.admin', compact(
            'stats','waitingForPlotting','activeGuidance',
            'queueForExam','waitingForGraduation','lecturers'
        ));
    }

    // =========================
    // ACTIONS
    // =========================

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
                'status' => 'menunggu_plotting',
                'proposal_date' => now()
            ]
        );

        if ($request->wantsJson()) {
            return response()->json(['message'=>'Proposal berhasil','data'=>$thesis]);
        }

        return back()->with('success','Proposal berhasil dikirim');
    }

    public function uploadDraft(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf|max:10240',
            'thesis_id' => 'required|exists:theses,id'
        ]);

        $thesis = Thesis::findOrFail($request->thesis_id);

        $fileName = 'draft_'.time().'_'.$request->file('file')->getClientOriginalName();
        $filePath = $this->supabase->uploadFile($request->file('file'), $fileName);

        if (!$filePath) {
            return $request->wantsJson()
                ? response()->json(['error'=>'Upload gagal'],500)
                : back()->withErrors(['file'=>'Upload gagal']);
        }

        $newStatus = ($thesis->status === 'perlu_revisi') ? 'bimbingan_aktif' : $thesis->status;

        if (in_array($thesis->status,['pengerjaan_skripsi'])) {
            $newStatus = 'bimbingan_aktif';
        }

        $thesis->update([
            'draft_file_path'=>$filePath,
            'status'=>$newStatus,
            'lecturer_notes'=>null
        ]);

        Logbook::create([
            'thesis_id'=>$thesis->id,
            'activity_type'=>'upload_draft',
            'file_path'=>$filePath,
            'status'=>'pending'
        ]);

        return $request->wantsJson()
            ? response()->json(['message'=>'Draft uploaded'])
            : back()->with('success','Draft uploaded');
    }

    public function reviewThesis(Request $request)
    {
        $request->validate([
            'thesis_id'=>'required|exists:theses,id',
            'action'=>'required|in:revisi,acc',
            'notes'=>'nullable|string'
        ]);

        $thesis = Thesis::findOrFail($request->thesis_id);

        if ($request->action === 'acc') {
            $thesis->update([
                'status'=>'acc_pembimbing',
                'lecturer_notes'=>$request->notes ?? 'ACC',
                'proposal_date'=>$thesis->proposal_date ?? now()
            ]);
        } else {
            $thesis->update([
                'status'=>'perlu_revisi',
                'lecturer_notes'=>$request->notes
            ]);
        }

        return $request->wantsJson()
            ? response()->json(['message'=>'Review berhasil'])
            : back()->with('success','Review berhasil');
    }

    public function assignLecturers(Request $request)
    {
        $request->validate([
            'thesis_id'=>'required|exists:theses,id',
            'lecturer_1_id'=>'required|exists:users,id',
            'lecturer_2_id'=>'required|exists:users,id|different:lecturer_1_id',
        ]);

        $thesis = Thesis::findOrFail($request->thesis_id);

        $thesis->update([
            'lecturer_1_id'=>$request->lecturer_1_id,
            'lecturer_2_id'=>$request->lecturer_2_id,
            'status'=>'pengerjaan_skripsi',
            'proposal_date'=>now()
        ]);

        return $request->wantsJson()
            ? response()->json(['message'=>'Dosen assigned'])
            : back()->with('success','Dosen assigned');
    }

    public function scheduleExam(Request $request)
    {
        $request->validate([
            'thesis_id'=>'required|exists:theses,id',
            'date'=>'required|date',
            'time'=>'required',
            'room'=>'required|string'
        ]);

        $thesis = Thesis::findOrFail($request->thesis_id);

        $thesis->update([
            'final_exam_date'=>$request->date,
            'exam_time'=>$request->time,
            'exam_room'=>$request->room,
            'status'=>'siap_sidang'
        ]);

        return $request->wantsJson()
            ? response()->json(['message'=>'Jadwal dibuat'])
            : back()->with('success','Jadwal dibuat');
    }

    public function markAsGraduated(Request $request)
    {
        $request->validate([
            'thesis_id'=>'required|exists:theses,id',
        ]);

        $thesis = Thesis::findOrFail($request->thesis_id);

        $thesis->update(['status'=>'lulus']);

        return $request->wantsJson()
            ? response()->json(['message'=>'Lulus'])
            : back()->with('success','Mahasiswa lulus');
    }
}