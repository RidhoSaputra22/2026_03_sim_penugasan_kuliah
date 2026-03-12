<x-layouts.app title="Tambah Todo">
    <div class="container">
        <h2>Tambah Todo</h2>
        <form action="{{ route('todo.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="tugas_id" class="form-label">Tugas</label>
                <select name="tugas_id" id="tugas_id" class="form-control" required>
                    <option value="">Pilih Tugas</option>
                    @foreach($tugasList as $tugas)
                        <option value="{{ $tugas->id }}" {{ (isset($tugasId) && $tugasId == $tugas->id) ? 'selected' : '' }}>{{ $tugas->judul }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label for="judul" class="form-label">Judul</label>
                <input type="text" name="judul" id="judul" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="deskripsi" class="form-label">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <input type="text" name="status" id="status" class="form-control" value="pending">
            </div>
            <div class="mb-3">
                <label for="deadline" class="form-label">Deadline</label>
                <input type="datetime-local" name="deadline" id="deadline" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
</x-layouts.app>
