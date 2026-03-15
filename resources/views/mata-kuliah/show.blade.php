@php
    $attendanceStatusOptions = collect(\App\Enums\AttendanceStatus::cases())
        ->mapWithKeys(fn($status) => [$status->value => $status->label()])
        ->toArray();
    $attendanceColorMap = [
        \App\Enums\AttendanceStatus::HADIR->value => 'success',
        \App\Enums\AttendanceStatus::IZIN->value => 'info',
        \App\Enums\AttendanceStatus::SAKIT->value => 'warning',
        \App\Enums\AttendanceStatus::ALPHA->value => 'error',
    ];
    $attendanceCalendarEvents = collect($absensiPayload)
        ->map(function (array $attendance) use ($attendanceColorMap) {
            $meetingLabel = $attendance['meeting_number']
                ? 'Pertemuan ' . $attendance['meeting_number']
                : 'Absensi Kuliah';
            $description = collect([
                $attendance['topic'],
                $attendance['notes_count'] > 0 ? $attendance['notes_count'] . ' catatan' : null,
            ])->filter()->implode(' • ');

            return [
                'id' => 'attendance-' . $attendance['id'],
                'title' => $meetingLabel,
                'start' => $attendance['date'],
                'allDay' => true,
                'color' => $attendanceColorMap[$attendance['status']] ?? 'primary',
                'extendedProps' => [
                    'type' => 'custom',
                    'eventId' => 'attendance-' . $attendance['id'],
                    'attendanceId' => $attendance['id'],
                    'location' => $attendance['status_label'],
                    'description' => $description,
                    'status' => $attendance['status'],
                    'color' => $attendanceColorMap[$attendance['status']] ?? 'primary',
                ],
            ];
        })
        ->values();
    $tabButtonClass = 'tab h-auto gap-2 whitespace-nowrap min-h-9 px-3 py-2 text-xs sm:text-sm';

    $focusConfig = [
        'initialTaskId' => $initialTaskId,
        'initialAttendanceId' => $initialAbsensiId,
        'tasks' => $tugasPayload,
        'attendances' => $absensiPayload,
        'storageKey' => 'mata-kuliah-focus-' . $mataKuliah->id,
        'attendanceStatuses' => [
            'hadir' => \App\Enums\AttendanceStatus::HADIR->value,
            'izin' => \App\Enums\AttendanceStatus::IZIN->value,
            'sakit' => \App\Enums\AttendanceStatus::SAKIT->value,
            'alpha' => \App\Enums\AttendanceStatus::ALPHA->value,
        ],
        'doneStatus' => \App\Enums\Status::SELESAI->value,
        'progressStatus' => \App\Enums\Status::PROGRESS->value,
        'openStatus' => \App\Enums\Status::BELUM->value,
        'workspaceTab' => 'action',
        'openTaskForm' => $errors->quickTask->any(),
        'openTodoForm' => $errors->quickTodo->any(),
        'attendanceSaveUrl' => route('mata-kuliah.focus-attendance.save', $mataKuliah),
        'attendanceCalendarSyncEvent' => 'attendance-calendar-sync',
        'attendanceCalendarSelectionEvent' => 'attendance-calendar-selection-sync',
        'openAttendanceModal' => $errors->attendanceManager->any(),
        'attendanceFormDraft' => [
            'absensi_id' => old('absensi_id', ''),
            'tanggal' => old('tanggal', ''),
            'pertemuan_ke' => old('pertemuan_ke', ''),
            'status' => old('status', \App\Enums\AttendanceStatus::HADIR->value),
            'topik' => old('topik', ''),
        ],
        'hasAttendanceFormDraft' => $errors->attendanceManager->any(),
    ];
@endphp

<x-layouts.app title="Mode Fokus">
    <x-slot:header>
        <x-layouts.page-header title="Mode Fokus" description="Ruang kerja terfokus untuk {{ $mataKuliah->nama }}">
            <x-slot:actions>
                <x-ui.button type="ghost" size="sm" :href="route('mata-kuliah.index')" class="hidden sm:inline-flex">
                    <x-heroicon-o-arrow-left class="h-4 w-4" />
                    Kembali
                </x-ui.button>

                <x-ui.button type="secondary" size="sm" :href="route('mata-kuliah.edit', $mataKuliah)"
                    class="hidden sm:inline-flex">
                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                    Edit Mata Kuliah
                </x-ui.button>

                <x-ui.button type="primary" size="sm" :isSubmit="false" class="hidden sm:inline-flex"
                    onclick="document.getElementById('attendance-focus-modal')?.showModal()">
                    <x-heroicon-o-calendar-days class="h-4 w-4" />
                    Absensi
                </x-ui.button>

                @if ($mataKuliah->lms_link)
                    <x-ui.button type="primary" size="sm" :href="$mataKuliah->lms_link" target="_blank" rel="noreferrer"
                        class="hidden sm:inline-flex">
                        <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                        Buka LMS
                    </x-ui.button>
                @endif
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    <div x-data="mataKuliahFocus(@js($focusConfig))" x-init="init()" @keydown.escape.window="closeMobileFab()"
        @attendance-calendar-select.window="handleAttendanceCalendarSelect($event.detail?.event || null)"
        @attendance-calendar-date-select.window="handleAttendanceCalendarDateSelect($event.detail?.date || null)"
        class="space-y-6 pb-28 text-[13px] sm:pb-24 sm:text-base lg:pb-0">
        @include('mata-kuliah.partials.show.overview')

        <div class="grid gap-6 xl:grid-cols-[1.55fr_1fr]">
            @include('mata-kuliah.partials.show.task-column')
            @include('mata-kuliah.partials.show.focus-hub')
        </div>

        @include('mata-kuliah.partials.show.mobile-fab')
        @include('mata-kuliah.partials.show.task-modal')
        @include('mata-kuliah.partials.show.attendance-modal')
    </div>

    @push('scripts')
        <script>
            function mataKuliahFocus(config) {
                return {
                    tasks: Array.isArray(config.tasks) ? config.tasks : [],
                    attendances: Array.isArray(config.attendances) ? config.attendances : [],
                    selectedTaskId: config.initialTaskId ? Number(config.initialTaskId) : null,
                    selectedAttendanceId: config.initialAttendanceId ? Number(config.initialAttendanceId) : null,
                    taskFilter: 'all',
                    taskQuery: '',
                    workspaceTab: config.workspaceTab || 'action',
                    mobileFabOpen: false,
                    mobileFabBranch: null,
                    panels: {
                        task: Boolean(config.openTaskForm),
                        todo: Boolean(config.openTodoForm),
                    },
                    quickItemTitle: '',
                    quickItemType: 'follow-up',
                    quickItems: [],
                    storageKey: config.storageKey,
                    attendanceStatuses: config.attendanceStatuses ?? {},
                    attendanceSaveUrl: config.attendanceSaveUrl || '',
                    attendanceCalendarSyncEvent: config.attendanceCalendarSyncEvent || 'attendance-calendar-sync',
                    attendanceCalendarSelectionEvent: config.attendanceCalendarSelectionEvent ||
                        'attendance-calendar-selection-sync',
                    attendanceFormDraft: config.attendanceFormDraft ?? {},
                    hasAttendanceFormDraft: Boolean(config.hasAttendanceFormDraft),
                    openAttendanceDialogOnInit: Boolean(config.openAttendanceModal),
                    attendanceSubmitting: false,
                    attendanceDeleting: false,
                    attendanceFormErrors: {},
                    attendanceNotice: null,
                    attendanceForm: {
                        absensi_id: '',
                        tanggal: '',
                        pertemuan_ke: '',
                        status: config.attendanceStatuses?.hadir ?? '',
                        topik: '',
                    },
                    doneStatus: config.doneStatus,
                    progressStatus: config.progressStatus,
                    openStatus: config.openStatus,

                    init() {
                        this.loadQuickItems();

                        if (!this.selectedTaskId && this.tasks.length > 0) {
                            this.selectedTaskId = Number(this.tasks[0].id);
                        }

                        if (this.selectedTaskId && !this.selectedTask) {
                            this.selectedTaskId = this.tasks[0] ? Number(this.tasks[0].id) : null;
                        }

                        if (this.hasAttendanceFormDraft) {
                            this.attendanceForm = this.normalizeAttendanceForm(this.attendanceFormDraft);
                            this.selectedAttendanceId = this.attendanceForm.absensi_id
                                ? Number(this.attendanceForm.absensi_id)
                                : null;
                        } else if (this.selectedAttendance) {
                            this.loadSelectedAttendanceForm();
                        } else {
                            this.prepareNewAttendance();
                        }

                        if (this.openAttendanceDialogOnInit) {
                            this.$nextTick(() => this.showDialog('attendance-focus-modal'));
                        }

                        this.dispatchAttendanceCalendarSync();
                        this.$nextTick(() => this.dispatchAttendanceCalendarSelection(this.attendanceForm.tanggal, true));
                    },

                    get filteredTasks() {
                        return this.tasks.filter((task) => {
                            const matchesFilter = this.matchesTaskFilter(task);
                            const matchesQuery = this.matchesTaskQuery(task);

                            return matchesFilter && matchesQuery;
                        });
                    },

                    get selectedTask() {
                        return this.tasks.find((task) => Number(task.id) === Number(this.selectedTaskId)) || null;
                    },

                    get selectedAttendance() {
                        return this.attendances.find((attendance) => Number(attendance.id) === Number(this.selectedAttendanceId)) || null;
                    },

                    get quickItemsSorted() {
                        return [...this.quickItems].sort((left, right) => {
                            if (left.done !== right.done) {
                                return Number(left.done) - Number(right.done);
                            }

                            return right.id - left.id;
                        });
                    },

                    togglePanel(name) {
                        this.panels[name] = !this.panels[name];
                    },

                    toggleMobileFab() {
                        this.mobileFabOpen = !this.mobileFabOpen;

                        if (!this.mobileFabOpen) {
                            this.mobileFabBranch = null;
                            return;
                        }

                        if (!this.mobileFabBranch) {
                            this.mobileFabBranch = 'panel';
                        }
                    },

                    closeMobileFab() {
                        this.mobileFabOpen = false;
                        this.mobileFabBranch = null;
                    },

                    toggleMobileFabBranch(branch) {
                        this.mobileFabBranch = this.mobileFabBranch === branch ? null : branch;
                    },

                    goTo(url, newTab = false) {
                        this.closeMobileFab();

                        if (!url) {
                            return;
                        }

                        if (newTab) {
                            window.open(url, '_blank', 'noreferrer');
                            return;
                        }

                        window.location.href = url;
                    },

                    activateWorkspaceTab(tab, panel = null) {
                        this.workspaceTab = tab;

                        if (panel && this.panels[panel] !== undefined) {
                            this.panels[panel] = true;
                        }

                        this.mobileFabBranch = tab === 'parking' ? 'panel' : this.mobileFabBranch;
                        this.scrollToSection('focus-hub');
                    },

                    showDialog(id) {
                        document.getElementById(id)?.showModal();
                    },

                    closeDialog(id) {
                        document.getElementById(id)?.close();
                    },

                    openAttendanceModal() {
                        if (!this.selectedAttendance && !this.hasAttendanceFormDraft) {
                            this.prepareNewAttendance();
                        }

                        this.showDialog('attendance-focus-modal');
                    },

                    attendanceError(field) {
                        return this.attendanceFormErrors[field] || null;
                    },

                    setAttendanceNotice(message, type = 'success') {
                        this.attendanceNotice = {
                            message,
                            type,
                        };
                    },

                    clearAttendanceFeedback() {
                        this.attendanceFormErrors = {};
                        this.attendanceNotice = null;
                    },

                    focusTask(id) {
                        this.selectedTaskId = Number(id);
                        this.scrollToSection('task-board');
                    },

                    scrollToSection(id) {
                        const section = document.getElementById(id);

                        if (section) {
                            section.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start',
                            });
                        }
                    },

                    countByFilter(filter) {
                        if (filter === 'all') {
                            return this.tasks.length;
                        }

                        return this.tasks.filter((task) => this.matchesTaskFilter(task, filter)).length;
                    },

                    matchesTaskFilter(task, filter = this.taskFilter) {
                        if (filter === 'active') {
                            return [this.openStatus, this.progressStatus].includes(task.status);
                        }

                        if (filter === 'soon') {
                            return Boolean(task.is_due_soon);
                        }

                        if (filter === 'done') {
                            return task.status === this.doneStatus;
                        }

                        return true;
                    },

                    matchesTaskQuery(task) {
                        if (!this.taskQuery.trim()) {
                            return true;
                        }

                        const keyword = this.taskQuery.trim().toLowerCase();
                        const todoText = (task.todos || []).map((todo) => [todo.title, todo.description].filter(Boolean).join(' '))
                            .join(' ');
                        const haystack = [task.title, task.description, task.note, todoText]
                            .filter(Boolean)
                            .join(' ')
                            .toLowerCase();

                        return haystack.includes(keyword);
                    },

                    truncate(text, limit = 140) {
                        if (!text) {
                            return '';
                        }

                        return text.length > limit ? text.slice(0, limit).trim() + '...' : text;
                    },

                    statusLabel(status) {
                        if (status === this.doneStatus) {
                            return 'Selesai';
                        }

                        if (status === this.progressStatus) {
                            return 'Progress';
                        }

                        if (status === this.openStatus) {
                            return 'Belum';
                        }

                        return status;
                    },

                    statusClass(status) {
                        if (status === this.doneStatus) {
                            return 'badge-success';
                        }

                        if (status === this.progressStatus) {
                            return 'badge-warning';
                        }

                        if (status === this.openStatus) {
                            return 'badge-error';
                        }

                        return 'badge-ghost';
                    },

                    priorityLabel(priority) {
                        if (priority === 'tinggi') {
                            return 'Prioritas Tinggi';
                        }

                        if (priority === 'rendah') {
                            return 'Prioritas Rendah';
                        }

                        return 'Prioritas Sedang';
                    },

                    priorityClass(priority) {
                        if (priority === 'tinggi') {
                            return 'badge-error';
                        }

                        if (priority === 'rendah') {
                            return 'badge-success';
                        }

                        return 'badge-warning';
                    },

                    attendanceLabel(attendance = this.selectedAttendance) {
                        if (!attendance) {
                            return 'Absensi baru';
                        }

                        return attendance.meeting_number
                            ? 'Pertemuan ' + attendance.meeting_number
                            : 'Pertemuan tanpa nomor';
                    },

                    attendanceStatusClass(status) {
                        if (status === this.attendanceStatuses.hadir) {
                            return 'badge-success';
                        }

                        if (status === this.attendanceStatuses.izin) {
                            return 'badge-info';
                        }

                        if (status === this.attendanceStatuses.sakit) {
                            return 'badge-warning';
                        }

                        if (status === this.attendanceStatuses.alpha) {
                            return 'badge-error';
                        }

                        return 'badge-ghost';
                    },

                    selectAttendance(id) {
                        this.clearAttendanceFeedback();
                        this.selectedAttendanceId = Number(id);
                        this.loadSelectedAttendanceForm();
                    },

                    handleAttendanceCalendarSelect(event) {
                        const attendanceId = Number(event?.attendanceId);

                        if (!attendanceId) {
                            return;
                        }

                        this.selectAttendance(attendanceId);
                    },

                    handleAttendanceCalendarDateSelect(date) {
                        if (!date) {
                            return;
                        }

                        const existingAttendance = this.findAttendanceByDate(date);

                        if (existingAttendance) {
                            this.selectAttendance(existingAttendance.id);
                            return;
                        }

                        this.prepareNewAttendance(date);
                    },

                    loadSelectedAttendanceForm() {
                        const attendance = this.selectedAttendance;

                        if (!attendance) {
                            this.prepareNewAttendance();
                            return;
                        }

                        this.attendanceForm = this.normalizeAttendanceForm({
                            absensi_id: attendance.id,
                            tanggal: attendance.date,
                            pertemuan_ke: attendance.meeting_number ?? '',
                            status: attendance.status ?? this.attendanceStatuses.hadir,
                            topik: attendance.topic ?? '',
                        });
                    },

                    prepareNewAttendance(date = this.today()) {
                        this.clearAttendanceFeedback();
                        this.selectedAttendanceId = null;
                        this.attendanceForm = this.normalizeAttendanceForm({
                            absensi_id: '',
                            tanggal: date || this.today(),
                            pertemuan_ke: this.nextMeetingNumber(),
                            status: this.attendanceStatuses.hadir,
                            topik: '',
                        });
                    },

                    normalizeAttendanceForm(form) {
                        return {
                            absensi_id: form.absensi_id ? String(form.absensi_id) : '',
                            tanggal: form.tanggal || this.today(),
                            pertemuan_ke: form.pertemuan_ke ?? '',
                            status: form.status || this.attendanceStatuses.hadir,
                            topik: form.topik || '',
                        };
                    },

                    today() {
                        const today = new Date();
                        const offset = today.getTimezoneOffset();
                        return new Date(today.getTime() - (offset * 60 * 1000)).toISOString().slice(0, 10);
                    },

                    nextMeetingNumber() {
                        const maxMeeting = this.attendances.reduce((max, attendance) => {
                            const current = Number(attendance.meeting_number) || 0;
                            return Math.max(max, current);
                        }, 0);

                        return maxMeeting + 1;
                    },

                    findAttendanceByDate(date) {
                        return this.attendances.find((attendance) => attendance.date === date) || null;
                    },

                    sortAttendances() {
                        this.attendances = [...this.attendances].sort((left, right) => {
                            const leftDate = left.date || '';
                            const rightDate = right.date || '';

                            if (leftDate !== rightDate) {
                                return rightDate.localeCompare(leftDate);
                            }

                            const leftMeeting = Number(left.meeting_number) || 0;
                            const rightMeeting = Number(right.meeting_number) || 0;

                            if (leftMeeting !== rightMeeting) {
                                return rightMeeting - leftMeeting;
                            }

                            return Number(right.id) - Number(left.id);
                        });
                    },

                    upsertAttendance(attendance) {
                        const index = this.attendances.findIndex((entry) => Number(entry.id) === Number(attendance.id));

                        if (index === -1) {
                            this.attendances.push(attendance);
                        } else {
                            this.attendances.splice(index, 1, attendance);
                        }

                        this.sortAttendances();
                    },

                    removeAttendanceById(id) {
                        this.attendances = this.attendances.filter((entry) => Number(entry.id) !== Number(id));
                    },

                    attendanceCalendarEvents() {
                        return this.attendances.map((attendance) => {
                            const descriptionParts = [];

                            if (attendance.topic) {
                                descriptionParts.push(attendance.topic);
                            }

                            if (Number(attendance.notes_count) > 0) {
                                descriptionParts.push(attendance.notes_count + ' catatan');
                            }

                            return {
                                id: 'attendance-' + attendance.id,
                                title: attendance.meeting_number
                                    ? 'Pertemuan ' + attendance.meeting_number
                                    : 'Absensi Kuliah',
                                start: attendance.date,
                                allDay: true,
                                color: this.attendanceCalendarColor(attendance.status),
                                extendedProps: {
                                    type: 'custom',
                                    eventId: 'attendance-' + attendance.id,
                                    attendanceId: attendance.id,
                                    location: attendance.status_label,
                                    description: descriptionParts.join(' • '),
                                    status: attendance.status,
                                    color: this.attendanceCalendarColor(attendance.status),
                                },
                            };
                        });
                    },

                    attendanceCalendarColor(status) {
                        if (status === this.attendanceStatuses.hadir) {
                            return 'success';
                        }

                        if (status === this.attendanceStatuses.izin) {
                            return 'info';
                        }

                        if (status === this.attendanceStatuses.sakit) {
                            return 'warning';
                        }

                        if (status === this.attendanceStatuses.alpha) {
                            return 'error';
                        }

                        return 'primary';
                    },

                    dispatchAttendanceCalendarSync() {
                        window.dispatchEvent(new CustomEvent(this.attendanceCalendarSyncEvent, {
                            detail: {
                                events: this.attendanceCalendarEvents(),
                            },
                        }));
                    },

                    dispatchAttendanceCalendarSelection(date = this.attendanceForm.tanggal, syncMonth = false) {
                        if (!this.attendanceCalendarSelectionEvent) {
                            return;
                        }

                        window.dispatchEvent(new CustomEvent(this.attendanceCalendarSelectionEvent, {
                            detail: {
                                date: date || null,
                                syncMonth,
                            },
                        }));
                    },

                    normalizeValidationErrors(errors = {}) {
                        return Object.entries(errors).reduce((carry, [field, messages]) => {
                            carry[field] = Array.isArray(messages) ? messages[0] : messages;
                            return carry;
                        }, {});
                    },

                    async submitAttendanceForm() {
                        this.attendanceSubmitting = true;
                        this.attendanceFormErrors = {};
                        this.attendanceNotice = null;

                        const formData = new FormData();
                        formData.append('absensi_id', this.attendanceForm.absensi_id || '');
                        formData.append('tanggal', this.attendanceForm.tanggal || '');
                        formData.append('pertemuan_ke', this.attendanceForm.pertemuan_ke || '');
                        formData.append('status', this.attendanceForm.status || '');
                        formData.append('topik', this.attendanceForm.topik || '');

                        try {
                            const response = await fetch(this.attendanceSaveUrl, {
                                method: 'POST',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: formData,
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                this.attendanceFormErrors = this.normalizeValidationErrors(data.errors || {});
                                this.setAttendanceNotice(data.message || 'Gagal menyimpan absensi.', 'error');
                                return;
                            }

                            this.upsertAttendance(data.attendance);
                            this.selectedAttendanceId = Number(data.attendance.id);
                            this.loadSelectedAttendanceForm();
                            this.setAttendanceNotice(data.message || 'Data absensi berhasil disimpan.');
                            this.dispatchAttendanceCalendarSync();
                        } catch (error) {
                            this.setAttendanceNotice('Terjadi kesalahan saat menyimpan absensi.', 'error');
                        } finally {
                            this.attendanceSubmitting = false;
                        }
                    },

                    async deleteSelectedAttendance() {
                        const attendance = this.selectedAttendance;

                        if (!attendance || !attendance.delete_url) {
                            return;
                        }

                        if (!window.confirm('Hapus data absensi terpilih?')) {
                            return;
                        }

                        this.attendanceDeleting = true;
                        this.attendanceNotice = null;

                        try {
                            const response = await fetch(attendance.delete_url, {
                                method: 'DELETE',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                            });

                            const data = await response.json();

                            if (!response.ok) {
                                this.setAttendanceNotice(data.message || 'Gagal menghapus absensi.', 'error');
                                return;
                            }

                            this.removeAttendanceById(data.deleted_id ?? attendance.id);

                            if (data.next_attendance_id) {
                                this.selectedAttendanceId = Number(data.next_attendance_id);
                                this.loadSelectedAttendanceForm();
                            } else if (this.attendances[0]) {
                                this.selectedAttendanceId = Number(this.attendances[0].id);
                                this.loadSelectedAttendanceForm();
                            } else {
                                this.prepareNewAttendance();
                            }

                            this.setAttendanceNotice(data.message || 'Data absensi berhasil dihapus.');
                            this.dispatchAttendanceCalendarSync();
                        } catch (error) {
                            this.setAttendanceNotice('Terjadi kesalahan saat menghapus absensi.', 'error');
                        } finally {
                            this.attendanceDeleting = false;
                        }
                    },

                    relatedItemLabel(type) {
                        if (type === 'referensi') {
                            return 'Referensi';
                        }

                        if (type === 'ide') {
                            return 'Ide';
                        }

                        return 'Follow Up';
                    },

                    relatedItemClass(type) {
                        if (type === 'referensi') {
                            return 'badge-info';
                        }

                        if (type === 'ide') {
                            return 'badge-accent';
                        }

                        return 'badge-secondary';
                    },

                    addQuickItem() {
                        const title = this.quickItemTitle.trim();

                        if (!title) {
                            return;
                        }

                        this.quickItems.unshift({
                            id: Date.now(),
                            title,
                            type: this.quickItemType || 'follow-up',
                            done: false,
                        });

                        this.quickItemTitle = '';
                        this.persistQuickItems();
                        this.$refs.quickItemInput?.focus();
                    },

                    toggleQuickItem(id) {
                        const item = this.quickItems.find((entry) => entry.id === id);

                        if (!item) {
                            return;
                        }

                        item.done = !item.done;
                        this.persistQuickItems();
                    },

                    removeQuickItem(id) {
                        this.quickItems = this.quickItems.filter((entry) => entry.id !== id);
                        this.persistQuickItems();
                    },

                    loadQuickItems() {
                        try {
                            const items = window.localStorage.getItem(this.storageKey);
                            this.quickItems = items ? JSON.parse(items) : [];
                        } catch (error) {
                            this.quickItems = [];
                        }
                    },

                    persistQuickItems() {
                        window.localStorage.setItem(this.storageKey, JSON.stringify(this.quickItems));
                    },

                    async toggleTodo(taskId, todoId, isChecked) {
                        const task = this.tasks.find((entry) => Number(entry.id) === Number(taskId));

                        if (!task) {
                            return;
                        }

                        const todo = task.todos.find((entry) => Number(entry.id) === Number(todoId));

                        if (!todo) {
                            return;
                        }

                        const nextStatus = isChecked ? this.doneStatus : this.openStatus;
                        const previousTodoStatus = todo.status;
                        const previousTaskStatus = task.status;
                        const previousTaskProgress = task.progress;
                        const previousCompletedCount = task.todo_completed_count;

                        todo.status = nextStatus;
                        todo.status_label = this.statusLabel(nextStatus);
                        task.todo_completed_count = task.todos.filter((entry) => entry.status === this.doneStatus).length;

                        try {
                            const response = await fetch(todo.update_url, {
                                method: 'PATCH',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                },
                                body: JSON.stringify({
                                    status: nextStatus,
                                }),
                            });

                            const data = await response.json();

                            if (!response.ok || !data.success) {
                                throw new Error('Todo update failed');
                            }

                            todo.status = data.status;
                            todo.status_label = this.statusLabel(data.status);
                            task.status = data.tugas_status;
                            task.status_label = this.statusLabel(data.tugas_status);
                            task.progress = data.progress;
                            task.todo_completed_count = task.todos.filter((entry) => entry.status === this.doneStatus).length;
                        } catch (error) {
                            todo.status = previousTodoStatus;
                            todo.status_label = this.statusLabel(previousTodoStatus);
                            task.status = previousTaskStatus;
                            task.status_label = this.statusLabel(previousTaskStatus);
                            task.progress = previousTaskProgress;
                            task.todo_completed_count = previousCompletedCount;
                        }
                    },
                }
            }
        </script>
    @endpush
</x-layouts.app>
