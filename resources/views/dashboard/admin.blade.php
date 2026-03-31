@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-8" x-data="{ 
    modalOpen: false, 
    selectedThesis: null, 
    sTitle: '', 
    sNim: '', 
    activeTab: 'antrean' 
}">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Admin Overview</h1>
        <p class="text-slate-500">Kelola plotting dosen, jadwal sidang, dan kelulusan.</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-xs text-slate-500 uppercase font-bold">Total Mahasiswa</p>
            <p class="text-3xl font-bold text-slate-800 mt-2">{{ $stats['students'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-xs text-slate-500 uppercase font-bold">Sedang Skripsi</p>
            <p class="text-3xl font-bold text-indigo-600 mt-2">{{ $stats['active_theses'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="absolute right-0 top-0 w-16 h-16 bg-green-500 rounded-bl-full opacity-10"></div>
            <p class="text-xs text-slate-500 uppercase font-bold">Siap Sidang</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ $queueForExam->count() }}</p>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden">
            <div class="absolute right-0 top-0 w-16 h-16 bg-green-500 rounded-bl-full opacity-10"></div>
            <p class="text-xs text-slate-500 uppercase font-bold">Sedang Sidang</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ $waitingForGraduation->count() }}</p>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-xs text-slate-500 uppercase font-bold">Lulus</p>
            <p class="text-3xl font-bold text-slate-800 mt-2">{{ $stats['graduated'] }}</p>
        </div>
    </div>

    <!-- TABS Navigation -->
    <div class="border-b border-slate-200">
        <nav class="-mb-px flex space-x-8">
            <button @click="activeTab = 'pengajuan'" :class="activeTab === 'pengajuan' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                Pengajuan Proposal
            </button>
            <button @click="activeTab = 'bimbingan'" :class="activeTab === 'bimbingan' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                Sedang Bimbingan
            </button>
            <button @click="activeTab = 'antrean'" :class="activeTab === 'antrean' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                Antrean Sidang (ACC)
            </button>
            <button @click="activeTab = 'kelulusan'" :class="activeTab === 'kelulusan' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition">
                Menunggu Kelulusan
            </button>
        </nav>
    </div>

    <!-- TAB CONTENT: Pengajuan Proposal -->
    <div x-show="activeTab === 'pengajuan'" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-orange-50/50">
            <h3 class="font-bold text-lg text-slate-800">Daftar Pengajuan Judul Baru</h3>
            <p class="text-xs text-slate-500 mt-1">Mahasiswa yang menunggu plotting dosen pembimbing.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3">Mahasiswa</th>
                        <th class="px-6 py-3">Judul Proposal</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($waitingForPlotting as $thesis)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-medium text-slate-900">{{ $thesis->student->name }}<br><span class="text-xs text-slate-400">{{ $thesis->student->nip_nim }}</span></td>
                        <td class="px-6 py-4 text-slate-600 max-w-xs truncate">{{ $thesis->title }}</td>
                        <td class="px-6 py-4 text-right">
                            <button @click="modalOpen = true; selectedThesis = {{ $thesis->id }}; sTitle = '{{ $thesis->title }}'; sNim = '{{ $thesis->student->name }}'" class="text-indigo-600 hover:text-indigo-800 font-bold text-xs border border-indigo-200 bg-indigo-50 px-3 py-2 rounded-lg transition shadow-sm">
                                Assign Dosen
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-6 py-8 text-center text-slate-400">Tidak ada pengajuan baru.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB CONTENT: Sedang Bimbingan -->
    <div x-show="activeTab === 'bimbingan'" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-blue-50/50">
            <h3 class="font-bold text-lg text-slate-800">Mahasiswa Sedang Bimbingan</h3>
            <p class="text-xs text-slate-500 mt-1">Daftar mahasiswa yang sedang dalam proses bimbingan aktif atau revisi.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3">Mahasiswa</th>
                        <th class="px-6 py-3">Judul Skripsi</th>
                        <th class="px-6 py-3">Dosen Pembimbing</th>
                        <th class="px-6 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($activeGuidance as $thesis)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-medium text-slate-900">{{ $thesis->student->name }}</td>
                        <td class="px-6 py-4 text-slate-600 max-w-xs truncate">{{ $thesis->title }}</td>
                        <td class="px-6 py-4 text-slate-600 text-xs">
                            1. {{ $thesis->lecturer1?->name ?? '-' }}<br>
                            2. {{ $thesis->lecturer2?->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 rounded-md text-xs font-bold uppercase 
                                @if($thesis->status == 'perlu_revisi') bg-orange-100 text-orange-700
                                @else bg-blue-100 text-blue-700
                                @endif">
                                {{ str_replace('_', ' ', $thesis->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-8 text-center text-slate-400">Belum ada mahasiswa bimbingan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB CONTENT: Antrean Sidang (ACC) -->
    <div x-show="activeTab === 'antrean'" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden ring-2 ring-indigo-50">
        <div class="px-6 py-4 border-b border-slate-100 bg-green-50/50 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                    <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                    Antrean Sidang Akhir (Sudah ACC)
                </h3>
                <p class="text-xs text-slate-500 mt-1">Mahasiswa yang telah di-ACC pembimbing dan menunggu jadwal.</p>
            </div>
            <span class="bg-indigo-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-md">{{ $queueForExam->count() }} Mahasiswa</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3">Mahasiswa</th>
                        <th class="px-6 py-3">Judul Skripsi</th>
                        <th class="px-6 py-3">Pembimbing</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($queueForExam as $thesis)
                    <tr class="hover:bg-slate-50 transition group">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900">{{ $thesis->student->name }}</div>
                            <div class="text-xs text-slate-500">{{ $thesis->student->nip_nim }}</div>
                        </td>
                        <td class="px-6 py-4 text-slate-600 max-w-xs truncate" title="{{ $thesis->title }}">{{ $thesis->title }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $thesis->lecturer1->name }}</td>
                        <td class="px-6 py-4 text-right">
                            @if($thesis->final_exam_date)
                                <div class="text-right">
                                    <span class="block text-xs font-bold text-slate-700">{{ \Carbon\Carbon::parse($thesis->final_exam_date)->format('d M Y') }}</span>
                                    <span class="block text-xs text-slate-500">{{ $thesis->exam_time }} • {{ $thesis->exam_room }}</span>
                                </div>
                            @else
                                <button 
                                    @click="modalOpen = true; selectedThesis = {{ $thesis->id }}; sTitle = '{{ $thesis->title }}'; sNim = '{{ $thesis->student->name }}'" 
                                    class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 font-bold text-xs border border-indigo-200 bg-indigo-50 px-3 py-2 rounded-lg transition shadow-sm hover:shadow-md hover:-translate-y-0.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                    Set Jadwal
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <p class="text-slate-500 font-medium">Belum ada mahasiswa yang di-ACC untuk sidang akhir.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB CONTENT: Menunggu Kelulusan -->
    <div x-show="activeTab === 'kelulusan'" class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden ring-2 ring-yellow-400">
        <div class="px-6 py-4 border-b border-slate-100 bg-yellow-50/50 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-lg text-slate-800 flex items-center gap-2">
                    <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
                    Menunggu Kelulusan (Sudah Sidang)
                </h3>
                <p class="text-xs text-slate-500 mt-1">Mahasiswa yang sudah sidang dan menunggu status lulus.</p>
            </div>
            <span class="bg-yellow-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-md">{{ $waitingForGraduation->count() }} Mahasiswa</span>
        </div>
        
        <!-- ALERT: Informasi penting sebelum meluluskan -->
        <div class="px-6 py-4 bg-yellow-50 border-t border-b border-yellow-100">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 1010 10A10 10 0 0012 2z"></path></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-yellow-800">Perhatian</p>
                    <p class="text-xs text-yellow-700 mt-1">Pastikan nilai, verifikasi administrasi, dan berkas pendukung sudah lengkap sebelum menandai mahasiswa sebagai <strong>lulus</strong>. Tindakan ini bersifat final.</p>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3">Mahasiswa</th>
                        <th class="px-6 py-3">Judul Skripsi</th>
                        <th class="px-6 py-3">Tanggal Sidang</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($waitingForGraduation as $thesis)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4 font-bold text-slate-900">{{ $thesis->student->name }}</td>
                        <td class="px-6 py-4 text-slate-600 max-w-xs truncate">{{ $thesis->title }}</td>
                        <td class="px-6 py-4 text-slate-600 text-sm">
                            {{ \Carbon\Carbon::parse($thesis->final_exam_date)->format('d M Y') }}<br>
                            <span class="text-xs text-slate-400">{{ $thesis->exam_time }} • {{ $thesis->exam_room }}</span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <button type="button" @click="modalOpen = true; selectedThesis = {{ $thesis->id }}; sNim = '{{ $thesis->student->name }}'; sTitle = '{{ $thesis->title }}'" class="bg-green-600 hover:bg-green-700 text-white font-bold text-xs px-4 py-2 rounded-lg shadow-md transition transform hover:-translate-y-0.5">
                                ✓ Luluskan
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center">
                            <p class="text-slate-500 font-medium">Belum ada mahasiswa yang siap diluluskan.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: Konfirmasi Luluskan -->
    <div x-show="modalOpen && activeTab === 'kelulusan'" style="display: none;" class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md" x-transition.opacity>
        <div @click.away="modalOpen = false" class="bg-white rounded-2xl w-full max-w-md p-0 shadow-2xl transform transition-all scale-100 overflow-hidden">
            <div class="bg-yellow-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-white font-bold text-lg">Konfirmasi Pelulusan</h3>
                <button @click="modalOpen = false" class="text-yellow-200 hover:text-white">&times;</button>
            </div>
            <div class="p-6">
                <div class="mb-4 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <p class="text-xs text-slate-500 uppercase font-bold mb-1">Mahasiswa</p>
                    <p class="font-bold text-slate-900" x-text="sNim"></p>
                    <p class="text-sm text-slate-600 mt-1 line-clamp-2" x-text="sTitle"></p>
                </div>

                <p class="text-sm text-slate-700">Pastikan semua persyaratan terpenuhi. Menandai mahasiswa sebagai <strong>lulus</strong> tidak dapat dibatalkan melalui antarmuka ini.</p>

                <form action="{{ route('admin.mark_graduated') }}" method="POST" class="mt-6">
                    @csrf
                    <input type="hidden" name="thesis_id" :value="selectedThesis">
                    <div class="flex gap-3">
                        <button type="button" @click="modalOpen = false" class="flex-1 py-3 px-4 border border-slate-300 rounded-xl text-slate-700 font-bold hover:bg-slate-50 transition">Batal</button>
                        <button type="submit" class="flex-1 py-3 px-4 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 shadow-lg shadow-green-200 transition transform active:scale-95">✓ Konfirmasi Lulus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Assign Dosen -->
    <div x-show="modalOpen && activeTab === 'pengajuan'" style="display: none;" 
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md"
         x-transition.opacity>
        <div @click.away="modalOpen = false" class="bg-white rounded-2xl w-full max-w-lg p-0 shadow-2xl transform transition-all scale-100 overflow-hidden">
            <div class="bg-indigo-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-white font-bold text-lg">Assign Dosen Pembimbing</h3>
                <button @click="modalOpen = false" class="text-indigo-200 hover:text-white">&times;</button>
            </div>
            <div class="p-6">
                <div class="mb-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <p class="text-xs text-slate-500 uppercase font-bold mb-1">Mahasiswa</p>
                    <p class="font-bold text-slate-900" x-text="sNim"></p>
                    <p class="text-sm text-slate-600 mt-1 line-clamp-2" x-text="sTitle"></p>
                </div>

                <form action="{{ route('admin.assign_lecturers') }}" method="POST">
                    @csrf
                    <input type="hidden" name="thesis_id" :value="selectedThesis">
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Dosen Pembimbing 1</label>
                            <select name="lecturer_1_id" required class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 bg-white">
                                <option value="">Pilih Dosen 1...</option>
                                @foreach($lecturers as $lec)
                                    <option value="{{ $lec->id }}">{{ $lec->name }} ({{ $lec->nip_nim }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Dosen Pembimbing 2</label>
                            <select name="lecturer_2_id" required class="w-full rounded-xl border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 bg-white">
                                <option value="">Pilih Dosen 2...</option>
                                @foreach($lecturers as $lec)
                                    <option value="{{ $lec->id }}">{{ $lec->name }} ({{ $lec->nip_nim }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex gap-3 mt-8">
                        <button type="button" @click="modalOpen = false" class="flex-1 py-3 px-4 border border-slate-300 rounded-xl text-slate-700 font-bold hover:bg-slate-50 transition">Batal</button>
                        <button type="submit" class="flex-1 py-3 px-4 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition transform active:scale-95">Simpan Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Set Jadwal Sidang -->
    <div x-show="modalOpen && activeTab === 'antrean'" style="display: none;" 
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-md"
         x-transition.opacity>
        <div @click.away="modalOpen = false" class="bg-white rounded-2xl w-full max-w-lg p-0 shadow-2xl transform transition-all scale-100 overflow-hidden">
            <div class="bg-green-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-white font-bold text-lg">Jadwalkan Sidang Akhir</h3>
                <button @click="modalOpen = false" class="text-green-200 hover:text-white">&times;</button>
            </div>
            
            <div class="p-6">
                <div class="mb-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <p class="text-xs text-slate-500 uppercase font-bold mb-1">Mahasiswa</p>
                    <p class="font-bold text-slate-900" x-text="sNim"></p>
                    <p class="text-sm text-slate-600 mt-1 line-clamp-2" x-text="sTitle"></p>
                </div>

                <form action="{{ route('admin.schedule_exam') }}" method="POST">
                    @csrf
                    <input type="hidden" name="thesis_id" :value="selectedThesis">
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal</label>
                            <input type="date" name="date" required class="w-full rounded-xl border-slate-300 shadow-sm focus:border-green-500 focus:ring-green-500 py-2.5">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Waktu</label>
                            <input type="time" name="time" required class="w-full rounded-xl border-slate-300 shadow-sm focus:border-green-500 focus:ring-green-500 py-2.5">
                        </div>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Ruangan</label>
                        <input type="text" name="room" required placeholder="Contoh: Ruang Sidang A / Zoom" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-green-500 focus:ring-green-500 py-2.5">
                        <p class="text-xs text-slate-400 mt-1">Ketik nama ruangan secara manual.</p>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="button" @click="modalOpen = false" class="flex-1 py-3 px-4 border border-slate-300 rounded-xl text-slate-700 font-bold hover:bg-slate-50 transition">Batal</button>
                        <button type="submit" class="flex-1 py-3 px-4 bg-green-600 text-white rounded-xl font-bold hover:bg-green-700 shadow-lg shadow-green-200 transition transform active:scale-95">Simpan Jadwal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection