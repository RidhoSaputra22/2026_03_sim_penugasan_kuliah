<x-layouts.app title="Tambah Todo">
    <x-slot:header>
        <x-layouts.page-header title="Tambah Todo" description="Tambahkan checklist baru ke tugas">
            <x-slot:actions>
                <x-ui.button type="ghost" size="sm" :href="isset($tugasId) ? route('tugas.show', $tugasId) : route('todo.index')">
                    Kembali
                </x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    <x-ui.card class="max-w-3xl">
        <form action="{{ route('todo.store') }}" method="POST" class="space-y-4">
            @csrf
            <x-ui.select name="tugas_id" label="Tugas" :required="true" placeholder="Pilih Tugas"
                :options="$tugasList->pluck('judul', 'id')->toArray()"
                :value="old('tugas_id', $tugasId ?? '')" :error="$errors->first('tugas_id')" />

            <x-ui.input name="judul" label="Judul" :required="true" :value="old('judul')" :error="$errors->first('judul')" />

            <x-ui.textarea name="deskripsi" label="Deskripsi"
                :value="old('deskripsi')" :error="$errors->first('deskripsi')" />

            <x-ui.input name="status" label="Status"
                :value="old('status', 'pending')" :error="$errors->first('status')" />

            <x-ui.input name="deadline" type="datetime-local" label="Deadline"
                :value="old('deadline')" :error="$errors->first('deadline')" />

            <div class="flex justify-end gap-2">
                <x-ui.button type="ghost" :href="isset($tugasId) ? route('tugas.show', $tugasId) : route('todo.index')" :isSubmit="false">
                    Batal
                </x-ui.button>
                <x-ui.button type="primary">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
