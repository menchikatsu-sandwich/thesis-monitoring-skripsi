@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Progress Skripsi</h1>
            <p class="text-slate-500">Pantau status dan lakukan aksi berikutnya.</p>
        </div>
        <div class="flex gap-2">
            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border
                @if($thesis->status == 'menunggu_plotting') bg-yellow-100 text-yellow-700 border-yellow-200
                @elseif($thesis->status == 'bimbingan_aktif') bg-blue-100 text-blue-700 border-blue-200
                @elseif($thesis->status == 'perlu_revisi') bg-orange-100 text-orange-700 border-orange-200
                @elseif($thesis->status == 'acc_pembimbing') bg-green-100 text-green-700 border-green-200
                @else bg-indigo-100 text-indigo-700 border-indigo-200
                @endif">
                Status: {{ str_replace('_', ' ', $thesis->status) }}
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="p-4 bg-green-50 text-green-700 border border-green-200 rounded-xl flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            {{ session('success') }}
        </div>
    @endif

    <!-- KONDISI 1: Belum Submit Judul / Menunggu Plotting -->
    @if(in_array($thesis->status, ['pengajuan_awal', 'menunggu_plotting']) && !$thesis->lecturer_1_id)
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100">
            <h3 class="text-xl font-bold text-slate-900 mb-4">Ajukan Judul & Abstrak</h3>
            <p class="text-slate-500 mb-6">Silakan isi judul sementara dan deskripsi singkat (latar belakang/garis besar) untuk diajukan ke Admin.</p>
            
            <form action="{{ route('thesis.submit_proposal') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Judul Skripsi (Sementara)</label>
                        <input type="text" name="title" value="{{ old('title', $thesis->title) }}" required 
                               class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" 
                               placeholder="Contoh: Sistem Monitoring Berbasis AI...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi / Abstrak Singkat</label>
                        <textarea name="abstract" rows="5" required 
                                  class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" 
                                  placeholder="Jelaskan latar belakang masalah dan garis besar solusi yang ditawarkan...">{{ old('abstract', $thesis->abstract) }}</textarea>
                    </div>
                    <button type="submit" class="px-6 py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition shadow-lg shadow-indigo-200">
                        Kirim Proposal
                    </button>
                </div>
            </form>
            
            @if($thesis->status == 'menunggu_plotting')
                <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-yellow-800 text-sm">
                    <strong>Menunggu Admin:</strong> Proposal Anda sedang ditinjau. Admin akan segera menetapkan Dosen Pembimbing.
                </div>
            @endif
        </div>

    <!-- KONDISI 2: Sudah Dapat Dosen (Bimbingan Aktif / Revisi / ACC) -->
    @else
        <!-- Progress Bar -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
            <h3 class="font-semibold text-lg mb-6">Tahapan Skripsi</h3>
            <div class="relative pt-2">
                <div class="absolute top-1/2 left-0 w-full h-1 bg-slate-100 -translate-y-1/2 rounded-full"></div>
                @php
                    $steps = ['pengajuan_awal', 'menunggu_plotting', 'bimbingan_aktif', 'acc_pembimbing', 'siap_sidang', 'lulus'];
                    $currentIdx = array_search($thesis->status, $steps);
                    if ($currentIdx === false) $currentIdx = 0;
                    $percent = (($currentIdx + 1) / count($steps)) * 100;
                @endphp
                <div class="absolute top-1/2 left-0 h-1 bg-indigo-600 -translate-y-1/2 rounded-full transition-all duration-1000" style="width: {{ $percent }}%"></div>
                
                <div class="relative flex justify-between">
                    @foreach(['Ajukan', 'Plotting', 'Bimbingan', 'ACC', 'Sidang', 'Lulus'] as $label)
                        @php $isActive = $loop->index <= $currentIdx; @endphp
                        <div class="flex flex-col items-center gap-2">
                            <div class="w-8 h-8 rounded-full border-2 {{ $isActive ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white border-slate-300 text-slate-400' }} flex items-center justify-center text-xs font-bold z-10 transition-colors">
                                {{ $loop->index + 1 }}
                            </div>
                            <span class="text-xs font-medium text-slate-600 hidden md:block">{{ $label }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Upload Area -->
            <div class="lg:col-span-2 bg-gradient-to-br from-indigo-600 to-violet-700 rounded-2xl p-6 text-white shadow-lg">
                <h3 class="font-semibold text-lg mb-2">Upload Draft Skripsi (PDF)</h3>
                <p class="text-indigo-100 text-sm mb-6">Unggah progres terbaru (BAB 1-3 atau Full Draft) untuk direview dosen pembimbing.</p>
                
                @if($thesis->draft_file_path)
                    <div class="mb-6 bg-white/10 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium">File Terakhir:</span>
                            <a href="{{ asset('storage/' . $thesis->draft_file_path) }}" target="_blank" class="text-xs bg-white text-indigo-600 px-3 py-1.5 rounded-lg font-bold hover:bg-indigo-50 transition flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Download PDF
                            </a>
                        </div>
                        <p class="text-xs text-indigo-200 truncate">{{ basename($thesis->draft_file_path) }}</p>
                    </div>

                    @if($thesis->lecturer_notes)
                        <div class="mb-6 p-4 bg-orange-500/20 border border-orange-400/30 rounded-xl">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-orange-200 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                <div>
                                    <p class="text-xs font-bold text-orange-200 uppercase mb-1">Catatan Revisi Dosen:</p>
                                    <p class="text-sm text-white leading-relaxed">{{ $thesis->lecturer_notes }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif

                <form action="{{ route('thesis.upload') }}" method="POST" enctype="multipart/form-data" class="mt-auto">
                    @csrf
                    <input type="hidden" name="thesis_id" value="{{ $thesis->id }}">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input type="file" name="file" accept=".pdf" required 
                               class="block w-full text-sm text-slate-200 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-white file:text-indigo-700 hover:file:bg-indigo-50 cursor-pointer"/>
                        <button type="submit" class="bg-white text-indigo-700 font-bold py-2.5 px-6 rounded-lg hover:bg-indigo-50 transition shadow-lg whitespace-nowrap">
                            Upload
                        </button>
                    </div>
                </form>
            </div>

            <!-- Info Dosen -->
            <div class="space-y-4">
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Pembimbing 1</h4>
                    @if($thesis->lecturer1)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold">{{ substr($thesis->lecturer1->name, 0, 2) }}</div>
                            <div>
                                <p class="font-bold text-slate-900 text-sm">{{ $thesis->lecturer1->name }}</p>
                                <p class="text-xs text-slate-500">{{ $thesis->lecturer1->nip_nim }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic">Belum ditentukan</p>
                    @endif
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Pembimbing 2</h4>
                    @if($thesis->lecturer2)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center font-bold">{{ substr($thesis->lecturer2->name, 0, 2) }}</div>
                            <div>
                                <p class="font-bold text-slate-900 text-sm">{{ $thesis->lecturer2->name }}</p>
                                <p class="text-xs text-slate-500">{{ $thesis->lecturer2->nip_nim }}</p>
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-slate-400 italic">Belum ditentukan</p>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
@endsection