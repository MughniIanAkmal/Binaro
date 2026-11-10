@extends('layouts.app')

@section('content')
<div class="p-6">
    <!-- Header Halaman -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Jadwal Mata Pelajaran</h1>
            <p class="text-sm text-gray-500 mt-1">Data jadwal pembelajaran</p>
        </div>
        <a href="{{ route('jadwal.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
            + Tambah Jadwal
        </a>
    </div>

    <!-- Alert Sukses -->
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <!-- Tabel Data Jadwal -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        <th class="px-6 py-4">No</th>
                        <th class="px-6 py-4">Hari</th>
                        <th class="px-6 py-4">Jam</th>
                        <th class="px-6 py-4">Mata Pelajaran</th>
                        <th class="px-6 py-4">Guru</th>
                        <th class="px-6 py-4">Kelas</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                    @forelse ($jadwals as $jadwal)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 font-medium">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">{{ $jadwal->hari }}</td>
                            <td class="px-6 py-4 font-mono text-xs">{{ $jadwal->jam }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-900">{{ $jadwal->nama_mapel }}</td>
                            <td class="px-6 py-4">{{ $jadwal->nama_guru }}</td>
                            <td class="px-6 py-4">
                                <span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-md text-xs font-medium">
                                    {{ $jadwal->nama_kelas }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('jadwal.edit', $jadwal->id_jadwal) }}" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-md text-xs font-medium transition inline-block">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400">
                                Belum ada data jadwal.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
