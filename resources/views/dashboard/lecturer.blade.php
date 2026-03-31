@extends('layouts.app')

@section('title', 'Dashboard Dosen')

@section('content')
<div class="space-y-8">
    <div class="flex justify-between items-end">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Mahasiswa Bimbingan</h1>
            <p class="text-slate-500">Review draft, acc skripsi, dan pantau riwayat logbook.</p>
        </div>
        <div class="bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm text-center">
            <span class="block text-2xl font-bold text-indigo-600">{{ $theses->count() }}</span>
            <span class="text-xs text-slate-500 uppercase font-bold">Total Mhs</span>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($theses as $thesis)
        @php
            $isFinished = in_array($thesis->status, ['lulus', 'siap_sidang']);
            $isAcc = $thesis->status == 'acc_pembimbing';
        @endphp
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 flex flex-col h-full hover:shadow-lg hover:-translate-y-1 transition duration-300 overflow-hidden relative">
            
            <!-- Badge Centang Hijau (Jika Selesai/Sidang/Lulus) -->
            @if($isFinished || $isAcc)
            <div class="absolute top-0 right-0 bg-green-500 text-white p-2 rounded-bl-2xl shadow-md z-10" title="{{ $isFinished ? 'Selesai/Sidang' : 'Di-ACC' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
            </div>
            @endif

            <!-- Header Card -->
            <div class="p-6 border-b border-slate-50 bg-gradient-to-r from-slate-50 to-white">
                <div class="flex justify-between items-start mb-3">
                    <div class="w-12 h-12 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-lg shadow-sm">
                        {{ substr($thesis->student->name, 0, 2) }}
                    </div>
                    <!-- Status Badge Kecil -->
                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider
                        @if($thesis->status == 'lulus') bg-green-100 text-green-700
                        @elseif($thesis->status == 'siap_sidang') bg-blue-100 text-blue-700
                        @elseif($thesis->status == 'acc_pembimbing') bg-indigo-100 text-indigo-700
                        @elseif($thesis->status == 'perlu_revisi') bg-orange-100 text-orange-700
                        @else bg-slate-100 text-slate-600
                        @endif">
                        {{ str_replace('_', ' ', $thesis->status) }}
                    </span>
                </div>
                <h3 class="font-bold text-slate-900 line-clamp-2 leading-snug mb-1 pr-6" title="{{ $thesis->title }}">{{ $thesis->title }}</h3>
                <p class="text-sm text-slate-500 font-medium">{{ $thesis->student->name }}</p>
                <p class="text-xs text-slate-400">{{ $thesis->student->nip_nim }}</p>
            </div>

            <!-- Body Card -->
            <div class="p-6 flex-1 flex flex-col gap-4">
                
                <!-- Section: Draft Skripsi -->
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase mb-2 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full"></span> Draft Skripsi
                    </h4>
                    
                    @if($thesis->draft_file_path)
                        <a href="{{ $thesis->draft_file_path }}" target="_blank" class="group flex items-center justify-between p-3 rounded-xl bg-indigo-50 border border-indigo-100 hover:bg-indigo-100 transition mb-3">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <div class="bg-white p-2 rounded-lg text-red-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 2H7a2 2 0 00-2 2v15a2 2 0 002 2z"></path></svg>
                                </div>
                                <span class="text-sm font-medium text-indigo-900 truncate">Lihat Draft PDF</span>
                            </div>
                            <svg class="w-4 h-4 text-indigo-400 group-hover:text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        </a>

                        {{-- HANYA TAMPILKAN AKSI REVISI/ACC JIKA BELUM SELESAI/SIDANG --}}
                        @if(!$isFinished && !$isAcc)
                            <div x-data="{ open: false, accOpen: false }" class="grid grid-cols-2 gap-2">
                                <button @click="open = true" class="py-2 px-3 bg-orange-50 text-orange-700 border border-orange-200 rounded-lg text-xs font-bold hover:bg-orange-100 transition">
                                    Revisi
                                </button>

                                <!-- Hidden ACC form (submitted from modal) -->
                                <form id="acc-form-{{ $thesis->id }}" action="{{ route('thesis.review') }}" method="POST" class="hidden">
                                    @csrf
                                    <input type="hidden" name="thesis_id" value="{{ $thesis->id }}">
                                    <input type="hidden" name="action" value="acc">
                                </form>

                                <!-- ACC button opens styled confirmation modal -->
                                <button type="button" @click="accOpen = true" class="w-full py-2 px-3 bg-green-600 text-white rounded-lg text-xs font-bold hover:bg-green-700 shadow-md transition">
                                    ACC Skripsi
                                </button>

                                <!-- Modal Revisi dengan Alert Styling -->
                                <div x-show="open" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" x-transition.opacity>
                                    <div @click.away="open = false" class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden transform transition-all scale-100">
                                        <div class="px-6 py-4 bg-orange-50 border-b border-orange-100 flex items-center gap-3">
                                            <div class="p-2 bg-orange-100 rounded-full text-orange-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                            </div>
                                            <h4 class="font-bold text-orange-800">Konfirmasi Revisi</h4>
                                        </div>
                                        <form action="{{ route('thesis.review') }}" method="POST" class="p-6">
                                            @csrf
                                            <input type="hidden" name="thesis_id" value="{{ $thesis->id }}">
                                            <input type="hidden" name="action" value="revisi">
                                            <p class="text-sm text-slate-600 mb-3">Berikan catatan spesifik agar mahasiswa dapat memperbaiki drafnya dengan tepat.</p>
                                            <textarea name="notes" rows="5" class="w-full border-slate-200 rounded-xl focus:ring-orange-500 focus:border-orange-500 text-sm p-3 bg-slate-50" placeholder="Contoh: Perbaiki format daftar pustaka pada Bab 2..." required></textarea>
                                            <div class="flex gap-2 mt-4">
                                                <button type="button" @click="open = false" class="flex-1 py-2.5 border border-slate-300 rounded-xl text-slate-600 text-sm font-medium hover:bg-slate-50">Batal</button>
                                                <button type="submit" class="flex-1 py-2.5 bg-orange-600 text-white rounded-xl text-sm font-bold hover:bg-orange-700 shadow-lg shadow-orange-200">Kirim Revisi</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                <!-- Modal ACC dengan Alert Styling -->
                                <div x-show="accOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4" x-transition.opacity>
                                    <div @click.away="accOpen = false" class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden transform transition-all">
                                        <div class="px-6 py-4 bg-green-50 border-b border-green-100 flex items-center gap-3">
                                            <div class="p-2 bg-green-100 rounded-full text-green-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            </div>
                                            <h4 class="font-bold text-green-800">Konfirmasi ACC Skripsi</h4>
                                        </div>
                                        <div class="p-6">
                                            <p class="text-sm text-slate-600 mb-3">Yakin ingin ACC skripsi ini? Tindakan ini akan menandai mahasiswa untuk menunggu jadwal sidang.</p>
                                            <div class="flex gap-2 mt-4">
                                                <button type="button" @click="accOpen = false" class="flex-1 py-2.5 border border-slate-300 rounded-xl text-slate-600 text-sm font-medium hover:bg-slate-50">Batal</button>
                                                <button type="button" @click="document.getElementById('acc-form-{{ $thesis->id }}').submit();" class="flex-1 py-2.5 bg-green-600 text-white rounded-xl text-sm font-bold hover:bg-green-700 shadow">Kirim ACC</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($isAcc)
                            <div class="p-3 bg-indigo-50 border border-indigo-100 rounded-xl text-center">
                                <span class="text-indigo-700 font-bold text-xs block">✓ Telah Di-ACC</span>
                                <p class="text-[10px] text-indigo-600 mt-0.5">Menunggu jadwal sidang dari Admin</p>
                            </div>
                        @elseif($isFinished)
                            <div class="p-3 bg-green-50 border border-green-100 rounded-xl text-center">
                                <span class="text-green-700 font-bold text-xs block">✓ Proses Selesai</span>
                                <p class="text-[10px] text-green-600 mt-0.5">Mahasiswa telah dijadwalkan/lulus</p>
                            </div>
                        @endif
                    @else
                        <div class="p-4 bg-slate-50 rounded-xl border border-dashed border-slate-200 text-center">
                            <p class="text-xs text-slate-400 italic">Belum ada draft diunggah.</p>
                        </div>
                    @endif
                </div>

                <!-- Section: Logbook & Riwayat Revisi -->
                <div class="pt-4 border-t border-slate-100">
                    <h4 class="text-xs font-bold text-slate-400 uppercase mb-3 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full"></span> Riwayat Aktivitas Terakhir
                    </h4>
                    
                    @php $lastLog = $thesis->logbooks->sortByDesc('created_at')->first(); @endphp
                    
                    @if($lastLog)
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 space-y-3">
                            <!-- Info Utama Logbook -->
                            <div>
                                <div class="flex justify-between items-start mb-1">
                                    <span class="text-xs font-bold text-slate-800">{{ $lastLog->topic }}</span>
                                    <span class="text-[10px] px-1.5 py-0.5 rounded bg-white border border-slate-200 text-slate-500 font-medium">
                                        {{ $lastLog->created_at->format('d M Y') }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed">{{ $lastLog->description }}</p>
                            </div>

                            <!-- Tampilan Feedback/Revisi Dosen (Jika Ada) -->
                            @if($lastLog->feedback)
                                <div class="relative pl-3 ml-1 border-l-2 border-orange-300 bg-orange-50/50 rounded-r-lg p-2">
                                    <div class="absolute -left-[5px] top-2 w-2 h-2 bg-orange-400 rounded-full"></div>
                                    <p class="text-[10px] font-bold text-orange-700 uppercase mb-1 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                        Catatan Dosen:
                                    </p>
                                    <p class="text-xs text-orange-800 italic leading-relaxed">"{{ $lastLog->feedback }}"</p>
                                </div>
                            @endif
                            
                            <div class="pt-2 border-t border-slate-200 flex justify-end">
                                <p class="text-[10px] text-slate-400">Updated {{ $lastLog->updated_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @else
                        <div class="p-4 bg-slate-50 rounded-xl border border-dashed border-slate-200 text-center">
                            <p class="text-xs text-slate-400 italic">Belum ada aktivitas logbook.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
        <!-- Empty State -->
        @if($theses->isEmpty())
        <div class="col-span-full py-20 text-center bg-white rounded-3xl border border-dashed border-slate-200">
            <p class="text-slate-500 font-medium">Belum ada mahasiswa bimbingan.</p>
        </div>
        @endif
    </div>
@endsection