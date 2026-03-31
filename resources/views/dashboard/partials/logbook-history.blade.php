{{-- resources/views/dashboard/partials/logbook-history.blade.php --}}
@if($logbooks->count() > 0)
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase font-semibold">
                <tr>
                    <th class="px-4 py-3 rounded-l-lg">Tanggal</th>
                    <th class="px-4 py-3">Topik / Kegiatan</th>
                    <th class="px-4 py-3">Deskripsi Singkat</th>
                    <th class="px-4 py-3">Feedback Dosen</th>
                    <th class="px-4 py-3 rounded-r-lg text-center">File</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($logbooks as $log)
                <tr class="hover:bg-slate-50 transition">
                    <td class="px-4 py-3 whitespace-nowrap text-slate-600">{{ $log->created_at->format('d M Y') }}</td>
                    <td class="px-4 py-3 font-bold text-slate-800">{{ $log->topic }}</td>
                    <td class="px-4 py-3 text-slate-600 max-w-xs truncate">{{ Str::limit($log->description, 50) }}</td>
                    <td class="px-4 py-3 text-slate-600 italic">
                        {{ $log->feedback ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($log->file_path)
                            <a href="{{ $log->file_path }}" target="_blank" class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-800 font-medium text-xs bg-indigo-50 px-2 py-1 rounded">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                PDF
                            </a>
                        @else
                            <span class="text-slate-400 text-xs">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="text-center py-8">
        <div class="inline-block p-3 bg-slate-50 rounded-full mb-2">
            <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
        </div>
        <p class="text-slate-500 text-sm">Belum ada riwayat logbook.</p>
    </div>
@endif