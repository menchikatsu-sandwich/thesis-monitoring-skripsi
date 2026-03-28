@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="space-y-8" x-data="{ modalOpen: false, selectedThesisId: null, sTitle: '', sName: '' }">
    
    <!-- Header Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-xs text-slate-500 uppercase font-bold">Total Mahasiswa</p>
            <p class="text-3xl font-bold text-slate-800 mt-2">{{ $stats['students'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-xs text-slate-500 uppercase font-bold">Sedang Skripsi</p>
            <p class="text-3xl font-bold text-indigo-600 mt-2">{{ $stats['active_theses'] }}</p>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-xs text-slate-500 uppercase font-bold">Menunggu Plotting</p>
            <p class="text-3xl font-bold text-orange-600 mt-2">{{ $unassignedTheses->count() }}</p>
        </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
            <p class="text-xs text-slate-500 uppercase font-bold">Siap Sidang (ACC)</p>
            <p class="text-3xl font-bold text-green-600 mt-2">{{ $queueForExam->count() }}</p>
        </div>
    </div>

    <!-- SECTION 1: Menunggu Plotting Dosen -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden ring-2 ring-orange-50">
        <div class="px-6 py-4 border-b border-slate-100 bg-orange-50/50 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-lg text-slate-800">Antrean Plotting Dosen</h3>
                <p class="text-xs text-slate-500 mt-1">Mahasiswa yang mengajukan judul baru dan menunggu penunjukan pembimbing.</p>
            </div>
            <span class="bg-orange-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-md">{{ $unassignedTheses->count() }} Pending</span>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3">Mahasiswa</th>
                        <th class="px-6 py-3">Judul Proposal</th>
                        <th class="px-6 py-3">Abstrak Singkat</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($unassignedTheses as $thesis)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-900">{{ $thesis->student->name }}</div>
                            <div class="text-xs text-slate-500">{{ $thesis->student->email }}</div>
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-700 max-w-xs truncate">{{ $thesis->title }}</td>
                        <td class="px-6 py-4 text-slate-500 max-w-xs truncate">{{ Str::limit($thesis->abstract, 50) }}</td>
                        <td class="px-6 py-4 text-right">
                            <button 
                                @click="modalOpen = true; selectedThesisId = {{ $thesis->id }}; sTitle = '{{ $thesis->title }}'; sName = '{{ $thesis->student->name }}'" 
                                class="inline-flex items-center gap-1 text-orange-700 hover:text-orange-900 font-bold text-xs border border-orange-200 bg-orange-50 px-3 py-2 rounded-lg transition shadow-sm hover:shadow-md">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                Assign Dosen
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                            Tidak ada proposal yang menunggu plotting.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECTION 2: Antrean Sidang (ACC) -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden ring-2 ring-green-50">
        <div class="px-6 py-4 border-b border-slate-100 bg-green-50/50 flex justify-between items-center">
            <div>
                <h3 class="font-bold text-lg text-slate-800">Antrean Sidang Akhir (Sudah ACC)</h3>
                <p class="text-xs text-slate-500 mt-1">Mahasiswa yang telah disetujui pembimbing untuk sidang.</p>
            </div>
            <span class="bg-green-600 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-md">{{ $queueForExam->count() }} Siap</span>
        </div>
        <!-- (Kode tabel antrean sidang sama seperti sebelumnya, silakan salin dari versi sebelumnya) -->
         <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs font-semibold">
                    <tr>
                        <th class="px-6 py-3">Mahasiswa</th>
                        <th class="px-6 py-3">Judul</th>
                        <th class="px-6 py-3">Pembimbing</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($queueForExam as $thesis)
                    <tr class="hover:bg-slate-50">
                        <td class="px-6 py-4 font-bold">{{ $thesis->student->name }}</td>
                        <td class="px-6 py-4 text-slate-600 max-w-xs truncate">{{ $thesis->title }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $thesis->lecturer1->name }}</td>
                        <td class="px-6 py-4 text-right">
                             @if($thesis->final_exam_date)
                                <span class="text-xs font-bold text-green-700 bg-green-100 px-2 py-1 rounded">Terjadwal</span>
                            @else
                                <!-- Tombol trigger modal jadwal (sama seperti sebelumnya) -->
                                <button class="text-indigo-600 font-bold text-xs border border-indigo-200 bg-indigo-50 px-3 py-1.5 rounded hover:bg-indigo-100">Set Jadwal</button>
                            @endif
                        </td>
                    </tr>
                    @empty
                     <tr><td colspan="4" class="p-6 text-center text-slate-400">Belum ada yang ACC.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL ASSIGN DOSEN (FIXED: No Blur, High Z-Index) -->
    <div x-show="modalOpen" style="display: none;" 
         class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm"
         x-transition.opacity>
        <div @click.away="modalOpen = false" class="bg-white rounded-2xl w-full max-w-lg p-0 shadow-2xl transform transition-all scale-100 overflow-hidden relative">
            <div class="bg-orange-600 px-6 py-4 flex justify-between items-center">
                <h3 class="text-white font-bold text-lg">Assign Dosen Pembimbing</h3>
                <button @click="modalOpen = false" class="text-orange-200 hover:text-white text-2xl leading-none">&times;</button>
            </div>
            
            <div class="p-6">
                <div class="mb-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <p class="text-xs text-slate-500 uppercase font-bold mb-1">Mahasiswa</p>
                    <p class="font-bold text-slate-900" x-text="sName"></p>
                    <p class="text-sm text-slate-600 mt-1 line-clamp-2" x-text="sTitle"></p>
                </div>

                <form action="{{ route('admin.assign') }}" method="POST">
                    @csrf
                    <input type="hidden" name="thesis_id" :value="selectedThesisId">
                    
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Pembimbing 1 (Wajib)</label>
                        <select name="lecturer_1_id" required class="w-full rounded-xl border-slate-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 py-2.5 bg-white">
                            <option value="">-- Pilih Dosen --</option>
                            @foreach($lecturers as $lec)
                                <option value="{{ $lec->id }}">{{ $lec->name }} ({{ $lec->nip_nim }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Pembimbing 2 (Opsional)</label>
                        <select name="lecturer_2_id" class="w-full rounded-xl border-slate-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 py-2.5 bg-white">
                            <option value="">-- Tidak Ada --</option>
                            @foreach($lecturers as $lec)
                                <option value="{{ $lec->id }}">{{ $lec->name }} ({{ $lec->nip_nim }})</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="flex gap-3">
                        <button type="button" @click="modalOpen = false" class="flex-1 py-3 px-4 border border-slate-300 rounded-xl text-slate-700 font-bold hover:bg-slate-50 transition">Batal</button>
                        <button type="submit" class="flex-1 py-3 px-4 bg-orange-600 text-white rounded-xl font-bold hover:bg-orange-700 shadow-lg shadow-orange-200 transition">Simpan Assign</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection