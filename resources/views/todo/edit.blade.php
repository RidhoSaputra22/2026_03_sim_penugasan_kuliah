<x-layouts.app title="Edit Todo">
    <div class="container">
        <h2>Edit Todo</h2>
        <form action="{{ route('todo.update', $todo->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="tugas_id" class="form-label">Tugas</label>
                <select name="tugas_id" id="tugas_id" class="form-control" required>
                    <option value="">Pilih Tugas</option>
                    @foreach($tugasList as $tugas)
                        <option value="{{ $tugas->id }}" {{ ($todo->tugas_id == $tugas->id) ? 'selected' : '' }}>{{ $tugas->judul }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="judul" class="form-label">Judul</label>
                <input type="text" name="judul" id="judul" class="form-control" value="{{ $todo->judul }}" required>
            </div>
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control">{{ $todo->deskripsi }}</textarea>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <input type="text" name="status" id="status" class="form-control" value="{{ $todo->status }}">
            </div>
            <div class="mb-3">
                <label for="deadline" class="form-label">Deadline</label>
                <input type="datetime-local" name="deadline" id="deadline" class="form-control" value="{{ $todo->deadline ? date('Y-m-d\TH:i', strtotime($todo->deadline)) : '' }}">
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
</x-layouts.app>
