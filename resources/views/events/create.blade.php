@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Tambah Event</h1>
    <form action="{{ route('events.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="title" class="form-label">Judul</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label for="start" class="form-label">Mulai</label>
            <input type="datetime-local" name="start" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="end" class="form-label">Selesai</label>
            <input type="datetime-local" name="end" class="form-control">
        </div>
        <div class="mb-3">
            <label for="location" class="form-label">Lokasi</label>
            <input type="text" name="location" class="form-control">
        </div>
        <div class="mb-3">
            <label for="color" class="form-label">Warna</label>
            <input type="color" name="color" class="form-control" value="#2196f3">
        </div>
        <button type="submit" class="btn btn-success">Simpan</button>
    </form>
</div>
@endsection
