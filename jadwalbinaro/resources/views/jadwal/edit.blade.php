@extends('layouts.app')

@section('content')
<div class="p-6">
    <!-- Breadcrumb & Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold mb-2">
                <span class="bg-emerald-100 text-emerald-700 px-2.5 py-1 rounded-md">📅 Kelola Jadwal Pelajaran</span>
                <span class="text-gray-400">Dashboard > Jadwal Pelajaran > Edit Jadwal</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">Edit Jadwal Pelajaran</h1>
            <p class="text-sm text-gray-500 mt-1">Ubah formulir di bawah untuk memperbarui jadwal mata pelajaran.</p>
        </div>
        <a href="{{ route('jadwal.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
            ← Kembali
        </a>
    </div>

    <!-- Alert Error Validasi -->
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Card Utama (Satu Kotak Utuh) -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

        <!-- Judul Sub-Header -->
        <div class="bg-gray-50/50 px-6 py-2.5 border-b border-gray-100">
            <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider">FORMULIR EDIT JADWAL</h2>
        </div>

        <!-- Form (Padding atas pt-2 dipangkas agar jarak ke 'Hari' dekat) -->
        <form action="{{ route('jadwal.update', $jadwal->id_jadwal) }}" method="POST" class="px-6 pt-2 pb-6 space-y-4">
            @csrf
            @method('PUT')

            <!-- Input Hari -->
            <div>
                <label for="hari" class="block text-sm font-semibold text-gray-700 mb-1.5">Hari</label>
                <select name="hari" id="hari" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-700" required>
                    <option value="">-- Pilih Hari --</option>
                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari)
                        <option value="{{ $hari }}" {{ old('hari', $jadwal->hari) == $hari ? 'selected' : '' }}>
                            {{ $hari }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Input Jam -->
            <div>
                <label for="jam" class="block text-sm font-semibold text-gray-700 mb-1.5">Jam Pelajaran (Format: 07.00-09.00)</label>
                <input
                    type="text"
                    name="jam"
                    id="jam"
                    value="{{ old('jam', $jadwal->jam) }}"
                    placeholder="Contoh: 07.00-08.00"
                    pattern="[0-9]{2}\.[0-9]{2}-[0-9]{2}\.[0-9]{2}"
                    title="Format jam harus sesuai contoh: 07.00-09.00"
                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-700"
                    required
                >
                <p class="text-xs text-gray-400 mt-1">*Gunakan format titik (contoh: 07.00-08.00) agar lolos validasi controller.</p>
            </div>

            <!-- Select Mata Pelajaran -->
            <div>
                <label for="id_mapel" class="block text-sm font-semibold text-gray-700 mb-1.5">Mata Pelajaran</label>
                <select name="id_mapel" id="id_mapel" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-700" required>
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    @foreach ($mapel as $m)
                        <option value="{{ $m->id_mapel }}" {{ old('id_mapel', $jadwal->id_mapel) == $m->id_mapel ? 'selected' : '' }}>
                            {{ $m->nama_mapel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Select Guru -->
            <div>
                <label for="id_guru" class="block text-sm font-semibold text-gray-700 mb-1.5">Guru Pengampu</label>
                <select name="id_guru" id="id_guru" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-700" required>
                    <option value="">-- Pilih Guru --</option>
                    @foreach ($guru as $g)
                        <option value="{{ $g->id_guru }}" {{ old('id_guru', $jadwal->id_guru) == $g->id_guru ? 'selected' : '' }}>
                            {{ $g->nama_guru }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Select Kelas -->
            <div>
                <label for="id_kelas" class="block text-sm font-semibold text-gray-700 mb-1.5">Kelas</label>
                <select name="id_kelas" id="id_kelas" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm text-gray-700" required>
                    <option value="">-- Pilih Kelas --</option>
                    @foreach ($kelas as $k)
                        <option value="{{ $k->id_rooms }}" {{ old('id_kelas', $jadwal->id_rooms) == $k->id_rooms ? 'selected' : '' }}>
                            {{ $k->pararel }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                <a href="{{ route('jadwal.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-xl text-sm font-medium transition">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition flex items-center gap-2">
                    💾 Update Jadwal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
