@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Edit Guru</h2>

        <form action="{{ route('guru.update', $guru->id_guru) }}" method="POST">
            @csrf
            @method('PUT')
            @include('guru._form')
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection