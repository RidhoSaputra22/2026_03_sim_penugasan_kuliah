<div class="sm:hidden" x-cloak>
    <div x-show="mobileFabOpen" x-transition.opacity
        class="fixed inset-0 z-30 bg-base-content/12 backdrop-blur-[3px]" @click="closeMobileFab()"></div>

    <div class="fixed bottom-4 right-4 z-40">
        <div class="flex flex-col items-end">
            <div x-show="mobileFabOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-y-4 scale-95 opacity-0"
                x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                x-transition:leave-end="translate-y-4 scale-95 opacity-0"
                class="mb-4 w-[min(21rem,calc(100vw-2rem))] rounded-[1.8rem] border border-base-300/70 bg-base-100/97 p-4 shadow-[0_24px_64px_-28px_rgba(15,23,42,0.38)] backdrop-blur">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="text-[11px] font-semibold uppercase tracking-[0.28em] text-base-content/45">
                            Aksi Mode Fokus
                        </div>
                        <div class="mt-1 text-sm font-semibold text-base-content">Semua aksi mobile diringkas ke FAB</div>
                    </div>

                    <button type="button"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-base-content/55 transition hover:bg-base-200 hover:text-base-content"
                        @click="closeMobileFab()" aria-label="Tutup aksi mobile">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-3 flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-base-200 px-3 py-1 text-[11px] font-medium text-base-content/75"
                        x-text="selectedAttendance ? attendanceLabel(selectedAttendance) : 'Belum pilih absensi'"></span>
                    <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full border border-base-300 px-2 text-[11px] font-semibold text-base-content/70"
                        x-text="selectedTask ? selectedTask.title.charAt(0).toUpperCase() : '-'"></span>
                </div>

                <div class="mt-4 max-h-[58vh] space-y-3 overflow-y-auto pr-1">
                    <div class="space-y-2">
                        <button type="button"
                            class="flex w-full items-center justify-between rounded-2xl border px-3.5 py-3 text-left transition duration-200"
                            :class="mobileFabBranch === 'course'
                                ? 'border-primary/70 bg-primary/[0.05] shadow-lg shadow-primary/10'
                                : 'border-base-300/70 bg-base-100 hover:border-base-300 hover:bg-base-200/35'"
                            @click="toggleMobileFabBranch('course')">
                            <div class="flex items-center gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                                    <x-heroicon-o-book-open class="h-4 w-4" />
                                </span>
                                <div>
                                    <div class="text-sm font-semibold text-base-content">Mata Kuliah</div>
                                    <div class="text-[11px] leading-4 text-base-content/45">Kembali, edit, dan LMS</div>
                                </div>
                            </div>
                            <x-heroicon-o-chevron-right class="h-4 w-4 transition"
                                x-bind:class="mobileFabBranch === 'course' ? 'rotate-90 text-primary' : 'text-base-content/25'" />
                        </button>

                        <div x-show="mobileFabBranch === 'course'" x-transition
                            class="ml-5 space-y-2 border-l border-base-300/60 pl-4">
                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition hover:bg-base-200"
                                @click='goTo(@js(route("mata-kuliah.index")))'>
                                <x-heroicon-o-arrow-left class="h-4 w-4 text-base-content/55" />
                                Kembali ke daftar mata kuliah
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition hover:bg-base-200"
                                @click='goTo(@js(route("mata-kuliah.edit", $mataKuliah)))'>
                                <x-heroicon-o-pencil-square class="h-4 w-4 text-base-content/55" />
                                Edit data mata kuliah
                            </button>

                            @if ($mataKuliah->lms_link)
                                <button type="button"
                                    class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition hover:bg-base-200"
                                    @click='goTo(@js($mataKuliah->lms_link), true)'>
                                    <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4 text-base-content/55" />
                                    Buka LMS
                                </button>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-2">
                        <button type="button"
                            class="flex w-full items-center justify-between rounded-2xl border px-3.5 py-3 text-left transition duration-200"
                            :class="mobileFabBranch === 'panel'
                                ? 'border-primary/70 bg-primary/[0.05] shadow-lg shadow-primary/10'
                                : 'border-base-300/70 bg-base-100 hover:border-base-300 hover:bg-base-200/35'"
                            @click="toggleMobileFabBranch('panel')">
                            <div class="flex items-center gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-info/10 text-info">
                                    <x-heroicon-o-squares-2x2 class="h-4 w-4" />
                                </span>
                                <div>
                                    <div class="text-sm font-semibold text-base-content">Panel Fokus</div>
                                    <div class="text-[11px] leading-4 text-base-content/45">Papan, tab workspace, dan parkir</div>
                                </div>
                            </div>
                            <x-heroicon-o-chevron-right class="h-4 w-4 transition"
                                x-bind:class="mobileFabBranch === 'panel' ? 'rotate-90 text-primary' : 'text-base-content/25'" />
                        </button>

                        <div x-show="mobileFabBranch === 'panel'" x-transition
                            class="ml-5 space-y-2 border-l border-base-300/60 pl-4">
                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition hover:bg-base-200"
                                @click="scrollToSection('task-board'); closeMobileFab()">
                                <x-heroicon-o-rectangle-stack class="h-4 w-4 text-base-content/55" />
                                Buka papan fokus tugas
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition hover:bg-base-200"
                                @click="activateWorkspaceTab('attendance', 'attendance'); closeMobileFab()">
                                <x-heroicon-o-academic-cap class="h-4 w-4 text-base-content/55" />
                                Tab absensi
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition hover:bg-base-200"
                                @click="activateWorkspaceTab('action'); closeMobileFab()">
                                <x-heroicon-o-bolt class="h-4 w-4 text-base-content/55" />
                                Tab aksi
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition hover:bg-base-200"
                                @click="activateWorkspaceTab('parking'); closeMobileFab(); $nextTick(() => $refs.quickItemInput?.focus())">
                                <x-heroicon-o-bookmark-square class="h-4 w-4 text-base-content/55" />
                                Tab parkir fokus
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <button type="button"
                            class="flex w-full items-center justify-between rounded-2xl border px-3.5 py-3 text-left transition duration-200"
                            :class="mobileFabBranch === 'attendance'
                                ? 'border-primary/70 bg-primary/[0.05] shadow-lg shadow-primary/10'
                                : 'border-base-300/70 bg-base-100 hover:border-base-300 hover:bg-base-200/35'"
                            @click="toggleMobileFabBranch('attendance')">
                            <div class="flex items-center gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-success/10 text-success">
                                    <x-heroicon-o-user-plus class="h-4 w-4" />
                                </span>
                                <div>
                                    <div class="text-sm font-semibold text-base-content">Absensi</div>
                                    <div class="text-[11px] leading-4 text-base-content/45">Pertemuan, form, dan catatan</div>
                                </div>
                            </div>
                            <x-heroicon-o-chevron-right class="h-4 w-4 transition"
                                x-bind:class="mobileFabBranch === 'attendance' ? 'rotate-90 text-primary' : 'text-base-content/25'" />
                        </button>

                        <div x-show="mobileFabBranch === 'attendance'" x-transition
                            class="ml-5 space-y-2 border-l border-base-300/60 pl-4">
                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition hover:bg-base-200"
                                @click="activateWorkspaceTab('attendance', 'attendance'); closeMobileFab()">
                                <x-heroicon-o-academic-cap class="h-4 w-4 text-base-content/55" />
                                Kelola absensi
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition hover:bg-base-200"
                                @click="prepareNewAttendance(); activateWorkspaceTab('attendance', 'attendance'); closeMobileFab()">
                                <x-heroicon-o-plus class="h-4 w-4 text-base-content/55" />
                                Pertemuan baru
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition hover:bg-base-200"
                                @click="activateWorkspaceTab('attendance'); togglePanel('attendance'); closeMobileFab()">
                                <x-heroicon-o-pencil-square class="h-4 w-4 text-base-content/55" />
                                <span x-text="panels.attendance ? 'Tutup form absensi' : 'Buka form absensi'"></span>
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition hover:bg-base-200"
                                @click="activateWorkspaceTab('attendance'); notesMode = 'edit'; closeMobileFab()">
                                <x-heroicon-o-document-text class="h-4 w-4 text-base-content/55" />
                                Edit catatan pertemuan
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition hover:bg-base-200"
                                @click="activateWorkspaceTab('attendance'); notesMode = 'preview'; closeMobileFab()">
                                <x-heroicon-o-eye class="h-4 w-4 text-base-content/55" />
                                Preview catatan pertemuan
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <button type="button"
                            class="flex w-full items-center justify-between rounded-2xl border px-3.5 py-3 text-left transition duration-200"
                            :class="mobileFabBranch === 'task'
                                ? 'border-primary/70 bg-primary/[0.05] shadow-lg shadow-primary/10'
                                : 'border-base-300/70 bg-base-100 hover:border-base-300 hover:bg-base-200/35'"
                            @click="toggleMobileFabBranch('task')">
                            <div class="flex items-center gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-secondary/10 text-secondary">
                                    <x-heroicon-o-clipboard-document-list class="h-4 w-4" />
                                </span>
                                <div>
                                    <div class="text-sm font-semibold text-base-content">Tugas</div>
                                    <div class="text-[11px] leading-4 text-base-content/45">Form, workspace, dan tugas terpilih</div>
                                </div>
                            </div>
                            <x-heroicon-o-chevron-right class="h-4 w-4 transition"
                                x-bind:class="mobileFabBranch === 'task' ? 'rotate-90 text-primary' : 'text-base-content/25'" />
                        </button>

                        <div x-show="mobileFabBranch === 'task'" x-transition
                            class="ml-5 space-y-2 border-l border-base-300/60 pl-4">
                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition hover:bg-base-200"
                                @click="activateWorkspaceTab('action', 'task'); closeMobileFab()">
                                <x-heroicon-o-plus class="h-4 w-4 text-base-content/55" />
                                Tambah tugas
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition hover:bg-base-200"
                                @click="activateWorkspaceTab('action'); togglePanel('task'); closeMobileFab()">
                                <x-heroicon-o-pencil-square class="h-4 w-4 text-base-content/55" />
                                <span x-text="panels.task ? 'Tutup form tugas' : 'Buka form tugas'"></span>
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition"
                                :class="selectedTask ? 'hover:bg-base-200' : 'cursor-not-allowed opacity-45'"
                                :disabled="!selectedTask"
                                @click="if (!selectedTask) return; activateWorkspaceTab('action', 'todo'); closeMobileFab()">
                                <x-heroicon-o-list-bullet class="h-4 w-4 text-base-content/55" />
                                Tambah checklist
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition"
                                :class="selectedTask ? 'hover:bg-base-200' : 'cursor-not-allowed opacity-45'"
                                :disabled="!selectedTask"
                                @click="if (!selectedTask) { scrollToSection('task-board'); closeMobileFab(); return; } showDialog('task-focus-modal'); closeMobileFab()">
                                <x-heroicon-o-arrows-pointing-out class="h-4 w-4 text-base-content/55" />
                                Workspace tugas
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition"
                                :class="selectedTask?.show_url ? 'hover:bg-base-200' : 'cursor-not-allowed opacity-45'"
                                :disabled="!selectedTask?.show_url"
                                @click="if (!selectedTask?.show_url) return; goTo(selectedTask.show_url)">
                                <x-heroicon-o-eye class="h-4 w-4 text-base-content/55" />
                                Detail tugas terpilih
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition"
                                :class="selectedTask?.edit_url ? 'hover:bg-base-200' : 'cursor-not-allowed opacity-45'"
                                :disabled="!selectedTask?.edit_url"
                                @click="if (!selectedTask?.edit_url) return; goTo(selectedTask.edit_url)">
                                <x-heroicon-o-pencil-square class="h-4 w-4 text-base-content/55" />
                                Edit tugas terpilih
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition"
                                :class="selectedTask?.attendance_id ? 'hover:bg-base-200' : 'cursor-not-allowed opacity-45'"
                                :disabled="!selectedTask?.attendance_id"
                                @click="if (!selectedTask?.attendance_id) return; focusAttendance(selectedTask.attendance_id); closeMobileFab()">
                                <x-heroicon-o-link class="h-4 w-4 text-base-content/55" />
                                Buka absensi terkait
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button"
                class="flex h-14 w-14 items-center justify-center rounded-full bg-primary text-primary-content shadow-[0_18px_36px_-16px_rgba(79,70,229,0.75)] transition duration-200 hover:scale-[1.03]"
                @click="toggleMobileFab()" aria-label="Buka aksi mode fokus">
                <x-heroicon-o-plus class="h-6 w-6 transition-transform duration-200"
                    x-bind:class="mobileFabOpen ? 'rotate-45' : ''" />
            </button>
        </div>
    </div>
</div>
