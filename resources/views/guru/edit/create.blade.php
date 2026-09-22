@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Data Guru</h2>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <a href="{{ route('guru.create') }}" class="btn btn-primary mb-3">+ Tambah Guru</a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Email</th>
                    <th>No HP</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($guru as $index => $item)
                    <tr>
                        <td>{{ $guru->firstItem() + $index }}</td>
                        <td>{{ $item->nama }}</td>
                        <td>{{ $item->nip }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ $item->no_hp }}</td>
                        <td>
                            <a href="{{ route('guru.edit', $item->id_guru) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('guru.destroy', $item->id_guru) }}" method="POST" style="display:inline;"
                                onsubmit="return confirm('Yakin hapus data ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada data guru</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $guru->links() }}
    </div>
@endsection