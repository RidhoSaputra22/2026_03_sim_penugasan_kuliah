@props(['events' => []])
<div x-data="kalenderApp()" x-init="init()" {{ $attributes->merge(['class' => '']) }}>
    {{-- Navigation --}}
    <x-ui.card class="mb-6">
        <div class="flex items-center justify-between">
            <button class="btn btn-ghost btn-sm" @click="prevMonth()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <h3 class="text-lg font-bold" x-text="monthYear"></h3>
            <button class="btn btn-ghost btn-sm" @click="nextMonth()">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        {{-- Legend --}}
        <div class="flex items-center justify-center flex-wrap gap-8 mt-3 text-xs md:text-sm">
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-primary"></span>
                <span>Jadwal Kuliah</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-error"></span>
                <span>Deadline Tugas</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-success"></span>
                <span>Event Custom</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-base-300"></span>
                <span>Hari Ini</span>
            </div>
        </div>
    </x-ui.card>

    {{-- Calendar Grid --}}
    <x-ui.card>
        {{-- Day Headers --}}
        <div class="grid grid-cols-7 gap-px mb-1 sm:mb-2">
            <template x-for="day in ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']" :key="day">
                <div class="text-center text-xs sm:text-sm font-semibold text-base-content/60 py-1 sm:py-2"
                    x-text="day"></div>
            </template>
        </div>

        {{-- Date Grid --}}
        <div
            class="grid grid-cols-7 gap-px bg-base-200 border border-base-200 rounded-lg overflow-hidden text-[11px] sm:text-xs">
            <template x-for="(cell, index) in calendarCells" :key="index">
                <div class="bg-base-100 min-h-[60px] sm:min-h-[100px] p-1 sm:p-1.5 relative group focus-within:z-30"
                    :class="{
                        'bg-primary/5': cell.isToday && !isInDragSelection(index),
                        'opacity-40': !cell.currentMonth,
                        'cursor-pointer': cell.currentMonth,
                        'cursor-default': !cell.currentMonth,
                        'bg-success/10 ring-1 ring-inset ring-success/40': isInDragSelection(index) && isDragging && cell.currentMonth,
                    }"
                    @mousedown.prevent="onCellMouseDown(index, cell)"
                    @mouseenter="onCellMouseEnter(index, cell)">
                    <div class="font-medium mb-1 flex items-center gap-1"
                        :class="cell.isToday ? 'text-primary font-bold' : 'text-base-content/70'">
                        <div :class="cell.isToday ? 'badge badge-primary badge-xs' : ''">
                            <span x-text="cell.day"></span>
                            <span x-show="cell.isToday">Hari Ini</span>
                        </div>
                    </div>

                    {{-- Multi-day span bars --}}
                    <template x-for="(bar, bi) in (cell.spanBars || [])" :key="bi">
                        <div class="h-4 flex items-center text-[9px] font-medium bg-success/25 text-success -mx-1 sm:-mx-1.5 px-1 mb-0.5 overflow-hidden"
                            :class="{
                                'rounded-l-full': bar.isStart,
                                'rounded-r-full': bar.isEnd,
                            }"
                            :title="bar.title">
                            <span x-show="bar.showLabel" class="truncate block w-full px-0.5" x-text="bar.title"></span>
                        </div>
                    </template>

                    {{-- Events --}}
                    <div class="space-y-0.5">
                        <template x-for="(event, ei) in cell.events" :key="ei">
                            <div class="text-[9px] sm:text-[10px] leading-tight px-1 py-0.5 rounded truncate cursor-default"
                                :class="event.type === 'jadwal' ?
                                    'bg-primary/15 text-primary' :
                                    event.type === 'custom' ?
                                    'bg-success/15 text-success' :
                                    'bg-error/15 text-error'"
                                :title="event.title" x-text="event.title">
                            </div>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        {{-- Modal Event --}}
        <x-ui.modal id="modal-event" size="xl" :closeButton="true">
            <x-slot:title>
                <div class="flex justify-center items-center gap-2">
                    <x-heroicon-o-calendar-days class="h-8 w-8 text-primary/80" />
                    <h3 class="font-bold text-xl">Event <span x-text="modalDate"></span></h3>

                </div>
            </x-slot:title>



            {{-- Header: tambah event + info range saat membuat dari drag --}}
            <div class="flex items-center justify-between mb-3">
                <p x-show="dragRangeLabel" class="text-xs text-base-content/50" x-text="dragRangeLabel"></p>
                <span x-show="!dragRangeLabel"></span>
                <button class="btn btn-success btn-sm gap-1" @click="openCreateEvent()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Event
                </button>
            </div>

            <div x-show="modalEvents.length === 0" class="text-center text-base-content/50 text-xs py-4">Tidak ada event
            </div>
            <div class="space-y-3 max-h-[28rem] overflow-y-auto pr-1">
                <template x-for="(event, idx) in modalEvents" :key="idx">
                    <div class="mb-3 card border p-3"
                        x-bind:class="event.type === 'deadline' ?
                            'border-error/30 bg-error/5' :
                            event.type === 'custom' ?
                            'border-success/30 bg-success/5' :
                            'border-primary/30 bg-primary/5'">

                        {{-- Event header --}}
                        <div class="flex items-start gap-2 mb-2">
                            <span class="mt-1 inline-block w-2 h-2 rounded-full flex-shrink-0"
                                :class="event.type === 'deadline' ? 'bg-error' : event.type === 'custom' ? 'bg-success' :
                                    'bg-primary'"></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-semibold leading-snug"
                                    :class="event.type === 'deadline' ? 'text-error' : event.type === 'custom' ?
                                        'text-success' :
                                        'text-primary'"
                                    x-text="event.title"></p>
                                <span class="text-[10px] font-medium uppercase tracking-wide"
                                    :class="event.type === 'deadline' ? 'text-error/60' : event.type === 'custom' ?
                                        'text-success/60' : 'text-primary/60'"
                                    x-text="event.type === 'deadline' ? 'Deadline Tugas' : event.type === 'custom' ? 'Event Custom' : 'Jadwal Kuliah'"></span>
                            </div>
                            {{-- Edit / Delete buttons for custom events --}}
                            <template x-if="event.type === 'custom'">
                                <div class="flex items-center gap-1 ml-auto flex-shrink-0">
                                    <button class="btn btn-xs btn-ghost text-success" @click.stop="openEditEvent(event)"
                                        title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button class="btn btn-xs btn-ghost text-error"
                                        @click.stop="deleteEvent(event, idx)" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </template>
                        </div>

                        {{-- Jadwal details --}}
                        <template x-if="event.type === 'jadwal'">
                            <div class="ml-4 space-y-1 text-xs text-base-content/70">
                                <div x-show="event.jam_mulai" class="flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 flex-shrink-0 text-primary/60" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span
                                        x-text="event.jam_mulai + (event.jam_selesai ? ' – ' + event.jam_selesai : '')"></span>
                                </div>
                                <div x-show="event.ruangan" class="flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 flex-shrink-0 text-primary/60" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span x-text="event.ruangan"></span>
                                </div>
                            </div>
                        </template>

                        {{-- Deadline details --}}
                        <template x-if="event.type === 'deadline'">
                            <div class="ml-4 space-y-1 text-xs text-base-content/70">
                                <div x-show="event.mata_kuliah" class="flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 flex-shrink-0 text-error/60" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                    <span x-text="event.mata_kuliah"></span>
                                </div>
                                <div x-show="event.status" class="flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 flex-shrink-0 text-error/60" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span x-text="event.status"></span>
                                </div>
                                <div x-show="event.progress !== null && event.progress !== undefined"
                                    class="space-y-1">
                                    <div class="flex items-center justify-between">
                                        <span class="flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="w-3.5 h-3.5 flex-shrink-0 text-error/60" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                            Progress
                                        </span>
                                        <span class="font-semibold" x-text="event.progress + '%'"></span>
                                    </div>
                                    <div class="w-full bg-base-300 rounded-full h-1.5">
                                        <div class="bg-error h-1.5 rounded-full transition-all"
                                            :style="'width: ' + event.progress + '%'"></div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Custom event details --}}
                        <template x-if="event.type === 'custom'">
                            <div class="ml-4 space-y-1 text-xs text-base-content/70">
                                <div x-show="event.start" class="flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 flex-shrink-0 text-success/60" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span x-text="event.start + (event.end ? ' – ' + event.end : '')"></span>
                                </div>
                                <div x-show="event.location" class="flex items-center gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 flex-shrink-0 text-success/60" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span x-text="event.location"></span>
                                </div>
                                <div x-show="event.description" class="flex items-start gap-1.5">
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        class="w-3.5 h-3.5 flex-shrink-0 mt-0.5 text-success/60" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h7" />
                                    </svg>
                                    <span x-text="event.description"></span>
                                </div>
                            </div>
                        </template>

                    </div>
                </template>
            </div>
        </x-ui.modal>

        {{-- Modal Form Custom Event (Create / Edit) --}}
        <dialog id="modal-event-form" class="modal modal-middle">
            <div class="modal-box max-w-lg">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-lg text-success" x-text="eventForm.id ? 'Edit Event' : 'Tambah Event'"></h3>
                    <button class="btn btn-sm btn-circle btn-ghost" @click="closeEventForm()">✕</button>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="label label-text text-xs font-medium">Judul <span
                                class="text-error">*</span></label>
                        <input type="text" class="input input-bordered w-full input-sm" x-model="eventForm.title"
                            placeholder="Nama event">
                        <p x-show="eventFormErrors.title" class="text-error text-xs mt-1"
                            x-text="eventFormErrors.title"></p>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label label-text text-xs font-medium">Mulai <span
                                    class="text-error">*</span></label>
                            <input type="datetime-local" class="input input-bordered w-full input-sm"
                                x-model="eventForm.start">
                            <p x-show="eventFormErrors.start" class="text-error text-xs mt-1"
                                x-text="eventFormErrors.start"></p>
                        </div>
                        <div>
                            <label class="label label-text text-xs font-medium">Selesai</label>
                            <input type="datetime-local" class="input input-bordered w-full input-sm"
                                x-model="eventForm.end">
                        </div>
                    </div>
                    <div>
                        <label class="label label-text text-xs font-medium">Lokasi</label>
                        <input type="text" class="input input-bordered w-full input-sm"
                            x-model="eventForm.location" placeholder="Lokasi (opsional)">
                    </div>
                    <div>
                        <label class="label label-text text-xs font-medium">Deskripsi</label>
                        <textarea class="textarea textarea-bordered w-full textarea-sm" x-model="eventForm.description" rows="2"
                            placeholder="Deskripsi (opsional)"></textarea>
                    </div>
                </div>

                <div class="modal-action mt-4">
                    <button class="btn btn-ghost btn-sm" @click="closeEventForm()">Batal</button>
                    <button class="btn btn-success btn-sm" @click="submitEventForm()" :disabled="eventFormLoading">
                        <span x-show="eventFormLoading" class="loading loading-spinner loading-xs"></span>
                        <span x-text="eventForm.id ? 'Update' : 'Simpan'"></span>
                    </button>
                </div>
            </div>
            <div class="modal-backdrop" @click="closeEventForm()"></div>
        </dialog>
    </x-ui.card>


</div>

@push('scripts')
    <script>
        function kalenderApp() {
            return {
                currentDate: new Date(),
                events: @json($events),
                calendarCells: [],
                monthYear: '',
                modalEvents: [],
                modalDate: '',
                modalDateObj: null,

                // Custom event form state
                eventForm: { id: null, title: '', start: '', end: '', location: '', description: '' },
                eventFormErrors: {},
                eventFormLoading: false,
                dragRangeLabel: '',

                // Drag-to-select state
                isDragging: false,
                dragStartIndex: null,
                dragEndIndex: null,

                init() {
                    this.render();
                    document.addEventListener('mouseup', () => {
                        if (this.isDragging) this.onDragEnd();
                    });
                },

                // Recurring jadwal events: have daysOfWeek
                get jadwalEvents() {
                    return this.events.filter(e => Array.isArray(e.daysOfWeek));
                },

                // Date-specific deadline events: have start, no daysOfWeek, type deadline
                get deadlineEvents() {
                    return this.events.filter(e => e.start && !e.daysOfWeek && e.extendedProps?.type === 'deadline');
                },

                // Custom events: have start, no daysOfWeek, type custom
                get customEvents() {
                    return this.events.filter(e => e.start && !e.daysOfWeek && e.extendedProps?.type === 'custom');
                },

                get weekSchedule() {
                    const dayOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                    return this.jadwalEvents
                        .map(e => ({
                            title: e.title,
                            hari: e.extendedProps?.hari,
                            jam_mulai: e.startTime,
                            jam_selesai: e.endTime,
                            ruangan: e.extendedProps?.ruangan,
                        }))
                        .sort((a, b) => dayOrder.indexOf(a.hari) - dayOrder.indexOf(b.hari));
                },

                get upcomingDeadlines() {
                    const now = new Date();
                    now.setHours(0, 0, 0, 0);
                    return this.deadlineEvents
                        .filter(e => e.start && new Date(e.start) >= now)
                        .sort((a, b) => new Date(a.start) - new Date(b.start))
                        .slice(0, 5)
                        .map(e => ({
                            title: e.title,
                            date: e.start,
                            mata_kuliah: e.extendedProps?.mata_kuliah,
                            status: e.extendedProps?.status,
                            progress: e.extendedProps?.progress,
                        }));
                },

                render() {
                    const year = this.currentDate.getFullYear();
                    const month = this.currentDate.getMonth();
                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ];
                    this.monthYear = months[month] + ' ' + year;

                    const firstDay = new Date(year, month, 1);
                    let startDay = firstDay.getDay() - 1;
                    if (startDay < 0) startDay = 6;

                    const daysInMonth = new Date(year, month + 1, 0).getDate();
                    const daysInPrevMonth = new Date(year, month, 0).getDate();
                    const today = new Date();

                    const cells = [];

                    // Previous month filler days
                    for (let i = startDay - 1; i >= 0; i--) {
                        cells.push({
                            day: daysInPrevMonth - i,
                            currentMonth: false,
                            isToday: false,
                            events: []
                        });
                    }

                    // Current month days
                    for (let d = 1; d <= daysInMonth; d++) {
                        const date = new Date(year, month, d);
                        const dayOfWeek = date.getDay(); // 0=Sun, 1=Mon … 6=Sat (matches FullCalendar daysOfWeek)
                        const isToday = date.toDateString() === today.toDateString();
                        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;

                        const events = [];

                        // Recurring jadwal: match by daysOfWeek
                        this.jadwalEvents.forEach(e => {
                            if (e.daysOfWeek.includes(dayOfWeek)) {
                                events.push({
                                    title: e.title,
                                    type: 'jadwal',
                                    jam_mulai: e.startTime || null,
                                    jam_selesai: e.endTime || null,
                                    ruangan: e.extendedProps?.ruangan || null,
                                    hari: e.extendedProps?.hari || null,
                                });
                            }
                        });

                        // Deadline: match by start date
                        this.deadlineEvents.forEach(e => {
                            if (e.start && String(e.start).substring(0, 10) === dateStr) {
                                events.push({
                                    title: e.title,
                                    type: 'deadline',
                                    mata_kuliah: e.extendedProps?.mata_kuliah || null,
                                    status: e.extendedProps?.status || null,
                                    progress: e.extendedProps?.progress ?? null,
                                    date: e.start || null,
                                });
                            }
                        });

                        // Custom events: match by start date
                        this.customEvents.forEach(e => {
                            if (e.start && String(e.start).substring(0, 10) === dateStr) {
                                events.push({
                                    title: e.title,
                                    type: 'custom',
                                    eventId: e.extendedProps?.eventId || null,
                                    start: e.start || null,
                                    end: e.end || null,
                                    location: e.extendedProps?.location || null,
                                    description: e.extendedProps?.description || null,
                                });
                            }
                        });

                        cells.push({
                            day: d,
                            currentMonth: true,
                            isToday,
                            events
                        });
                    }

                    // Next month filler days
                    const remaining = 42 - cells.length;
                    for (let i = 1; i <= remaining; i++) {
                        cells.push({ day: i, currentMonth: false, isToday: false, events: [] });
                    }

                    // ── Multi-day span bars ───────────────────────────────────────────
                    // For custom events spanning multiple days, render as a colored bar
                    // across the cells they cover, removing the regular pill to avoid duplication.
                    this.customEvents.forEach(e => {
                        if (!e.start || !e.end) return;
                        const sDay = new Date(new Date(e.start).toDateString());
                        const enDay = new Date(new Date(e.end).toDateString());
                        if (sDay >= enDay) return; // single-day, skip

                        const monthStartDate = new Date(year, month, 1);
                        const monthEndDate = new Date(year, month, daysInMonth);
                        if (enDay < monthStartDate || sDay > monthEndDate) return;

                        const clampedStart = sDay < monthStartDate ? monthStartDate : sDay;
                        const clampedEnd = enDay > monthEndDate ? monthEndDate : enDay;
                        const sd = clampedStart.getDate();
                        const ed = clampedEnd.getDate();
                        const isLeftClamped = sDay < monthStartDate;
                        const isRightClamped = enDay > monthEndDate;

                        for (let d = sd; d <= ed; d++) {
                            const ci = startDay + d - 1;
                            if (!cells[ci] || !cells[ci].currentMonth) continue;
                            if (!cells[ci].spanBars) cells[ci].spanBars = [];
                            const dow = new Date(year, month, d).getDay();
                            cells[ci].spanBars.push({
                                title: e.title,
                                eventId: e.extendedProps?.eventId,
                                isStart: d === sd && !isLeftClamped,
                                isEnd: d === ed && !isRightClamped,
                                // Show label at start of event or start of each week row
                                showLabel: (d === sd && !isLeftClamped) || dow === 1 || isLeftClamped,
                            });
                            // Remove the regular pill so it doesn't appear twice
                            cells[ci].events = cells[ci].events.filter(
                                ev => !(ev.type === 'custom' && ev.eventId === e.extendedProps?.eventId)
                            );
                        }
                    });

                    this.calendarCells = cells;
                },

                isInDragSelection(index) {
                    if (!this.isDragging || this.dragStartIndex === null || this.dragEndIndex === null) return false;
                    const min = Math.min(this.dragStartIndex, this.dragEndIndex);
                    const max = Math.max(this.dragStartIndex, this.dragEndIndex);
                    return index >= min && index <= max;
                },

                onCellMouseDown(index, cell) {
                    if (!cell.currentMonth) return;
                    this.isDragging = true;
                    this.dragStartIndex = index;
                    this.dragEndIndex = index;
                },

                onCellMouseEnter(index, cell) {
                    if (!this.isDragging) return;
                    if (cell.currentMonth) this.dragEndIndex = index;
                },

                onDragEnd() {
                    if (!this.isDragging) return;
                    this.isDragging = false;

                    const startIdx = Math.min(this.dragStartIndex, this.dragEndIndex);
                    const endIdx = Math.max(this.dragStartIndex, this.dragEndIndex);
                    const startCell = this.calendarCells[startIdx];
                    const endCell = this.calendarCells[endIdx];

                    this.dragStartIndex = null;
                    this.dragEndIndex = null;

                    if (!startCell || !startCell.currentMonth) return;

                    if (startIdx === endIdx) {
                        // Simple click → show event modal
                        this.showEventModal(startCell);
                        return;
                    }

                    // Multi-cell drag → open create form with the selected range
                    if (endCell?.currentMonth) {
                        const y = this.currentDate.getFullYear();
                        const m = String(this.currentDate.getMonth() + 1).padStart(2, '0');
                        const sd = String(startCell.day).padStart(2, '0');
                        const ed = String(endCell.day).padStart(2, '0');
                        this.modalDateObj = startCell;

                        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                        const mName = months[this.currentDate.getMonth()];
                        this.dragRangeLabel = `${startCell.day}–${endCell.day} ${mName} ${y}`;

                        this.openCreateEvent(`${y}-${m}-${sd}T08:00`, `${y}-${m}-${ed}T17:00`);
                    }
                },

                showEventModal(cell) {
                    // Resolve full data for span bar events from the global customEvents list
                    const spanEvents = (cell.spanBars || []).map(b => {
                        const full = this.customEvents.find(e => e.extendedProps?.eventId === b.eventId);
                        return {
                            type: 'custom',
                            title: b.title,
                            eventId: b.eventId,
                            start: full?.start || null,
                            end: full?.end || null,
                            location: full?.extendedProps?.location || null,
                            description: full?.extendedProps?.description || null,
                        };
                    });
                    this.modalEvents = [...cell.events, ...spanEvents];
                    this.modalDateObj = cell;
                    this.dragRangeLabel = '';
                    const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
                    ];
                    this.modalDate = cell.day + ' ' + months[this.currentDate.getMonth()] + ' ' + this.currentDate
                        .getFullYear();
                    setTimeout(() => {
                        document.getElementById('modal-event').showModal();
                    }, 10);
                },

                // Build a datetime-local string for the selected date at a given HH:MM
                _dateTimeForModal(hhmm = '08:00') {
                    const y = this.currentDate.getFullYear();
                    const m = String(this.currentDate.getMonth() + 1).padStart(2, '0');
                    const d = this.modalDateObj ? String(this.modalDateObj.day).padStart(2, '0') : '01';
                    return `${y}-${m}-${d}T${hhmm}`;
                },

                openCreateEvent(startStr = null, endStr = null) {
                    this.eventForm = {
                        id: null,
                        title: '',
                        start: startStr || this._dateTimeForModal('08:00'),
                        end: endStr || this._dateTimeForModal('09:00'),
                        location: '',
                        description: '',
                    };
                    this.eventFormErrors = {};
                    document.getElementById('modal-event-form').showModal();
                },

                openEditEvent(event) {
                    this.eventForm = {
                        id: event.eventId,
                        title: event.title,
                        start: event.start ? event.start.replace(' ', 'T').substring(0, 16) : '',
                        end: event.end ? event.end.replace(' ', 'T').substring(0, 16) : '',
                        location: event.location || '',
                        description: event.description || '',
                    };
                    this.eventFormErrors = {};
                    document.getElementById('modal-event-form').showModal();
                },

                closeEventForm() {
                    document.getElementById('modal-event-form').close();
                },

                async submitEventForm() {
                    this.eventFormErrors = {};
                    if (!this.eventForm.title.trim()) {
                        this.eventFormErrors.title = 'Judul wajib diisi';
                        return;
                    }
                    if (!this.eventForm.start) {
                        this.eventFormErrors.start = 'Waktu mulai wajib diisi';
                        return;
                    }

                    this.eventFormLoading = true;
                    const isEdit = !!this.eventForm.id;
                    const url = isEdit ? `/events/${this.eventForm.id}` : '/events';
                    const method = isEdit ? 'PUT' : 'POST';

                    try {
                        const res = await fetch(url, {
                            method,
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                title: this.eventForm.title,
                                start: this.eventForm.start,
                                end: this.eventForm.end || null,
                                location: this.eventForm.location || null,
                                description: this.eventForm.description || null,
                            }),
                        });

                        if (!res.ok) {
                            const data = await res.json();
                            if (data.errors) this.eventFormErrors = data.errors;
                            return;
                        }

                        const saved = await res.json();
                        const mapped = {
                            title: saved.title,
                            type: 'custom',
                            eventId: saved.id,
                            start: saved.start ? saved.start.replace('T', ' ') : saved.start,
                            end: saved.end ? saved.end.replace('T', ' ') : saved.end,
                            location: saved.location,
                            description: saved.description,
                        };

                        if (isEdit) {
                            // Update in modalEvents
                            const idx = this.modalEvents.findIndex(e => e.type === 'custom' && e.eventId === saved.id);
                            if (idx !== -1) this.modalEvents.splice(idx, 1, mapped);
                            // Update in global events
                            const gi = this.events.findIndex(e => e.id === 'event-' + saved.id);
                            if (gi !== -1) {
                                this.events[gi].title = saved.title;
                                this.events[gi].extendedProps.description = saved.description;
                                this.events[gi].extendedProps.location = saved.location;
                                this.events[gi].start = saved.start;
                                this.events[gi].end = saved.end;
                            }
                        } else {
                            // Add to global events and re-render
                            this.events.push({
                                id: 'event-' + saved.id,
                                title: saved.title,
                                start: saved.start,
                                end: saved.end,
                                extendedProps: {
                                    type: 'custom',
                                    description: saved.description,
                                    location: saved.location,
                                    eventId: saved.id
                                },
                            });
                            this.render();
                            // Reload modalEvents for this date
                            const dateStr = this._dateTimeForModal('').substring(0, 10);
                            this.modalEvents.push(mapped);
                        }

                        this.closeEventForm();
                    } finally {
                        this.eventFormLoading = false;
                    }
                },

                async deleteEvent(event, idx) {
                    if (!confirm(`Hapus event "${event.title}"?`)) return;
                    try {
                        const res = await fetch(`/events/${event.eventId}`, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                        });
                        if (res.ok || res.status === 204) {
                            this.modalEvents.splice(idx, 1);
                            // Remove from global events and re-render
                            const gi = this.events.findIndex(e => e.id === 'event-' + event.eventId);
                            if (gi !== -1) this.events.splice(gi, 1);
                            this.render();
                        }
                    } catch (e) {
                        console.error('Gagal menghapus event', e);
                    }
                },

                prevMonth() {
                    this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
                    this.render();
                },

                nextMonth() {
                    this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
                    this.render();
                },

                formatDate(dateStr) {
                    const d = new Date(dateStr);
                    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
                    return d.getDate() + ' ' + months[d.getMonth()] + ' ' + d.getFullYear();
                }
            };
        }
    </script>
@endpush
