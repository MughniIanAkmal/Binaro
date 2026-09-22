@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Data Siswa</h2>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>NISN</th>
                <th>Email</th>
                <th>No HP</th>
                <th>Kelas</th>
            </tr>
        </thead>
        <tbody>
            @forelse($siswa as $index => $item)
            <tr>
                <td>{{ $siswa->firstItem() + $index }}</td>
                <td>{{ $item->nama }}</td>
                <td>{{ $item->nis }}</td>
                <td>{{ $item->email }}</td>
                <td>{{ $item->no_hp }}</td>
                <td>{{ $item->kelas->nama_kelas ?? '-' }}</td>
                <td>
                    <a href="{{ route('siswa.edit', $item->id_siswa) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('siswa.destroy', $item->id_siswa) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus data siswa ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="8"