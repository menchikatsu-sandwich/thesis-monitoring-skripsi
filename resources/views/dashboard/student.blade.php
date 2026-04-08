@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')

@section('content')
<div class="space-y-8">
    
    <!-- KONDISI 1: PENGAJUAN AWAL & MENUNGGU PLOTTING -->
    @if(in_array($thesis->status, ['pengajuan_awal', 'menunggu_plotting']))
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="p-8 text-center border-b border-slate-50 bg-slate-50/50">
                    <div class="w-16 h-16 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-slate-900">Pengajuan Proposal Skripsi</h2>
                    <p class="text-slate-500 mt-2">Silakan isi judul dan deskripsi awal untuk diajukan ke Kaprodi.</p>
                </div>

                <div class="p-8">
                    @if(session('success'))
                        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl flex items-start gap-3">
                            <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <div>
                                <h4 class="font-bold text-green-800">Proposal Berhasil Diajukan!</h4>
                                <p class="text-sm text-green-700 mt-1">Judul Anda sedang menunggu validasi dan plotting dosen pembimbing dari Admin. Silakan cek dashboard secara berkala.</p>
                            </div>
                        </div>
                    @else
                        <form action="{{ route('thesis.submit_proposal') }}" method="POST" class="space-y-6">
                            @csrf
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Judul Skripsi</label>
                                <input type="text" name="title" value="{{ old('title', $thesis->title) }}" required 
                                    class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" 
                                    placeholder="Contoh: Sistem Informasi Geografis Berbasis Web...">
                                @error('title') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">Abstrak / Deskripsi Awal</label>
                                <textarea name="abstract" rows="6" required 
                                    class="w-full rounded-xl border-slate-300 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm" 
                                    placeholder="Jelaskan latar belakang masalah, tujuan, dan garis besar solusi yang ditawarkan...">{{ old('abstract', $thesis->abstract) }}</textarea>
                                @error('abstract') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div class="pt-4">
                                <button type="submit" class="w-full py-3 px-4 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl shadow-lg shadow-indigo-200 transition transform active:scale-95">
                                    Ajukan Proposal
                                </button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
            
            @if($thesis->status == 'menunggu_plotting')
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-xl text-center">
                    <p class="text-sm text-blue-800 font-medium">Status Saat Ini: <span class="font-bold">Menunggu Plotting Dosen</span></p>
                    <p class="text-xs text-blue-600 mt-1">Admin sedang memproses penunjukan dosen pembimbing Anda.</p>
                </div>
            @endif
        </div>

    <!-- KONDISI 2: SUDAH LULUS -->
    @elseif($thesis->status == 'lulus')
        <div class="max-w-4xl mx-auto text-center pt-10">
            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-3xl p-10 border border-green-100 shadow-sm mb-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-green-200 rounded-full opacity-20 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-emerald-200 rounded-full opacity-20 blur-3xl"></div>
                
                <div class="relative z-10">
                    <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 mb-4">Selamat! Anda Telah Lulus</h1>
                    <p class="text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
                        Terima kasih telah berjuang keras dan menyelesaikan studi dengan baik. 
                        Kami bangga memiliki Anda sebagai bagian dari keluarga besar <span class="font-bold text-indigo-600">Politeknik Negeri Bali</span> dan jurusan Teknologi Informasi.
                        Semoga ilmu yang didapat menjadi bekal sukses di masa depan.
                    </p>
                    <div class="mt-8 inline-block px-6 py-2 bg-white border border-green-200 rounded-full text-green-700 font-bold text-sm shadow-sm">
                        Status: YUDISIUM / LULUS
                    </div>
                </div>
            </div>

            <!-- Logbook History for Graduated Student -->
            <div class="text-left bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-bold text-slate-800">Riwayat Perjalanan Skripsi</h3>
                </div>
                <div class="p-6">
                    @include('dashboard.partials.logbook-history', ['logbooks' => $logbooks])
                </div>
            </div>
        </div>

    <!-- KONDISI 3: SUDAH DIJADWALKAN SIDANG (SIAP_SIDANG) -->
    @elseif($thesis->status == 'siap_sidang')
        <div class="max-w-4xl mx-auto">
            <!-- Header Info -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Persiapan Sidang Akhir</h1>
                    <p class="text-slate-500">Berikut adalah jadwal sidang Anda. Harap hadir 15 menit sebelum acara dimulai.</p>
                </div>
                <span class="px-4 py-2 bg-indigo-100 text-indigo-700 rounded-full text-sm font-bold border border-indigo-200">
                    Siap Sidang
                </span>
            </div>

            <!-- Jadwal Card -->
            <div class="bg-white rounded-2xl shadow-lg border border-indigo-100 overflow-hidden mb-8">
                <div class="bg-indigo-600 px-6 py-4 flex items-center gap-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <h2 class="text-white font-bold text-lg">Detail Pelaksanaan Sidang</h2>
                </div>
                <div class="p-8 grid grid-cols-1 md:grid-cols-3 gap-8 text-center divide-y md:divide-y-0 md:divide-x divide-slate-100">
                    <div class="p-2">
                        <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-2">Tanggal</p>
                        <p class="text-xl font-bold text-slate-900">{{ \Carbon\Carbon::parse($thesis->final_exam_date)->isoFormat('dddd, D MMMM Y') }}</p>
                    </div>
                    <div class="p-2">
                        <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-2">Waktu</p>
                        <p class="text-xl font-bold text-indigo-600">{{ date('H:i', strtotime($thesis->exam_time)) }} WIB</p>
                    </div>
                    <div class="p-2">
                        <p class="text-xs text-slate-500 uppercase font-bold tracking-wider mb-2">Tempat</p>
                        <p class="text-xl font-bold text-slate-900">{{ $thesis->exam_room }}</p>
                    </div>
                </div>
                <div class="bg-slate-50 px-8 py-4 border-t border-slate-100 text-center">
                    <p class="text-sm text-slate-600">Pastikan seluruh berkas administrasi sudah diserahkan ke bagian tata usaha sebelum hari H.</p>
                </div>
            </div>

            <!-- Logbook History -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-bold text-slate-800">Riwayat Bimbingan</h3>
                </div>
                <div class="p-6">
                    @include('dashboard.partials.logbook-history', ['logbooks' => $logbooks])
                </div>
            </div>
        </div>

    <!-- KONDISI 4: BIMBINGAN AKTIF (PROPOSAL, REVISI, ACC, DLL) -->
    @else
        <!-- Header & Status -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Progress Skripsi</h1>
                <p class="text-slate-500">Pantau tahapan, upload draft, dan lihat riwayat bimbingan.</p>
            </div>
            <div class="flex gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide
                    @if($thesis->status == 'acc_pembimbing') bg-green-100 text-green-700 border-green-200
                    @elseif($thesis->status == 'perlu_revisi') bg-orange-100 text-orange-700 border-orange-200
                    @else bg-indigo-100 text-indigo-700 border-indigo-200
                    @endif border">
                    Status: {{ str_replace('_', ' ', $thesis->status) }}
                </span>
            </div>
        </div>

        <!-- Bento Grid Layout -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Progress Tracker -->
            <div class="md:col-span-2 bg-white rounded-2xl p-6 shadow-sm border border-slate-100 bento-card">
                <h3 class="font-semibold text-lg mb-6">Tahapan Skripsi</h3>
                <div class="relative">
                    <!-- Garis Abu-abu (Background) -->
                    <div class="absolute top-1/2 left-0 w-full h-1 bg-slate-100 -translate-y-1/2 rounded-full"></div>
                    
                    <!-- Garis Warna (Progress) -->
                    @php
                        $stepsMap = [
                            'pengajuan_awal' => 0, 'menunggu_plotting' => 0,
                            'pengerjaan_skripsi' => 1, 'bimbingan_aktif' => 2, 'perlu_revisi' => 2,
                            'acc_pembimbing' => 3, 'siap_sidang' => 4, 'lulus' => 5
                        ];
                        $currentStepIndex = $stepsMap[$thesis->status] ?? 0;
                        $progressWidth = ($currentStepIndex / 5) * 100;
                    @endphp
                    <div class="absolute top-1/2 left-0 h-1 bg-indigo-600 -translate-y-1/2 rounded-full transition-all duration-1000" style="width: {{ $progressWidth }}%"></div>
                    
                    <!-- Bola-bola Tahapan -->
                    <div class="relative flex justify-between">
                        @foreach(['Ajukan', 'Plotting', 'Bimbingan', 'ACC', 'Sidang', 'Lulus'] as $label)
                            @php
                                $isActive = $loop->index <= $currentStepIndex;
                                $isCurrent = $loop->index == $currentStepIndex;
                            @endphp
                            <div class="flex flex-col items-center gap-2 group">
                                <div class="w-8 h-8 rounded-full border-2 
                                    {{ $isActive ? 'bg-indigo-600 border-indigo-600 text-white shadow-md shadow-indigo-200' : 'bg-white border-slate-300 text-slate-400' }} 
                                    flex items-center justify-center text-xs font-bold z-10 transition-all duration-300
                                    {{ $isCurrent ? 'ring-4 ring-indigo-100 scale-110' : '' }}">
                                    {{ $loop->index + 1 }}
                                </div>
                                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Upload Card -->
            <div class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-2xl p-6 text-white shadow-lg bento-card flex flex-col justify-between relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-2xl"></div>
                
                <div>
                    <h3 class="font-semibold text-lg">Upload Draft PDF</h3>
                    <p class="text-indigo-100 text-sm mt-1">Unggah draf terbaru untuk direview dosen.</p>
                </div>
                
                <div class="mt-6 space-y-4">
                    @if($thesis->draft_file_path)
                        <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/20">
                            <p class="text-xs text-indigo-100 mb-1">File Terkini:</p>
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium truncate max-w-[150px]">{{ basename($thesis->draft_file_path) }}</span>
                                <a href="{{ $thesis->draft_file_path }}" target="_blank" class="text-xs bg-white text-indigo-600 px-2 py-1 rounded font-bold hover:bg-indigo-50">Download</a>
                            </div>
                        </div>
                        
                        @if($thesis->lecturer_notes && $thesis->status == 'perlu_revisi')
                            <div class="bg-orange-500/20 border border-orange-400/30 rounded-xl p-3">
                                <p class="text-xs font-bold text-orange-200 mb-1 flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    Catatan Dosen:
                                </p>
                                <p class="text-sm text-white italic">{{ $thesis->lecturer_notes }}</p>
                            </div>
                        @endif
                    @endif

                    <form action="{{ route('thesis.upload') }}" method="POST" enctype="multipart/form-data" class="mt-2">
                        @csrf
                        <input type="hidden" name="thesis_id" value="{{ $thesis->id }}">
                        <label class="block w-full">
                            <span class="sr-only">Choose file</span>
                            <input type="file" name="file" accept=".pdf" required class="block w-full text-sm text-slate-200
                                file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-white file:text-indigo-700 hover:file:bg-indigo-50"/>
                        </label>
                        <button type="submit" class="mt-3 w-full py-2.5 bg-white text-indigo-700 font-bold rounded-xl text-sm hover:bg-indigo-50 transition shadow-lg">
                            Upload Draft
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Dosen Pembimbing Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Pembimbing 1</h4>
                @if($thesis->lecturer1)
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold">{{ substr($thesis->lecturer1->name, 0, 2) }}</div>
                        <div><p class="font-bold text-slate-900">{{ $thesis->lecturer1->name }}</p><p class="text-xs text-slate-500">{{ $thesis->lecturer1->nip_nim }}</p></div>
                    </div>
                @else
                    <p class="text-sm text-slate-400 italic">Belum ditentukan</p>
                @endif
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Pembimbing 2</h4>
                @if($thesis->lecturer2)
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 font-bold">{{ substr($thesis->lecturer2->name, 0, 2) }}</div>
                        <div><p class="font-bold text-slate-900">{{ $thesis->lecturer2->name }}</p><p class="text-xs text-slate-500">{{ $thesis->lecturer2->nip_nim }}</p></div>
                    </div>
                @else
                    <p class="text-sm text-slate-400 italic">Belum ditentukan</p>
                @endif
            </div>
        </div>

        <!-- Logbook History -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-800">Riwayat Bimbingan (Logbook)</h3>
            </div>
            <div class="p-6">
                @include('dashboard.partials.logbook-history', ['logbooks' => $logbooks])
            </div>
        </div>
    @endif
</div>
@endsection