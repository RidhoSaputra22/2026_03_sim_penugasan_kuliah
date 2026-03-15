<x-ui.modal id="task-focus-modal" size="xl" :closeButton="true" :centered="false">
    <x-slot:titleSlot>
        <div>
            <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">Workspace Tugas</div>
            <h3 class="mt-1 text-lg font-semibold sm:text-xl" x-text="selectedTask?.title || 'Belum ada tugas terpilih'"></h3>
        </div>
    </x-slot:titleSlot>

    <div x-show="selectedTask" class="max-h-[75vh] space-y-5 overflow-y-auto pr-1 sm:max-h-[80vh]" x-cloak>
        <div class="flex flex-wrap items-center gap-2">
            <span class="badge badge-outline badge-sm" :class="statusClass(selectedTask?.status)"
                x-text="selectedTask?.status_label"></span>
            <span class="badge badge-sm" :class="priorityClass(selectedTask?.priority)"
                x-text="priorityLabel(selectedTask?.priority)"></span>
        </div>

        <p class="text-xs leading-5 text-base-content/70 sm:text-sm sm:leading-6"
            x-text="selectedTask?.description || 'Belum ada deskripsi untuk tugas ini.'"></p>

        <div class="grid gap-4 lg:grid-cols-[1fr_1.1fr]">
            <div class="space-y-4">
                <div class="rounded-md border border-base-300/70 bg-base-100 p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">
                                Status Deadline
                            </div>
                            <div class="mt-1 text-base font-semibold text-base-content sm:text-lg"
                                x-text="selectedTask?.deadline_label"></div>
                        </div>
                        <div class="text-sm font-medium"
                            :class="selectedTask?.is_overdue ? 'text-error' : (selectedTask?.is_due_soon ? 'text-warning' : 'text-base-content/65')"
                            x-text="selectedTask?.deadline_relative"></div>
                    </div>
                </div>

                <div class="rounded-md border border-base-300/70 bg-base-100 p-4">
                    <div class="flex items-center justify-between text-sm font-medium">
                        <span>Progress tugas</span>
                        <span class="text-base font-semibold text-primary sm:text-lg"
                            x-text="selectedTask?.progress + '%'"></span>
                    </div>
                    <progress class="progress progress-primary mt-3 h-3 w-full"
                        :value="selectedTask?.progress || 1" max="100"></progress>
                </div>

                <div x-show="selectedTask?.note"
                    class="rounded-md border border-base-300/70 bg-base-100 p-4" x-cloak>
                    <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">
                        Catatan Tugas
                    </div>
                    <pre class="mt-3 whitespace-pre-wrap font-sans text-sm leading-6 text-base-content/70"
                        x-text="selectedTask?.note"></pre>
                </div>
            </div>

            <div class="rounded-md border border-base-300/70 bg-base-100 p-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">
                            Checklist Tugas
                        </div>
                        <div class="mt-1 text-sm font-semibold text-base-content sm:text-base">
                            Fokus eksekusi detail
                        </div>
                    </div>

                    <x-ui.button type="ghost" size="sm" :isSubmit="false"
                        @click="closeDialog('task-focus-modal'); activateWorkspaceTab('action', 'todo')">
                        <x-heroicon-o-plus class="h-4 w-4" />
                        Tambah Checklist
                    </x-ui.button>
                </div>

                <div x-show="selectedTask?.todos?.length === 0"
                    class="mt-4 rounded-md border border-dashed border-base-300 p-6 text-center text-sm text-base-content/60"
                    x-cloak>
                    Belum ada checklist. Tambahkan item baru agar progres lebih terukur.
                </div>

                <div x-show="selectedTask?.todos?.length > 0" class="mt-4 space-y-3" x-cloak>
                    <template x-for="todo in selectedTask?.todos || []" :key="todo.id">
                        <label
                            class="flex cursor-pointer items-start gap-3 rounded-md border p-4 transition"
                            :class="todo.status === doneStatus
                                ? 'border-success/30 bg-success/5'
                                : 'border-base-300/70 bg-base-100 hover:border-primary/30'">
                            <input type="checkbox" class="checkbox checkbox-primary mt-0.5"
                                :checked="todo.status === doneStatus"
                                @change="toggleTodo(selectedTask.id, todo.id, $event.target.checked)">

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="font-medium text-base-content" x-text="todo.title"></div>
                                    <span class="badge badge-xs"
                                        :class="todo.status === doneStatus ? 'badge-success' : 'badge-warning'"
                                        x-text="todo.status_label"></span>
                                </div>
                                <p class="mt-1 text-xs leading-5 text-base-content/65 sm:text-sm sm:leading-6"
                                    x-text="todo.description || 'Tidak ada deskripsi tambahan.'"></p>
                                <div x-show="todo.deadline_label"
                                    class="mt-2 text-xs uppercase tracking-wide text-base-content/45"
                                    x-text="'Deadline checklist: ' + todo.deadline_label"></div>
                            </div>
                        </label>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div x-show="!selectedTask" class="rounded-md border border-dashed border-base-300 p-6 text-center text-sm text-base-content/60"
        x-cloak>
        Pilih tugas dari papan fokus terlebih dahulu.
    </div>
</x-ui.modal>
