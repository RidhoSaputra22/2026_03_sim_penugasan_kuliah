<div class="space-y-6">
    <x-ui.card id="task-board" class="border border-base-300/50">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h3 class="text-base font-semibold sm:text-lg">Papan Fokus Tugas</h3>
                    <p class="text-xs text-base-content/60 sm:text-sm">
                        Filter, cari, lalu pilih tugas yang ingin dijadikan pusat kerja saat ini.
                    </p>
                </div>

                <div class="flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm"
                        :class="taskFilter === 'all' ? 'btn-primary' : 'btn-ghost'"
                        @click="taskFilter = 'all'">
                        Semua
                        <span class="badge badge-ghost badge-sm" x-text="countByFilter('all')"></span>
                    </button>
                    <button type="button" class="btn btn-sm"
                        :class="taskFilter === 'active' ? 'btn-primary' : 'btn-ghost'"
                        @click="taskFilter = 'active'">
                        Aktif
                        <span class="badge badge-ghost badge-sm" x-text="countByFilter('active')"></span>
                    </button>
                    <button type="button" class="btn btn-sm"
                        :class="taskFilter === 'soon' ? 'btn-primary' : 'btn-ghost'"
                        @click="taskFilter = 'soon'">
                        Dekat
                        <span class="badge badge-ghost badge-sm" x-text="countByFilter('soon')"></span>
                    </button>
                    <button type="button" class="btn btn-sm"
                        :class="taskFilter === 'done' ? 'btn-primary' : 'btn-ghost'"
                        @click="taskFilter = 'done'">
                        Selesai
                        <span class="badge badge-ghost badge-sm" x-text="countByFilter('done')"></span>
                    </button>
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
                        class="w-full rounded-md border px-4 py-3 text-left transition duration-200"
                        :class="selectedTaskId === task.id
                            ? 'border-primary bg-primary/5 shadow-lg shadow-primary/10'
                            : 'border-base-300/70 bg-base-100 hover:border-primary/30 hover:bg-base-200/35'"
                        @click="selectedTaskId = task.id">
                        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="badge badge-outline badge-sm"
                                        :class="statusClass(task.status)" x-text="task.status_label"></span>
                                    <span class="badge badge-sm" :class="priorityClass(task.priority)"
                                        x-text="priorityLabel(task.priority)"></span>
                                    <span class="badge badge-ghost badge-sm"
                                        x-text="task.todo_completed_count + '/' + task.todo_count + ' checklist'"></span>
                                    <span x-show="task.attendance_label" x-cloak
                                        class="badge badge-sm badge-info"
                                        x-text="task.attendance_label"></span>
                                </div>

                                <div class="mt-2 flex flex-col gap-2 xl:flex-row xl:items-center xl:justify-between">
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
                                            :value="task.progress || 1" max="100"></progress>
                                    </div>
                                </div>
                            </div>

                            <div class="grid gap-3 xl:min-w-[20rem] xl:grid-cols-[10rem_minmax(0,1fr)]">
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
                                        :value="task.progress || 1" max="100"></progress>
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

    <x-ui.card class="border border-base-300/50">
        <div x-show="selectedTask" x-cloak>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="badge badge-outline badge-sm" :class="statusClass(selectedTask?.status)"
                            x-text="selectedTask?.status_label"></span>
                        <span class="badge badge-sm" :class="priorityClass(selectedTask?.priority)"
                            x-text="priorityLabel(selectedTask?.priority)"></span>
                        <span x-show="selectedTask?.attendance_label" class="badge badge-sm badge-info" x-cloak
                            x-text="selectedTask?.attendance_label"></span>
                    </div>
                    <h3 class="mt-3 text-lg font-semibold text-base-content sm:text-xl" x-text="selectedTask?.title"></h3>
                    <p class="mt-2 text-xs leading-5 text-base-content/65 sm:text-sm sm:leading-6"
                        x-text="truncate(selectedTask?.description || 'Belum ada deskripsi untuk tugas ini.', 170)"></p>
                </div>

                <div class="hidden flex-wrap gap-2 sm:flex">
                    <x-ui.button type="ghost" size="sm" :isSubmit="false"
                        @click="showDialog('task-focus-modal')">
                        <x-heroicon-o-arrows-pointing-out class="h-4 w-4" />
                        Workspace
                    </x-ui.button>
                    <a class="btn btn-sm btn-ghost" :href="selectedTask?.show_url">
                        <x-heroicon-o-eye class="h-4 w-4" />
                        Detail
                    </a>
                    <a class="btn btn-sm btn-primary" :href="selectedTask?.edit_url">
                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                        Edit
                    </a>
                </div>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-md border border-base-300/70 bg-base-100 p-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">Deadline</div>
                    <div class="mt-2 font-semibold text-base-content" x-text="selectedTask?.deadline_label"></div>
                    <div class="mt-1 text-sm"
                        :class="selectedTask?.is_overdue ? 'text-error' : (selectedTask?.is_due_soon ? 'text-warning' : 'text-base-content/60')"
                        x-text="selectedTask?.deadline_relative"></div>
                </div>

                <div class="rounded-md border border-base-300/70 bg-base-100 p-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-xs uppercase tracking-[0.2em] text-base-content/45">Progress</span>
                        <span class="font-semibold text-primary" x-text="selectedTask?.progress + '%'"></span>
                    </div>
                    <progress class="progress progress-primary mt-3 h-2 w-full"
                        :value="selectedTask?.progress || 1" max="100"></progress>
                    <div class="mt-2 text-sm text-base-content/60"
                        x-text="selectedTask?.status_label"></div>
                </div>

                <div class="rounded-md border border-base-300/70 bg-base-100 p-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">Checklist</div>
                    <div class="mt-2 font-semibold text-base-content"
                        x-text="selectedTask?.todo_completed_count + '/' + selectedTask?.todo_count"></div>
                    <button type="button" class="btn btn-link btn-sm hidden px-0 sm:inline-flex"
                        @click="activateWorkspaceTab('action', 'todo')">
                        Kelola checklist
                    </button>
                </div>

                <div class="rounded-md border border-base-300/70 bg-base-100 p-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">Pertemuan</div>
                    <div class="mt-2 font-semibold text-base-content"
                        x-text="selectedTask?.attendance_label || 'Belum ditautkan'"></div>
                    <button type="button" class="btn btn-link btn-sm hidden px-0 sm:inline-flex"
                        x-show="selectedTask?.attendance_id" x-cloak
                        @click="focusAttendance(selectedTask.attendance_id)">
                        Buka absensi
                    </button>
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
