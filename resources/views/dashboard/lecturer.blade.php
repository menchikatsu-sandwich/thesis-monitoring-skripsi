@extends('layouts.app')

@section('title', 'Dashboard Dosen')

@section('content')
<div class="space-y-8">
    <div class="flex justify-between items-end">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Bimbingan Skripsi</h1>
            <p class="text-slate-500 mt-1">Review draft, beri catatan, dan ACC mahasiswa.</p>
        </div>
        <div class="bg-white px-5 py-3 rounded-xl border border-slate-200 shadow-sm flex items-center gap-3">
            <div class="text-right">
                <p class="text-xs text-slate-400 font-bold uppercase">Total Bimbingan</p>
                <p class="text-xl font-bold text-indigo-600">{{ $theses->count() }} Mhs</p>
            </div>
            <div class="w-10 h-10 bg-indigo-50 rounded-full flex items-center justify-center text-indigo-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
        </div>
    </div>

    @if($theses->isEmpty())
        <div class="bg-white rounded-2xl p-12 text-center border border-dashed border-slate-200">
            <p class="text-slate-500">Belum ada mahasiswa yang dibimbing.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach($theses as $thesis)
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow duration-300 flex flex-col h-full relative group">
                <!-- Badge Status -->
                <div class="absolute top-4 right-4">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold uppercase tracking-wide
                        @if($thesis->status == 'acc_pembimbing') bg-green-100 text-green-700 border border-green-200
                        @elseif($thesis->status == 'revisi') bg-orange-100 text-orange-700 border border-orange-200
                        @else bg-slate-100 text-slate-600 border border-slate-200
                        @endif">
                        {{ str_replace('_', ' ', $thesis->status) }}
                    </span>
                </div>
                
                <!-- Profile Mahasiswa -->
                <div class="flex items-center gap-3 mb-5 pt-2">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-lg shadow-md">
                        {{ substr($thesis->student->name, 0, 2) }}
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900 line-clamp-1">{{ $thesis->student->name }}</h3>
                        <p class="text-xs text-slate-500">{{ $thesis->student->nip_nim }}</p>
                    </div>
                </div>
                
                <!-- Judul -->
                <div class="mb-6 flex-grow">
                    <p class="text-xs text-slate-400 font-bold uppercase mb-1">Judul Skripsi</p>
                    <h4 class="text-sm font-medium text-slate-700 line-clamp-3 leading-relaxed">{{ $thesis->title }}</h4>
                </div>
                
                <!-- Action Area -->
                <div class="mt-auto space-y-3 border-t border-slate-100 pt-4">
                    @if($thesis->draft_file_path)
                        <a href="{{ asset('storage/' . $thesis->draft_file_path) }}" target="_blank" class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-indigo-50 text-indigo-700 rounded-xl text-sm font-bold hover:bg-indigo-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            Baca Draft PDF
                        </a>
                    @else
                        <div class="w-full py-2.5 px-4 bg-slate-50 text-slate-400 rounded-xl text-xs font-medium text-center border border-slate-100 border-dashed">
                            Belum ada draft diunggah
                        </div>
                    @endif

                    @if($thesis->draft_file_path && $thesis->status !== 'acc_pembimbing')
                        <div x-data="{ open: false }" class="grid grid-cols-2 gap-3">
                            <!-- Tombol Revisi -->
                            <button @click="open = true" class="py-2.5 px-3 bg-white border border-orange-200 text-orange-600 rounded-xl text-sm font-bold hover:bg-orange-50 transition shadow-sm">
                                Revisi
                            </button>
                            
                            <!-- Tombol ACC -->
                            <form action="{{ route('thesis.review') }}" method="POST" onsubmit="return confirm('Yakin ingin ACC skripsi ini? Mahasiswa akan masuk antrean sidang.')">
                                @csrf
                                <input type="hidden" name="thesis_id" value="{{ $thesis->id }}">
                                <input type="hidden" name="action" value="acc">
                                <button type="submit" class="w-full py-2.5 px-3 bg-green-600 text-white rounded-xl text-sm font-bold hover:bg-green-700 transition shadow-md shadow-green-200">
                                    ACC
                                </button>
                            </form>

                            <!-- Modal Input Revisi -->
                            <div x-show="open" style="display: none;" 
                                 class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
                                 x-transition.opacity>
                                <div @click.away="open = false" class="bg-white rounded-2xl w-full max-w-md p-6 shadow-2xl transform transition-all scale-100">
                                    <h4 class="font-bold text-lg text-slate-900 mb-2">Berikan Catatan Revisi</h4>
                                    <p class="text-sm text-slate-500 mb-4">Jelaskan poin yang perlu diperbaiki oleh {{ $thesis->student->name }}.</p>
                                    
                                    <form action="{{ route('thesis.review') }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="thesis_id" value="{{ $thesis->id }}">
                                        <input type="hidden" name="action" value="revisi">
                                        
                                        <textarea name="notes" rows="5" class="w-full border-slate-200 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 text-sm p-3 bg-slate-50 resize-none" placeholder="Contoh:&#10;1. Bab 2 tambahkan referensi terbaru (2023-2024).&#10;2. Perbaiki format penulisan daftar pustaka." required></textarea>
                                        
                                        <div class="flex gap-3 mt-6">
                                            <button type="button" @click="open = false" class="flex-1 py-2.5 border border-slate-200 text-slate-600 rounded-xl font-bold hover:bg-slate-50">Batal</button>
                                            <button type="submit" class="flex-1 py-2.5 bg-orange-600 text-white rounded-xl font-bold hover:bg-orange-700 shadow-lg shadow-orange-200">Kirim Revisi</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @elseif($thesis->status == 'acc_pembimbing')
                        <div class="p-3 bg-green-50 border border-green-100 rounded-xl text-center">
                            <span class="text-green-700 font-bold text-sm block">✓ Telah Di-ACC</span>
                            <p class="text-xs text-green-600 mt-1">Menunggu jadwal sidang dari Admin</p>
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection