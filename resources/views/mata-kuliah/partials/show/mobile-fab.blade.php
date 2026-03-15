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
                    <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full border border-base-300 px-2 text-[11px] font-semibold text-base-content/70"
                        x-text="selectedTask ? selectedTask.title.charAt(0).toUpperCase() : '-'"></span>
                </div>

                <div class="mt-4 max-h-[58vh] space-y-3 overflow-y-auto pr-1">
                    <div class="space-y-2">
                        <button type="button"
                            class="flex w-full items-center justify-between rounded-md border px-3.5 py-3 text-left transition duration-200"
                            :class="mobileFabBranch === 'course'
                                ? 'border-primary/70 bg-primary/[0.05] shadow-lg shadow-primary/10'
                                : 'border-base-300/70 bg-base-100 hover:border-base-300 hover:bg-base-200/35'"
                            @click="toggleMobileFabBranch('course')">
                            <div class="flex items-center gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-md bg-primary/10 text-primary">
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
                            class="flex w-full items-center justify-between rounded-md border px-3.5 py-3 text-left transition duration-200"
                            :class="mobileFabBranch === 'task'
                                ? 'border-primary/70 bg-primary/[0.05] shadow-lg shadow-primary/10'
                                : 'border-base-300/70 bg-base-100 hover:border-base-300 hover:bg-base-200/35'"
                            @click="toggleMobileFabBranch('task')">
                            <div class="flex items-center gap-3">
                                <span class="flex h-11 w-11 items-center justify-center rounded-md bg-secondary/10 text-secondary">
                                    <x-heroicon-o-clipboard-document-list class="h-4 w-4" />
                                </span>
                                <div>
                                    <div class="text-sm font-semibold text-base-content">Fokus</div>
                                    <div class="text-[11px] leading-4 text-base-content/45">Tugas, checklist, dan catatan materi</div>
                                </div>
                            </div>
                            <x-heroicon-o-chevron-right class="h-4 w-4 transition"
                                x-bind:class="mobileFabBranch === 'task' ? 'rotate-90 text-primary' : 'text-base-content/25'" />
                        </button>

                        <div x-show="mobileFabBranch === 'task'" x-transition
                            class="ml-5 space-y-2 border-l border-base-300/60 pl-4">
                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition hover:bg-base-200"
                                @click="openQuickTaskCreate(); closeMobileFab()">
                                <x-heroicon-o-plus class="h-4 w-4 text-base-content/55" />
                                Buat tugas
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition"
                                :class="selectedTask ? 'hover:bg-base-200' : 'cursor-not-allowed opacity-45'"
                                :disabled="!selectedTask"
                                @click="if (!selectedTask) return; editSelectedTaskInWizard(); closeMobileFab()">
                                <x-heroicon-o-pencil-square class="h-4 w-4 text-base-content/55" />
                                Edit tugas terpilih
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition"
                                :class="selectedTask ? 'hover:bg-base-200' : 'cursor-not-allowed opacity-45'"
                                :disabled="!selectedTask"
                                @click="if (!selectedTask) return; openQuickTodoCreate(); closeMobileFab()">
                                <x-heroicon-o-list-bullet class="h-4 w-4 text-base-content/55" />
                                Tambah checklist
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition hover:bg-base-200"
                                @click="openAttendanceModal(); closeMobileFab()">
                                <x-heroicon-o-calendar-days class="h-4 w-4 text-base-content/55" />
                                Catat materi
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition hover:bg-base-200"
                                @click="scrollToSection('task-board'); closeMobileFab()">
                                <x-heroicon-o-rectangle-stack class="h-4 w-4 text-base-content/55" />
                                Buka papan tugas
                            </button>

                            <button type="button"
                                class="flex w-full items-center gap-3 rounded-xl bg-base-200/65 px-4 py-3 text-left text-[13px] font-medium text-base-content transition"
                                :class="selectedTask ? 'hover:bg-base-200' : 'cursor-not-allowed opacity-45'"
                                :disabled="!selectedTask"
                                @click="if (!selectedTask) { scrollToSection('task-board'); closeMobileFab(); return; } scrollToSection('task-detail-card'); closeMobileFab()">
                                <x-heroicon-o-document-text class="h-4 w-4 text-base-content/55" />
                                Info tugas terpilih
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
