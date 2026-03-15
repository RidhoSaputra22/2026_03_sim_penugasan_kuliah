<x-layouts.app title="Detail Todo">
    <div class="container">
        <h2>Detail Todo</h2>
        <div class="mb-3">
            <strong>Judul:</strong> {{ $todo->judul }}
        </div>
        <div class="mb-3">
            <strong>Deskripsi:</strong> {{ $todo->deskripsi }}
        </div>
        <div class="mb-3">
            <strong>Status:</strong> {{ $todo->status }}
        </div>
        <div class="mb-3">
            <strong>Deadline:</strong> {{ $todo->deadline }}
        </div>
        <div class="mb-3">
            <strong>Tugas:</strong> <a href="{{ route('tugas.show', $todo->tugas_id) }}">Lihat Tugas</a>
        </div>
        <a href="{{ route('todo.edit', $todo->id) }}" class="btn btn-warning">Edit</a>
        <form action="{{ route('todo.destroy', $todo->id) }}" method="POST" style="display:inline-block">
            @csrf
            @method('DELETE')
            <x-ui.button type="error" onclick="return confirm('Yakin hapus?')">Hapus</x-ui.button>
        </form>
        <a href="{{ route('tugas.show', $todo->tugas_id) }}" class="btn btn-secondary">Kembali ke Tugas</a>
    </div>
</x-layouts.app>
