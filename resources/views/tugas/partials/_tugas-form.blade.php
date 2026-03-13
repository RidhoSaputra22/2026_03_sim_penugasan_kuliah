{{-- resources/views/tugas/_tugas-form.blade.php --}}
<form method="POST" action="{{ $formAction }}" class="space-y-4" enctype="multipart/form-data">
    @csrf
    @if(isset($method) && $method === 'PUT')
        @method('PUT')
    @endif
    <input type="hidden" name="MAX_FILE_SIZE" value="10485760" />


    <x-ui.select name="mata_kuliah_id" label="Mata Kuliah" :required="true" placeholder="Pilih mata kuliah"
        :options="$mataKuliah->pluck('nama', 'id')->toArray()" :value="optional($tugas)->mata_kuliah_id ?? old('mata_kuliah_id') ?? ''" />

    <x-ui.input name="judul" label="Judul Tugas" placeholder="Contoh: Makalah Kecerdasan Buatan"
        :required="true" :value="optional($tugas)->judul ?? old('judul') ?? ''" />

    <x-ui.textarea name="deskripsi" label="Deskripsi" placeholder="Deskripsi tugas (opsional)"
        :rows="4" :value="optional($tugas)->deskripsi ?? old('deskripsi') ?? ''" />

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <x-ui.input name="deadline" label="Deadline" type="date" :required="true"
            :value="optional($tugas)->deadline ? \Carbon\Carbon::parse(optional($tugas)->deadline)->format('Y-m-d') : old('deadline') ?? ''" />
        <x-ui.select name="status" label="Status" :searchable="false" :required="true"
            placeholder="Pilih status"
            :options="collect(\App\Enums\Status::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])->toArray()"
            :value="optional($tugas)->status?->value ?? old('status', \App\Enums\Status::BELUM->value)" />
        <x-ui.input name="progress" label="Progress (%)" type="number" placeholder="0" :required="true"
            :value="optional($tugas)->progress ?? old('progress', 0)" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <x-ui.select name="prioritas" label="Prioritas" :required="true" placeholder="Pilih prioritas"
            :options="['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi']"
            :value="optional($tugas)->prioritas ?? old('prioritas', 'sedang')" />
        <x-ui.input name="file" label="Upload File (PDF/IMG)" type="file"
            accept="application/pdf,image/*" :value="optional($tugas)->file ?? ''" />
    </div>

    <x-ui.textarea name="catatan" label="Catatan" placeholder="Catatan tambahan (opsional)" :value="optional($tugas)->catatan ?? old('catatan') ?? ''" />

    {{-- Input Todo Dinamis --}}
    @php
        $todosJson = json_encode(old('todos', isset($tugas) && $tugas->todos ? $tugas->todos->map(fn($todo) => [
            'judul' => $todo->judul,
            'deskripsi' => $todo->deskripsi,
            'status' => $todo->status,
            'deadline' => $todo->deadline ? \Carbon\Carbon::parse($todo->deadline)->format('Y-m-d') : '',
        ])->toArray() : [[
            'judul' => '',
            'deskripsi' => '',
            'status' => 'pending',
            'deadline' => ''
        ]]) );
    @endphp
    <div x-data='{
        todos: {!! $todosJson !!},
        addTodo() {
            this.todos.push({ judul: "", deskripsi: "", status: "pending", deadline: "" });
        },
        removeTodo(idx) {
            if (this.todos.length > 1) this.todos.splice(idx, 1);
        }
    }' class="space-y-4">
        <label class="block font-semibold text-base-content/80 mb-2">Todo List</label>
        <template x-for="(todo, idx) in todos" :key="idx">
            <div class="card   relative">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-ui.input name="todo_dummy[]" x-bind:name="`todos[${idx}][judul]`" label="Judul Todo" x-model="todo.judul"
                        placeholder="Judul todo" :required="false" />
                </div>
                <x-ui.textarea name="todo_dummy[]" x-bind:name="`todos[${idx}][deskripsi]`" label="Deskripsi Todo" x-model="todo.deskripsi"
                    placeholder="Deskripsi todo (opsional)" :rows="2" />
                <div class="flex items-center gap-2 mt-2">
                    <button type="button" class="ml-auto text-error" @click="removeTodo(idx)"
                        x-show="todos.length > 1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </template>
        <x-ui.button type="primary" size="sm" @click.prevent="addTodo" class="mt-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Todo
        </x-ui.button>
    </div>

    <div class="flex justify-end gap-2 pt-4">
        <x-ui.button type="ghost" :href="$cancelUrl ?? route('tugas.index')" :isSubmit="false">Batal</x-ui.button>
        <x-ui.button type="primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ $submitLabel ?? 'Simpan Tugas' }}
        </x-ui.button>
    </div>
</form>
