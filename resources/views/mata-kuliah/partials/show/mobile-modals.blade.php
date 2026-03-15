<x-ui.modal id="mobile-task-modal" size="lg" :closeButton="true" :centered="false">
    <x-slot:titleSlot>
        <div>
            <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">Quick Wizard</div>
            <h3 class="mt-1 text-lg font-semibold sm:text-xl"
                x-text="quickTaskMode === 'edit' ? 'Edit Tugas' : 'Buat Tugas Baru'"></h3>
            <p class="mt-1 text-xs text-base-content/60"
                x-text="quickTaskMode === 'edit'
                    ? (selectedTask ? 'Perbarui tugas: ' + selectedTask.title : 'Perbarui tugas terpilih.')
                    : 'Tambah tugas baru tanpa keluar dari mode fokus.'">
            </p>
        </div>
    </x-slot:titleSlot>

    <div class="space-y-4">
        <div x-show="quickNotice" x-cloak class="alert py-3"
            :class="quickNotice?.type === 'error' ? 'alert-error' : 'alert-success'">
            <span x-text="quickNotice?.message"></span>
        </div>

        <form method="POST" action="{{ route('mata-kuliah.focus-task', $mataKuliah) }}" class="space-y-4"
            x-ref="quickTaskFormMobile" @submit.prevent="submitQuickTask($event)">
            @csrf

            <div x-show="quickTaskMode === 'edit'" x-cloak
                class="rounded-md border border-primary/25 bg-primary/[0.04] p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="text-[11px] font-semibold uppercase tracking-[0.2em] text-base-content/45">
                            Edit Tugas
                        </div>
                        <div class="mt-1 text-sm font-semibold text-base-content"
                            x-text="selectedTask ? selectedTask.title : ''"></div>
                        <div class="mt-1 text-xs text-base-content/60">
                            Perubahan akan disimpan ke tugas yang sedang dipilih di papan fokus.
                        </div>
                    </div>

                    <x-ui.button type="ghost" size="sm" :isSubmit="false" @click="cancelQuickTaskEdit()">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                        Batal edit
                    </x-ui.button>
                </div>
            </div>

            <x-ui.input name="task_judul" label="Judul Tugas" placeholder="Contoh: Resume pertemuan 4"
                :value="old('task_judul')" :error="$errors->quickTask->first('task_judul')" :required="true"
                ::class="quickTaskErrors.task_judul ? 'input-error' : ''" />
            <div x-show="quickTaskErrors.task_judul" class="label" x-cloak>
                <span class="label-text-alt text-error" x-text="quickTaskErrors.task_judul"></span>
            </div>

            <x-ui.textarea name="task_deskripsi" label="Deskripsi" rows="3"
                placeholder="Tambahkan konteks singkat agar tugas mudah dipahami saat dibuka lagi."
                :value="old('task_deskripsi')" :error="$errors->quickTask->first('task_deskripsi')"
                ::class="quickTaskErrors.task_deskripsi ? 'textarea-error' : ''" />
            <div x-show="quickTaskErrors.task_deskripsi" class="label" x-cloak>
                <span class="label-text-alt text-error" x-text="quickTaskErrors.task_deskripsi"></span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <x-ui.input name="task_deadline" type="date" label="Deadline"
                    :value="old('task_deadline', now()->format('Y-m-d'))"
                    :error="$errors->quickTask->first('task_deadline')"
                    ::class="quickTaskErrors.task_deadline ? 'input-error' : ''" />
                <div x-show="quickTaskErrors.task_deadline" class="label sm:col-span-2" x-cloak>
                    <span class="label-text-alt text-error" x-text="quickTaskErrors.task_deadline"></span>
                </div>

                <x-ui.select name="task_prioritas" label="Prioritas" :searchable="false"
                    :options="['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi']"
                    :value="old('task_prioritas', 'sedang')"
                    :error="$errors->quickTask->first('task_prioritas')"
                    ::class="quickTaskErrors.task_prioritas ? 'select-error' : ''" />
                <div x-show="quickTaskErrors.task_prioritas" class="label sm:col-span-2" x-cloak>
                    <span class="label-text-alt text-error" x-text="quickTaskErrors.task_prioritas"></span>
                </div>
            </div>

            <x-ui.textarea name="task_catatan" label="Catatan Internal" rows="3"
                placeholder="Contoh: fokus ke referensi jurnal terbaru atau cek rubrik penilaian."
                :value="old('task_catatan')" :error="$errors->quickTask->first('task_catatan')"
                ::class="quickTaskErrors.task_catatan ? 'textarea-error' : ''" />
            <div x-show="quickTaskErrors.task_catatan" class="label" x-cloak>
                <span class="label-text-alt text-error" x-text="quickTaskErrors.task_catatan"></span>
            </div>

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
                <x-ui.button type="ghost" size="sm" :isSubmit="false"
                    @click="closeDialog('mobile-task-modal'); $nextTick(() => openQuickTodoCreate(false))">
                    <span x-text="quickTaskMode === 'edit' ? 'Ke checklist' : 'Lewati ke checklist'"></span>
                    <x-heroicon-o-arrow-right class="h-4 w-4" />
                </x-ui.button>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center">
                    <x-ui.button type="ghost" size="sm" :isSubmit="false" @click="closeDialog('mobile-task-modal')">
                        Tutup
                    </x-ui.button>

                    <x-ui.button type="primary" size="sm" x-bind:disabled="quickTaskSubmitting"
                        ::class="quickTaskSubmitting ? 'loading' : ''">
                        <x-heroicon-o-plus class="h-4 w-4" x-show="quickTaskMode !== 'edit'" x-cloak />
                        <x-heroicon-o-check class="h-4 w-4" x-show="quickTaskMode === 'edit'" x-cloak />
                        <span x-text="quickTaskMode === 'edit' ? 'Simpan Perubahan' : 'Simpan Tugas'"></span>
                    </x-ui.button>
                </div>
            </div>

            <p x-show="quickTaskMode !== 'edit'" class="text-xs text-base-content/55" x-cloak>
                Setelah tugas tersimpan, wizard akan otomatis membuka langkah checklist.
            </p>
            <p x-show="quickTaskMode === 'edit'" class="text-xs text-base-content/55" x-cloak>
                Setelah perubahan tersimpan, lanjutkan ke langkah checklist jika ingin menambah item kerja.
            </p>
        </form>
    </div>
</x-ui.modal>

<x-ui.modal id="mobile-todo-modal" size="lg" :closeButton="true" :centered="false">
    <x-slot:titleSlot>
        <div>
            <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">Quick Wizard</div>
            <h3 class="mt-1 text-lg font-semibold sm:text-xl"
                x-text="quickTodoMode === 'edit' ? 'Edit Checklist' : 'Tambah Checklist'"></h3>
            <p class="mt-1 text-xs text-base-content/60">
                Checklist akan ditempelkan ke tugas aktif di papan fokus.
            </p>
        </div>
    </x-slot:titleSlot>

    <div class="space-y-4">
        <div x-show="quickNotice" x-cloak class="alert py-3"
            :class="quickNotice?.type === 'error' ? 'alert-error' : 'alert-success'">
            <span x-text="quickNotice?.message"></span>
        </div>

        <div x-show="tasks.length === 0"
            class="rounded-md border border-dashed border-base-300 p-5 text-sm text-base-content/60" x-cloak>
            Tambahkan tugas terlebih dahulu sebelum membuat checklist.
            <div class="mt-3">
                <x-ui.button type="ghost" size="sm" :isSubmit="false"
                    @click="closeDialog('mobile-todo-modal'); $nextTick(() => activateWorkspaceTab('action', 'task'))">
                    <x-heroicon-o-arrow-left class="h-4 w-4" />
                    Buat tugas dulu
                </x-ui.button>
            </div>
        </div>

        <form method="POST" action="{{ route('mata-kuliah.focus-todo', $mataKuliah) }}" class="space-y-4"
            x-show="tasks.length > 0" x-cloak x-ref="quickTodoFormMobile"
            @submit.prevent="submitQuickTodo($event)">
            @csrf

            <x-ui.input type="hidden" name="tugas_id" x-model="selectedTaskId" />

            <div x-show="quickTodoMode === 'edit'" x-cloak class="rounded-md border border-primary/25 bg-primary/[0.04] p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="text-[11px] font-semibold uppercase tracking-[0.2em] text-base-content/45">
                            Edit Checklist
                        </div>
                        <div class="mt-1 text-sm font-semibold text-base-content"
                            x-text="editingTodo ? editingTodo.title : ''"></div>
                        <div class="mt-1 text-xs text-base-content/60">
                            Perubahan akan disimpan ke checklist terpilih.
                        </div>
                    </div>

                    <x-ui.button type="ghost" size="sm" :isSubmit="false" @click="cancelQuickTodoEdit()">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                        Batal edit
                    </x-ui.button>
                </div>
            </div>

            <div class="rounded-md border border-base-300/70 bg-base-100 p-4"
                :class="selectedTask ? 'border-base-300/70 bg-base-100' : 'border-warning/40 bg-warning/5'">
                <div class="text-[11px] uppercase tracking-[0.2em] text-base-content/45">Tugas Aktif</div>
                <div class="mt-2 text-sm font-semibold text-base-content sm:text-base"
                    x-text="selectedTask ? selectedTask.title : 'Pilih tugas terlebih dahulu'"></div>
                <div class="mt-1 text-xs text-base-content/60 sm:text-sm"
                    x-text="selectedTask ? selectedTask.deadline_label + ' • ' + selectedTask.deadline_relative : 'Checklist akan aktif setelah tugas dipilih.'">
                </div>
            </div>

            <x-ui.input name="todo_judul" label="Judul Checklist" placeholder="Contoh: Cari 3 referensi jurnal"
                :value="old('todo_judul')" :error="$errors->quickTodo->first('todo_judul')" :required="true"
                ::class="quickTodoErrors.todo_judul ? 'input-error' : ''" />
            <div x-show="quickTodoErrors.todo_judul" class="label" x-cloak>
                <span class="label-text-alt text-error" x-text="quickTodoErrors.todo_judul"></span>
            </div>

            <x-ui.textarea name="todo_deskripsi" label="Deskripsi" rows="3"
                placeholder="Opsional, gunakan jika perlu penjelasan tambahan."
                :value="old('todo_deskripsi')" :error="$errors->quickTodo->first('todo_deskripsi')"
                ::class="quickTodoErrors.todo_deskripsi ? 'textarea-error' : ''" />
            <div x-show="quickTodoErrors.todo_deskripsi" class="label" x-cloak>
                <span class="label-text-alt text-error" x-text="quickTodoErrors.todo_deskripsi"></span>
            </div>

            <x-ui.input name="todo_deadline" type="date" label="Deadline Checklist"
                :value="old('todo_deadline')" :error="$errors->quickTodo->first('todo_deadline')"
                ::class="quickTodoErrors.todo_deadline ? 'input-error' : ''" />
            <div x-show="quickTodoErrors.todo_deadline" class="label" x-cloak>
                <span class="label-text-alt text-error" x-text="quickTodoErrors.todo_deadline"></span>
            </div>

            <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
                <x-ui.button type="ghost" size="sm" :isSubmit="false"
                    @click="closeDialog('mobile-todo-modal'); $nextTick(() => activateWorkspaceTab('action', 'task'))">
                    <x-heroicon-o-arrow-left class="h-4 w-4" />
                    Kembali
                </x-ui.button>

                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center">
                    <x-ui.button type="ghost" size="sm" :isSubmit="false" @click="closeDialog('mobile-todo-modal')">
                        Tutup
                    </x-ui.button>

                    <x-ui.button type="primary" size="sm" x-bind:disabled="!selectedTaskId || quickTodoSubmitting"
                        ::class="quickTodoSubmitting ? 'loading' : ''">
                        <x-heroicon-o-plus class="h-4 w-4" x-show="quickTodoMode !== 'edit'" x-cloak />
                        <x-heroicon-o-check class="h-4 w-4" x-show="quickTodoMode === 'edit'" x-cloak />
                        <span x-text="quickTodoMode === 'edit' ? 'Simpan Perubahan' : 'Tambah Checklist'"></span>
                    </x-ui.button>
                </div>
            </div>
        </form>
    </div>
</x-ui.modal>
