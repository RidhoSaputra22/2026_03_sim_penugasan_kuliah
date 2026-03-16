@php
    use App\Enums\Status;
@endphp
<x-layouts.app title="Tugas">
    <x-slot:header>
        <x-layouts.page-header title="Manajemen Tugas" description="Kelola tugas dan deadline Anda">
            <x-slot:actions>
                <x-ui.button type="primary" size="sm" :href="route('tugas.create')">
                    <x-heroicon-o-plus class="h-4 w-4" />
                    Tambah Tugas
                </x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    {{-- Stats Summary --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <x-ui.stat title="Total" :value="$totalTugas">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Selesai" :value="$tugasSelesai">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-success" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Progress" :value="$tugasProgress">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-warning" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Belum" :value="$tugasBelum">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-error" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Terlambat" :value="$tugasTerlambat"
            description="{{ $tugasTerlambat > 0 ? 'Perlu perhatian!' : '' }}">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="h-6 w-6 {{ $tugasTerlambat > 0 ? 'text-error' : 'text-success' }}" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Avg Progress" :value="round($avgProgress) . '%'">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-info" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>
    </div>

    {{-- Priority & Deadline Summary --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        {{-- Prioritas breakdown --}}
        <x-ui.card>
            <h3 class="font-semibold text-sm mb-3">Distribusi Prioritas</h3>
            <div class="flex gap-4 items-center">
                @php
                    $priConfig = [
                        'tinggi' => ['label' => 'Tinggi', 'color' => 'error'],
                        'sedang' => ['label' => 'Sedang', 'color' => 'warning'],
                        'rendah' => ['label' => 'Rendah', 'color' => 'info'],
                    ];
                @endphp
                @foreach ($priConfig as $key => $cfg)
                    @php $cnt = $tugasPerPrioritas[$key] ?? 0; @endphp
                    <div class="flex-1 text-center">
                        <div class="radial-progress text-{{ $cfg['color'] }} text-sm font-bold"
                            style="--value:{{ $totalTugas > 0 ? round(($cnt / $totalTugas) * 100) : 0 }}; --size:3.5rem; --thickness:0.3rem;"
                            role="progressbar">
                            {{ $cnt }}
                        </div>
                        <div class="text-xs text-base-content/60 mt-1">{{ $cfg['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        {{-- Deadline info --}}
        <x-ui.card>
            <h3 class="font-semibold text-sm mb-3">Ringkasan Deadline</h3>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-base-200/50 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-warning">{{ $deadlineMingguIni }}</div>
                    <div class="text-xs text-base-content/60">Deadline Minggu Ini</div>
                </div>
                <div class="bg-base-200/50 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold {{ $tugasTerlambat > 0 ? 'text-error' : 'text-success' }}">
                        {{ $tugasTerlambat }}</div>
                    <div class="text-xs text-base-content/60">Sudah Terlambat</div>
                </div>
            </div>
            @if ($totalTugas > 0)
                <div class="mt-3">
                    <div class="flex justify-between text-xs text-base-content/60 mb-1">
                        <span>Completion Rate</span>
                        <span class="font-mono">{{ round(($tugasSelesai / $totalTugas) * 100) }}%</span>
                    </div>
                    <progress class="progress progress-success w-full" value="{{ $tugasSelesai }}"
                        max="{{ $totalTugas }}"></progress>
                </div>
            @endif
        </x-ui.card>
    </div>

    {{-- Deadline Calendar Widget --}}
    <div x-data="taskDeadlineCalendar(@js([
    'selectedDate' => $defaultTaskDate,
    'events' => $taskCalendarEvents,
]))" x-init="init()"
        x-on:task-calendar-date-select.window="selectDate($event.detail?.date || null)"
        x-on:task-calendar-event-select.window="selectDateFromEvent($event.detail?.event || null)"
        class="grid grid-cols-1 xl:grid-cols-[minmax(0,1.7fr)_minmax(20rem,1fr)] gap-4 mb-6">
        <div>
            <div x-effect="dispatchCalendarSelection(selectedDate, true)" class="hidden"></div>

            <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold">Kalender Deadline</h3>
                    <p class="text-xs text-base-content/60">Klik tanggal untuk melihat daftar tugas pada hari tersebut.
                    </p>
                </div>
                <x-ui.button type="ghost" size="sm" :href="route('kalender.index')" :isSubmit="false">
                    Buka Kalender
                </x-ui.button>
            </div>

            <x-ui.callendar :events="$taskCalendarEvents" mode="slim" :selectedDate="$defaultTaskDate" :showScheduleLegend="false" :showCustomLegend="false"
                :allowEventCrud="false" :interactive="false" eventClickName="task-calendar-event-select"
                dateClickName="task-calendar-date-select" selectionSyncEvent="task-calendar-selection-sync" />
        </div>

        <x-ui.card>
            <div class="flex items-start justify-between gap-3">
                <div>
                    <h3 class="font-semibold text-sm">Tugas Pada Tanggal</h3>
                    <p class="text-xs text-base-content/60" x-text="selectedDateLabel()"></p>
                </div>
                <span class="badge badge-ghost badge-sm" x-text="selectedTasks.length + ' tugas'"></span>
            </div>

            <div class="mt-4 space-y-3">
                <template x-if="selectedTasks.length === 0">
                    <div class="rounded-xl border border-dashed border-base-300 bg-base-200/30 px-4 py-6 text-center">
                        <div class="text-sm font-medium">Tidak ada tugas pada tanggal ini</div>
                        <p class="mt-1 text-xs text-base-content/60">Klik tanggal lain di kalender untuk melihat
                            deadline pada hari tersebut.</p>
                    </div>
                </template>

                <template x-for="task in selectedTasks" :key="task.id">
                    <a :href="task.detail_url"
                        class="block rounded-md border border-base-200 bg-base-100/80 p-3 transition hover:border-primary/30 hover:bg-primary/5">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="truncate font-medium" x-text="task.title"></div>
                                <div class="mt-1 truncate text-xs text-base-content/60" x-text="task.mata_kuliah">
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-1">
                                <span class="badge badge-xs badge-outline" :class="priorityBadgeClass(task.prioritas)"
                                    x-text="priorityLabel(task.prioritas)"></span>
                                <span class="badge badge-ghost badge-xs" x-text="task.progress + '%'"></span>
                            </div>
                        </div>

                        <div class="mt-3 flex items-center justify-between gap-3 text-xs">
                            <span class="text-base-content/60" x-text="task.deadline_label"></span>
                            <span class="font-medium" :class="deadlineTone(task.deadline)"
                                x-text="deadlineLabel(task.deadline)"></span>
                        </div>
                    </a>
                </template>
            </div>
        </x-ui.card>
    </div>


    <x-ui.card>
        <x-ui.data-table title="Daftar Tugas"
        :data="$tugas"
        model="\App\Models\Tugas"
        :exclude="[
            'user_id',
            'mata_kuliah_id',
            'absensi_id',
            'file'
        ]"
        :labels="[
            'judul' => 'Judul',
            'deskripsi' => 'Deskripsi',
            'status' => 'Status',
            'prioritas' => 'Prioritas',
            'deadline' => 'Deadline',
            'progress' => 'Progress',
        ]" :formats="[
            'status' => 'badge',
            'prioritas' => 'badge',
            'deadline' => 'date',
            'progress' => 'progress',
            ]" :sortable="['judul', 'deskripsi', 'status', 'prioritas', 'deadline', 'progress']" :bulkActionRoute="route('tugas.bulk-action')" :showRoute="fn($row) => route('tugas.show', $row->id)"
            :editRoute="fn($row) => route('tugas.edit', $row->id)" :deleteRoute="fn($row) => route('tugas.destroy', $row->id)">
            <x-slot:filters>
                  <x-ui.select name="status" :searchable="false" placeholder="Semua Status" :options="App\Enums\Status::options()" :value="request('status')" size="sm"  />

                <x-ui.select name="mata_kuliah_id" :searchable="true"
                    placeholder="Semua Mata Kuliah" :options="$mataKuliah->pluck('nama', 'id')->toArray()" :value="request('mata_kuliah_id')" size="sm" />
            </x-slot:filters>

            <x-slot:bulkActions>
                <option value="set_senin">Set Hari = Senin</option>
            </x-slot:bulkActions>
        </x-ui.data-table>
    </x-ui.card>

    @push('scripts')
        <script>
            function taskDeadlineCalendar(config = {}) {
                return {
                    selectedDate: config.selectedDate || null,
                    events: Array.isArray(config.events) ? config.events : [],
                    calendarSelectionEvent: 'task-calendar-selection-sync',

                    init() {
                        this.selectedDate = this.normalizeDate(this.selectedDate);

                        if (!this.selectedDate && this.events.length > 0) {
                            this.selectedDate = this.normalizeDate(this.events[0]?.start || null);
                        }
                    },

                    normalizeDate(date) {
                        if (!date) {
                            return null;
                        }

                        const normalized = String(date).trim().substring(0, 10);

                        if (/^\d{4}-\d{2}-\d{2}$/.test(normalized)) {
                            return normalized;
                        }

                        const parsed = new Date(date);

                        if (Number.isNaN(parsed.getTime())) {
                            return null;
                        }

                        return parsed.toISOString().slice(0, 10);
                    },

                    selectDate(date) {
                        const normalizedDate = this.normalizeDate(date);

                        if (normalizedDate) {
                            this.selectedDate = normalizedDate;
                        }
                    },

                    selectDateFromEvent(event) {
                        this.selectDate(event?.start || event?.date || null);
                    },

                    dispatchCalendarSelection(date, syncMonth = true) {
                        window.dispatchEvent(new CustomEvent(this.calendarSelectionEvent, {
                            detail: {
                                date: date || null,
                                syncMonth,
                            },
                        }));
                    },

                    priorityLabel(priority) {
                        if (!priority) {
                            return 'Umum';
                        }

                        return priority.charAt(0).toUpperCase() + priority.slice(1);
                    },

                    priorityBadgeClass(priority) {
                        return {
                            tinggi: 'badge-error',
                            sedang: 'badge-warning',
                            rendah: 'badge-info',
                        } [priority] || 'badge-ghost';
                    },

                    dateDiff(deadline) {
                        const normalizedDate = this.normalizeDate(deadline);

                        if (!normalizedDate) {
                            return null;
                        }

                        const today = new Date();
                        today.setHours(0, 0, 0, 0);

                        const target = new Date(`${normalizedDate}T00:00:00`);
                        return Math.round((target - today) / 86400000);
                    },

                    deadlineLabel(deadline) {
                        const diff = this.dateDiff(deadline);

                        if (diff === null) {
                            return '';
                        }

                        if (diff < 0) {
                            return `Terlambat ${Math.abs(diff)} hari`;
                        }

                        if (diff === 0) {
                            return 'Hari ini';
                        }

                        if (diff === 1) {
                            return 'Besok';
                        }

                        return `${diff} hari lagi`;
                    },

                    deadlineTone(deadline) {
                        const diff = this.dateDiff(deadline);

                        if (diff === null) {
                            return 'text-base-content/60';
                        }

                        if (diff < 0) {
                            return 'text-error';
                        }

                        if (diff <= 2) {
                            return 'text-warning';
                        }

                        return 'text-base-content/60';
                    },

                    selectedDateLabel() {
                        const normalizedDate = this.normalizeDate(this.selectedDate);

                        if (!normalizedDate) {
                            return 'Belum ada tanggal dipilih';
                        }

                        const date = new Date(`${normalizedDate}T00:00:00`);

                        return new Intl.DateTimeFormat('id-ID', {
                            day: 'numeric',
                            month: 'long',
                            year: 'numeric',
                        }).format(date);
                    },

                    get selectedTasks() {
                        const priorityOrder = {
                            tinggi: 0,
                            sedang: 1,
                            rendah: 2,
                        };

                        return this.events
                            .filter((event) => event?.extendedProps?.type === 'deadline')
                            .map((event) => ({
                                id: event?.extendedProps?.task_id ?? event?.id,
                                title: event?.title ?? '',
                                deadline: this.normalizeDate(event?.start || null),
                                deadline_label: event?.extendedProps?.deadline_label || '',
                                mata_kuliah: event?.extendedProps?.mata_kuliah || '-',
                                progress: Number(event?.extendedProps?.progress ?? 0),
                                prioritas: event?.extendedProps?.prioritas || 'rendah',
                                detail_url: event?.extendedProps?.detail_url || '#',
                                status: event?.extendedProps?.status || null,
                                status_label: event?.extendedProps?.status_label || null,
                            }))
                            .filter((task) => task.deadline === this.selectedDate)
                            .sort((left, right) => {
                                const priorityDelta = (priorityOrder[left.prioritas] ?? 9) - (priorityOrder[right
                                    .prioritas] ?? 9);

                                if (priorityDelta !== 0) {
                                    return priorityDelta;
                                }

                                return left.title.localeCompare(right.title);
                            });
                    },
                };
            }
        </script>
    @endpush
</x-layouts.app>
