<x-layouts.app title="Detail Todo">
    @php
        $statusLabel = $todo->status instanceof \App\Enums\Status
            ? $todo->status->label()
            : (\App\Enums\Status::tryFrom((string) $todo->status)?->label() ?? (string) $todo->status);
        $attachmentUrl = $todo->attachmentUrl();
        $attachmentName = $todo->attachmentName();
        $attachmentIsImage = $todo->attachmentIsImage();
    @endphp
    <div class="container">
        <h2>Detail Todo</h2>
        <div class="mb-3">
            <strong>Judul:</strong> {{ $todo->judul }}
        </div>
        <div class="mb-3">
            <strong>Deskripsi:</strong> {{ $todo->deskripsi }}
        </div>
        <div class="mb-3">
            <strong>Status:</strong> {{ $statusLabel }}
        </div>
        <div class="mb-3">
            <strong>Deadline:</strong> {{ $todo->deadline }}
        </div>
        <div class="mb-3">
            <strong>Tugas:</strong> <a href="{{ route('tugas.show', $todo->tugas_id) }}">Lihat Tugas</a>
        </div>
        <div class="mb-3">
            <strong>Foto Checklist:</strong> {{ $attachmentName ?? 'Belum ada foto.' }}
            @if ($attachmentUrl)
                <div class="mt-2">
                    <a href="{{ $attachmentUrl }}" target="_blank" rel="noreferrer" class="btn btn-info btn-sm">Buka Foto</a>
                </div>
            @endif
        </div>
        @if ($attachmentUrl && $attachmentIsImage)
            <div class="mb-4 overflow-hidden rounded-md border border-base-300/70 bg-base-100 p-2">
                <img src="{{ $attachmentUrl }}" alt="Preview foto checklist {{ $attachmentName }}"
                    class="max-h-96 w-full object-contain object-center">
            </div>
        @endif
        <a href="{{ route('todo.edit', $todo->id) }}" class="btn btn-warning">Edit</a>
        <form action="{{ route('todo.destroy', $todo->id) }}" method="POST" style="display:inline-block">
            @csrf
            @method('DELETE')
            <x-ui.button type="error" onclick="return confirm('Yakin hapus?')">Hapus</x-ui.button>
        </form>
        <a href="{{ route('tugas.show', $todo->tugas_id) }}" class="btn btn-secondary">Kembali ke Tugas</a>
    </div>
</x-layouts.app>
