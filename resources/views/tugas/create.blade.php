<x-layouts.app title="Tambah Tugas">
    <x-slot:header>
        <x-layouts.page-header title="Tambah Tugas" description="Buat tugas baru">
            <x-slot:actions>
                <x-ui.button type="ghost" size="sm" :href="route('tugas.index')">← Kembali</x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    <x-ui.card class="">
         @include('tugas/partials/_tugas-form', [
            'mataKuliah' => $mataKuliah,
            'formAction' => route('tugas.store'),
            'method' => 'POST',
            'tugas' => null,
        ])

    </x-ui.card>
</x-layouts.app>
