<div class="space-y-6">
    <x-ui.card id="task-board" compact class="border border-base-300/50">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-base font-semibold sm:text-lg">Papan Fokus Tugas</h3>
                    <p class="text-xs text-base-content/60 sm:text-sm">
                        Filter, cari, lalu pilih tugas yang ingin dijadikan pusat kerja saat ini.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <x-ui.button type="ghost" size="sm" :isSubmit="false"
                        ::class="taskFilter === 'all' ? 'btn-primary' : 'btn-ghost'"
                        @click="taskFilter = 'all'">
                        Semua
                        <span class="badge badge-ghost badge-sm" x-text="countByFilter('all')"></span>
                    </x-ui.button>
                    <x-ui.button type="ghost" size="sm" :isSubmit="false"
                        ::class="taskFilter === 'active' ? 'btn-primary' : 'btn-ghost'"
                        @click="taskFilter = 'active'">
                        Aktif
                        <span class="badge badge-ghost badge-sm" x-text="countByFilter('active')"></span>
                    </x-ui.button>
                    <x-ui.button type="ghost" size="sm" :isSubmit="false"
                        ::class="taskFilter === 'soon' ? 'btn-primary' : 'btn-ghost'"
                        @click="taskFilter = 'soon'">
                        Dekat
                        <span class="badge badge-ghost badge-sm" x-text="countByFilter('soon')"></span>
                    </x-ui.button>
                    <x-ui.button type="ghost" size="sm" :isSubmit="false"
                        ::class="taskFilter === 'done' ? 'btn-primary' : 'btn-ghost'"
                        @click="taskFilter = 'done'">
                        Selesai
                        <span class="badge badge-ghost badge-sm" x-text="countByFilter('done')"></span>
                    </x-ui.button>
                </div>
            </div>

            <x-ui.input name="task_search" placeholder="Cari judul, deskripsi, catatan, atau checklist..."
                x-model="taskQuery" />

            <div x-show="tasks.length === 0" class="rounded-md border border-dashed border-base-300 p-8 text-center"
                x-cloak>
                <div class="text-base font-semibold sm:text-lg">Belum ada tugas untuk mata kuliah ini</div>
                <p class="mt-2 text-xs text-base-content/60 sm:text-sm">
                    Tambahkan tugas pertama dari panel kanan agar mode fokus mulai terisi.
                </p>
                <x-ui.button type="primary" size="sm" :isSubmit="false" class="mt-4 hidden sm:inline-flex"
                    @click="activateWorkspaceTab('action', 'task')">
                    <x-heroicon-o-plus class="h-4 w-4" />
                    Buat Tugas Pertama
                </x-ui.button>
            </div>

            <div x-show="tasks.length > 0 && filteredTasks.length === 0"
                class="rounded-md border border-dashed border-base-300 p-8 text-center" x-cloak>
                <div class="text-base font-semibold sm:text-lg">Tidak ada tugas yang cocok</div>
                <p class="mt-2 text-xs text-base-content/60 sm:text-sm">
                    Coba ubah filter atau kata kunci pencarian.
                </p>
            </div>

            <div x-show="filteredTasks.length > 0" class="grid gap-3" x-cloak>
                <template x-for="task in filteredTasks" :key="task.id">
                    <button type="button"
                        class="w-full rounded-md border px-3.5 py-2.5 text-left transition duration-200"
                        :class="selectedTaskId === task.id
                            ? 'border-primary bg-primary/5 shadow-lg shadow-primary/10'
                            : 'border-base-300/70 bg-base-100 hover:border-primary/30 hover:bg-base-200/35'"
                        @click="selectedTaskId = task.id">
                        <div class="flex flex-col gap-2.5 xl:flex-row xl:items-center xl:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="badge badge-outline badge-sm"
                                        :class="statusClass(task.status)" x-text="task.status_label"></span>
                                    <span class="badge badge-sm" :class="priorityClass(task.priority)"
                                        x-text="priorityLabel(task.priority)"></span>


                                </div>

                                <div class="mt-1.5 flex flex-col gap-2 xl:flex-row xl:items-center xl:justify-between">
                                    <div class="min-w-0">
                                        <div class="truncate text-sm font-semibold text-base-content sm:text-base"
                                            x-text="task.title"></div>
                                        <p class="mt-0.5 truncate text-xs text-base-content/65 sm:text-sm"
                                            x-text="truncate(task.description || 'Belum ada deskripsi tugas.', 88)">
                                        </p>
                                    </div>

                                    <div class="xl:hidden">
                                        <div class="flex items-center justify-between text-[11px] uppercase tracking-wide text-base-content/45">
                                            <span>Progress</span>
                                            <span x-text="task.progress + '%'"></span>
                                        </div>
                                        <progress class="progress progress-primary mt-2 h-1.5 w-full"
                                            value="0" :value="task.progress ?? 0" max="100"></progress>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-2.5 xl:min-w-[18.5rem] xl:grid-cols-[9rem_minmax(0,1fr)]">
                                <div class="rounded-md bg-base-200/70 px-3 py-2 text-xs sm:text-sm">
                                    <div class="text-[11px] uppercase tracking-wide text-base-content/45">Deadline</div>
                                    <div class="mt-1 font-semibold text-base-content"
                                        x-text="task.deadline_label"></div>
                                    <div class="mt-0.5 text-xs"
                                        :class="task.is_overdue ? 'text-error' : (task.is_due_soon ? 'text-warning' : 'text-base-content/60')"
                                        x-text="task.deadline_relative"></div>
                                </div>

                                <div class="hidden rounded-md bg-base-200/50 px-3 py-2 xl:block">
                                    <div class="flex items-center justify-between text-[11px] uppercase tracking-wide text-base-content/45">
                                        <span>Progress</span>
                                        <span x-text="task.progress + '%'"></span>
                                    </div>
                                    <progress class="progress progress-primary mt-2 h-1.5 w-full"
                                        value="0" :value="task.progress ?? 0" max="100"></progress>
                                    <div class="mt-2 text-[11px] text-base-content/55"
                                        x-text="task.todo_completed_count + '/' + task.todo_count + ' checklist selesai'"></div>
                                </div>
                            </div>
                        </div>
                    </button>
                </template>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card id="task-detail-card" compact class="border border-base-300/50">
        <div x-show="selectedTask" x-cloak class="space-y-5">
            <div x-show="taskEditorNotice" x-cloak class="alert py-3"
                :class="taskEditorNotice?.type === 'error' ? 'alert-error' : 'alert-success'">
                <span x-text="taskEditorNotice?.message"></span>
            </div>

            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                <div class="min-w-0 flex-1">
                        <div class="text-[11px] font-semibold uppercase tracking-[0.22em] text-base-content/45">
                            Info Detail Tugas
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="badge badge-outline badge-sm" :class="statusClass(selectedTask?.status)"
                                x-text="selectedTask?.status_label"></span>
                            <span class="badge badge-sm" :class="priorityClass(selectedTask?.priority)"
                                x-text="priorityLabel(selectedTask?.priority)"></span>
                            <span x-show="selectedTask?.attendance_label" class="badge badge-sm badge-info" x-cloak
                                x-text="selectedTask?.attendance_label"></span>
                        </div>

                        <h3 class="mt-3 text-lg font-semibold text-base-content sm:text-xl"
                            x-text="selectedTask?.title"></h3>
                        <p class="mt-2 text-xs leading-5 text-base-content/65 sm:text-sm sm:leading-6"
                            x-text="selectedTask?.description || 'Belum ada deskripsi untuk tugas ini.'"></p>
                    </div>

                <div class="flex flex-wrap gap-2">
                        <x-ui.button type="ghost" size="sm" :isSubmit="false"
                            @click="activateWorkspaceTab('action', 'todo')">
                            <x-heroicon-o-list-bullet class="h-4 w-4" />
                            Tambah Checklist
                        </x-ui.button>

                        <x-ui.button type="primary" size="sm" :isSubmit="false" @click="editSelectedTaskInWizard()">
                            <x-heroicon-o-pencil-square class="h-4 w-4" />
                            Edit
                        </x-ui.button>
                        <x-ui.button type="ghost" size="sm" :isSubmit="false" class="text-error"
                            x-bind:disabled="taskDeleting" ::class="taskDeleting ? 'loading' : ''"
                            @click="deleteSelectedTask()">
                            <x-heroicon-o-trash class="h-4 w-4" />
                            Hapus
                        </x-ui.button>
                </div>
            </div>

            <div class="grid gap-3 lg:grid-cols-2 2xl:grid-cols-4">
                <div class="rounded-md border border-base-300/70 bg-base-100 px-3 py-3 text-xs sm:text-sm">
                    <div class="text-[11px] uppercase tracking-[0.18em] text-base-content/45">Deadline</div>
                    <div class="mt-1.5 font-semibold text-base-content" x-text="selectedTask?.deadline_label"></div>
                    <div class="mt-1 text-xs"
                        :class="selectedTask?.is_overdue ? 'text-error' : (selectedTask?.is_due_soon ? 'text-warning' : 'text-base-content/60')"
                        x-text="selectedTask?.deadline_relative"></div>
                </div>

                <div class="rounded-md border border-base-300/70 bg-base-100 px-3 py-3 text-xs sm:text-sm">
                    <div class="flex items-center justify-between text-[11px] uppercase tracking-[0.18em] text-base-content/45">
                        <span>Progress</span>
                        <span class="font-semibold text-primary" x-text="selectedTask?.progress + '%'"></span>
                    </div>
                    <progress class="progress progress-primary mt-2 h-1.5 w-full"
                        value="0" :value="selectedTask?.progress ?? 0" max="100"></progress>
                    <div class="mt-1.5 text-xs text-base-content/60" x-text="selectedTask?.status_label"></div>
                </div>

                <div class="rounded-md border border-base-300/70 bg-base-100 px-3 py-3 text-xs sm:text-sm">
                    <div class="text-[11px] uppercase tracking-[0.18em] text-base-content/45">Checklist</div>
                    <div class="mt-1.5 font-semibold text-base-content"
                        x-text="selectedTask?.todo_completed_count + '/' + selectedTask?.todo_count"></div>
                    <x-ui.button type="link" size="sm" :isSubmit="false" class="mt-1 h-auto min-h-0 px-0 text-xs"
                        @click="activateWorkspaceTab('action', 'todo')">
                        Kelola
                    </x-ui.button>
                </div>

                <div class="rounded-md border border-base-300/70 bg-base-100 px-3 py-3 text-xs sm:text-sm">
                    <div class="text-[11px] uppercase tracking-[0.18em] text-base-content/45">Pertemuan</div>
                    <div class="mt-1.5 truncate font-semibold text-base-content"
                        x-text="selectedTask?.attendance_label || 'Belum ditautkan'"></div>
                    <x-ui.button type="link" size="sm" :isSubmit="false" class="mt-1 h-auto min-h-0 px-0 text-xs"
                        x-show="selectedTask?.attendance_id" x-cloak
                        @click="focusAttendance(selectedTask.attendance_id)">
                        Buka absensi
                    </x-ui.button>
                </div>
            </div>

            <div x-show="selectedTask?.note" class="rounded-md border border-base-300/70 bg-base-100 p-4" x-cloak>
                <div class="text-[11px] uppercase tracking-[0.18em] text-base-content/45">Catatan Tugas</div>
                <pre class="mt-3 whitespace-pre-wrap font-sans text-xs leading-5 text-base-content/70 sm:text-sm sm:leading-6"
                    x-text="selectedTask?.note"></pre>
            </div>

            <div class="rounded-md border border-base-300/70 bg-base-100 p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <div class="text-[11px] uppercase tracking-[0.18em] text-base-content/45">Checklist Tugas</div>
                        <div class="mt-1 text-sm font-semibold text-base-content sm:text-base">
                            Semua item kerja untuk tugas terpilih tersimpan di sini.
                        </div>
                    </div>

                    <x-ui.button type="ghost" size="sm" :isSubmit="false"
                        @click="activateWorkspaceTab('action', 'todo')">
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
                        <div
                            class="flex items-start gap-3 rounded-md border p-4 transition"
                            :class="todo.status === doneStatus
                                ? 'border-success/30 bg-success/5'
                                : 'border-base-300/70 bg-base-100 hover:border-primary/30'">
                            <x-ui.checkbox :bare="true" class="mt-0.5"
                                x-bind:checked="todo.status === doneStatus"
                                x-bind:aria-label="'Tandai checklist ' + todo.title"
                                @change="toggleTodo(selectedTask.id, todo.id, $event.target.checked)" />

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                    <div class="min-w-0">
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

                                    <div class="flex items-start gap-1">
                                        <x-ui.button type="ghost" size="xs" :isSubmit="false"
                                            @click="editTodoInWizard(todo)">
                                            <x-heroicon-o-pencil-square class="h-4 w-4" />
                                        </x-ui.button>
                                        <x-ui.button type="ghost" size="xs" :isSubmit="false" class="text-error"
                                            x-bind:disabled="todoDeletingId === todo.id"
                                            ::class="todoDeletingId === todo.id ? 'loading' : ''"
                                            @click="deleteTodo(todo)">
                                            <x-heroicon-o-trash class="h-4 w-4" />
                                        </x-ui.button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <div x-show="!selectedTask" class="rounded-md border border-dashed border-base-300 p-8 text-center"
            x-cloak>
            <div class="text-base font-semibold sm:text-lg">Belum ada tugas terpilih</div>
            <p class="mt-2 text-xs text-base-content/60 sm:text-sm">
                Pilih tugas dari papan fokus atau tambahkan tugas baru dari panel aksi.
            </p>
        </div>
    </x-ui.card>
</div>
