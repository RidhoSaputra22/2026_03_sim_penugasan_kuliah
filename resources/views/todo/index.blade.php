<x-layouts.app title="Daftar Todo">
    @php
        $statusLabel = fn ($status) => $status instanceof \App\Enums\Status
            ? $status->label()
            : (\App\Enums\Status::tryFrom((string) $status)?->label() ?? (string) $status);
    @endphp
    <div class="container">
        <h2>Daftar Todo</h2>
        @if(isset($tugasId))
            <a href="{{ route('todo.create', ['tugas_id' => $tugasId]) }}" class="btn btn-primary mb-2">Tambah Todo</a>
        @endif
        <table class="table">
            <thead>
                <tr>
                    <th>Judul</th>
                    <th>Status</th>
                    <th>Deadline</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($todos as $todo)
                <tr>
                    <td>{{ $todo->judul }}</td>
                    <td>{{ $statusLabel($todo->status) }}</td>
                    <td>{{ $todo->deadline }}</td>
                    <td>
                        <a href="{{ route('todo.show', $todo->id) }}" class="btn btn-info btn-sm">Lihat</a>
                        <a href="{{ route('todo.edit', $todo->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('todo.destroy', $todo->id) }}" method="POST" style="display:inline-block">
                            @csrf
                            @method('DELETE')
                            <x-ui.button type="error" size="sm" onclick="return confirm('Yakin hapus?')">Hapus</x-ui.button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.app>
