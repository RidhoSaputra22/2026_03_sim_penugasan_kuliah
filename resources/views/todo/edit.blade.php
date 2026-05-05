<x-layouts.app title="Edit Todo">
    <x-slot:header>
        <x-layouts.page-header title="Edit Todo" description="{{ $todo->judul }}">
            <x-slot:actions>
                <x-ui.button type="ghost" size="sm" :href="route('tugas.show', $todo->tugas_id)">Kembali</x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    <x-ui.card class="max-w-3xl">
        @include('todo.partials._todo-form', [
            'formAction' => route('todo.update', $todo->id),
            'method' => 'PUT',
            'submitLabel' => 'Update',
            'cancelUrl' => route('tugas.show', $todo->tugas_id),
            'todo' => $todo,
        ])
    </x-ui.card>
</x-layouts.app>
