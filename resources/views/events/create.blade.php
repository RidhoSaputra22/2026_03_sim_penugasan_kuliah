<x-layouts.app title="Tambah Event">
    <x-slot:header>
        <x-layouts.page-header title="Tambah Event" description="Buat event kalender baru">
            <x-slot:actions>
                <x-ui.button type="ghost" size="sm" :href="route('events.index')">Kembali</x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    <x-ui.card class="max-w-3xl">
        <form action="{{ route('events.store') }}" method="POST" class="space-y-4">
            @csrf

            <x-ui.input name="title" label="Judul" :value="old('title')" :error="$errors->first('title')" :required="true" />
            <x-ui.textarea name="description" label="Deskripsi" :value="old('description')" :error="$errors->first('description')" />

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input name="start" type="datetime-local" label="Mulai" :value="old('start')"
                    :error="$errors->first('start')" :required="true" />
                <x-ui.input name="end" type="datetime-local" label="Selesai" :value="old('end')"
                    :error="$errors->first('end')" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input name="location" label="Lokasi" :value="old('location')" :error="$errors->first('location')" />
                <x-ui.input name="color" type="color" label="Warna" :value="old('color', '#2196f3')"
                    :error="$errors->first('color')" />
            </div>

            <div class="flex justify-end gap-2">
                <x-ui.button type="ghost" :href="route('events.index')" :isSubmit="false">Batal</x-ui.button>
                <x-ui.button type="success">Simpan</x-ui.button>
            </div>
        </form>
    </x-ui.card>
</x-layouts.app>
