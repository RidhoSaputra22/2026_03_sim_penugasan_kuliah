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
        @include('todo.partials._todo-form', [
            'formAction' => route('todo.store'),
            'submitLabel' => 'Simpan',
            'cancelUrl' => isset($tugasId) ? route('tugas.show', $tugasId) : route('todo.index'),
            'todo' => null,
        ])
    </x-ui.card>
</x-layouts.app>
