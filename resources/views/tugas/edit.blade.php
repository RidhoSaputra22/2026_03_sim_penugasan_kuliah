<x-layouts.app title="Edit Tugas">
    <x-slot:header>
        <x-layouts.page-header title="Edit Tugas" description="Edit {{ $tugas->judul }}">
            <x-slot:actions>
                <x-ui.button type="ghost" size="sm" :href="route('tugas.index')">← Kembali</x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    <x-ui.card class="">
        @include('tugas/partials/_tugas-form', [
            'mataKuliah' => $mataKuliah,
            'tugas' => $tugas,
            'formAction' => route('tugas.update', $tugas->id),
            'method' => 'PUT',
        ])
    </x-ui.card>
</x-layouts.app>
