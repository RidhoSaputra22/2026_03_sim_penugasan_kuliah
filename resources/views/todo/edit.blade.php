<x-layouts.app title="Edit Todo">
    <x-slot:header>
        <x-layouts.page-header title="Edit Todo" description="{{ $todo->judul }}">
            <x-slot:actions>
                <x-ui.button type="ghost" size="sm" :href="route('tugas.show', $todo->tugas_id)">Kembali</x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    <x-ui.card class="max-w-3xl">
        <form action="{{ route('todo.update', $todo->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <x-ui.select name="tugas_id" label="Tugas" :required="true" placeholder="Pilih Tugas"
                :options="$tugasList->pluck('judul', 'id')->toArray()"
                :value="old('tugas_id', $todo->tugas_id)" :error="$errors->first('tugas_id')" />

            <x-ui.input name="judul" label="Judul"
                :value="old('judul', $todo->judul)" :error="$errors->first('judul')" :required="true" />

            <x-ui.textarea name="deskripsi" label="Deskripsi"
                :value="old('deskripsi', $todo->deskripsi)" :error="$errors->first('deskripsi')" />

            <x-ui.select name="status" label="Status" :searchable="false" placeholder="Pilih status"
                :options="\App\Enums\Status::taskOptions()"
                :value="old('status', optional($todo->status)->value ?? (string) $todo->status)" :error="$errors->first('status')" />

            <x-ui.input name="deadline" type="datetime-local" label="Deadline"
                :value="old('deadline', $todo->deadline ? date('Y-m-d\\TH:i', strtotime($todo->deadline)) : '')"
                :error="$errors->first('deadline')" />

            <div class="flex justify-end gap-2">
                <x-ui.button type="ghost" :href="route('tugas.show', $todo->tugas_id)" :isSubmit="false">Batal</x-ui.button>
                <x-ui.button type="primary">Update</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
