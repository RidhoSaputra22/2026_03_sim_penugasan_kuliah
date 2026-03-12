@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Event</h1>
    <form action="{{ route('events.update', $event) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="title" class="form-label">Judul</label>
            <input type="text" name="title" class="form-control" value="{{ $event->title }}" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Deskripsi</label>
            <textarea name="description" class="form-control">{{ $event->description }}</textarea>
        </div>
        <div class="mb-3">
            <label for="start" class="form-label">Mulai</label>
            <input type="datetime-local" name="start" class="form-control" value="{{ $event->start }}" required>
        </div>
        <div class="mb-3">
            <label for="end" class="form-label">Selesai</label>
            <input type="datetime-local" name="end" class="form-control" value="{{ $event->end }}">
        </div>
        <div class="mb-3">
            <label for="location" class="form-label">Lokasi</label>
            <input type="text" name="location" class="form-control" value="{{ $event->location }}">
        </div>
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>
@endsection
