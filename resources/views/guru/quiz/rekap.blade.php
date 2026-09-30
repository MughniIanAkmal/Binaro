@extends('layouts.guru')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Rekap Nilai Kuis Siswa</h1>
            <p class="text-xs text-slate-500">Pantau hasil pengerjaan kuis siswa SDN Kalitapen 01</p>
        </div>

        <div class="flex items-center gap-2">
            <!-- Filter Kuis -->
            <form method="GET" action="{{ route('guru.quiz.rekap') }}" class="flex items-center gap-2">
                <select name="quiz_id" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-700 focus:outline-none focus:border-[#13527D]">
                    <option value="">-- Pilih Kuis --</option>
                    @foreach($quizzes as $q)
                        <option value="{{ $q->id_quiz }}" {{ $selectedQuizId == $q->id_quiz ? 'selected' : '' }}>
                            {{ $q->judul_quiz }} ({{ $q->subBab?->bab?->mataPelajaran?->nama_mapel ?? '-' }})
                        </option>
                    @endforeach
                </select>
            </form>

            @if($selectedQuizId && $hasils->isNotEmpty())
            <a href="{{ route('guru.quiz.export', $selectedQuizId) }}" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg transition flex items-center gap-1.5 shadow-sm">
                <i class="fas fa-file-export"></i> Export Rekap (.csv)
            </a>
            @endif
        </div>
    </div>

    @if(!$selectedQuizId)
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center text-xs text-slate-400">
        Silakan pilih Kuis di atas untuk melihat daftar nilai siswa.
    </div>
    @else
    <!-- Table Nilai -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-700 font-bold">
                        <th class="p-3.5 w-12 text-center">No</th>
                        <th class="p-3.5">NISN</th>
                        <th class="p-3.5">Nama Siswa</th>
                        <th class="p-3.5 text-center">Benar</th>
                        <th class="p-3.5 text-center">Salah</th>
                        <th class="p-3.5 text-center">Nilai Akhir</th>
                        <th class="p-3.5 text-right">Tanggal Submit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($hasils as $idx => $row)
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="p-3.5 text-center font-bold text-slate-400">{{ $idx + 1 }}</td>
                        <td class="p-3.5 font-mono text-slate-600">{{ $row->siswa?->nisn ?? '-' }}</td>
                        <td class="p-3.5 font-semibold text-slate-900">{{ $row->siswa?->nm_siswa ?? '-' }}</td>
                        <td class="p-3.5 text-center text-emerald-600 font-bold">{{ $row->jumlah_benar }}</td>
                        <td class="p-3.5 text-center text-rose-600 font-bold">{{ $row->jumlah_salah }}</td>
                        <td class="p-3.5 text-center">
                            <span class="px-2.5 py-1 rounded-lg font-black {{ $row->nilai_akhir >= 70 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                                {{ number_format($row->nilai_akhir, 0) }}
                            </span>
                        </td>
                        <td class="p-3.5 text-right text-slate-400 text-[11px]">
                            {{ $row->created_at?->format('d M Y H:i') ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400 italic">
                            Belum ada siswa yang mengerjakan kuis ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
