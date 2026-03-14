@php
    $attendanceStatusOptions = collect(\App\Enums\AttendanceStatus::cases())
        ->mapWithKeys(fn($status) => [$status->value => $status->label()])
        ->toArray();
    $tabButtonClass = 'tab h-auto gap-2 whitespace-nowrap min-h-9 px-3 py-2 text-xs sm:text-sm';
    $miniTabButtonClass = 'tab h-auto gap-2 whitespace-nowrap min-h-8 px-2 py-1 text-[11px]';

    $focusConfig = [
        'courseId' => $mataKuliah->id,
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
        'workspaceTab' => $errors->quickTask->any() || $errors->quickTodo->any()
            ? 'action'
            : ($errors->attendanceManager->any() || $errors->attendanceNotes->any() ? 'attendance' : 'attendance'),
        'openTaskForm' => $errors->quickTask->any(),
        'openTodoForm' => $errors->quickTodo->any(),
        'openAttendanceForm' => $errors->attendanceManager->any() || count($absensiPayload) === 0,
        'linkTaskToAttendance' => old('task_absensi_id') !== null
            ? old('task_absensi_id') !== ''
            : count($absensiPayload) > 0,
        'attendanceFormDraft' => [
            'absensi_id' => old('absensi_id', ''),
            'tanggal' => old('tanggal', ''),
            'pertemuan_ke' => old('pertemuan_ke', ''),
            'status' => old('status', \App\Enums\AttendanceStatus::HADIR->value),
            'topik' => old('topik', ''),
        ],
        'hasAttendanceFormDraft' => $errors->attendanceManager->any(),
        'noteItemsDraft' => old('catatan', []),
        'hasNoteDraft' => $errors->attendanceNotes->any(),
        'totalAttendanceNotes' => $totalCatatanAbsensi,
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
        class="space-y-6 pb-28 text-[13px] sm:pb-24 sm:text-base lg:pb-0">
        @include('mata-kuliah.partials.show.overview')

        <x-ui.callendar/>


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
                    notesMode: 'edit',
                    workspaceTab: config.workspaceTab || 'attendance',
                    mobileFabOpen: false,
                    mobileFabBranch: null,
                    noteItems: [],
                    panels: {
                        attendance: Boolean(config.openAttendanceForm),
                        task: Boolean(config.openTaskForm),
                        todo: Boolean(config.openTodoForm),
                    },
                    linkTaskToAttendance: Boolean(config.linkTaskToAttendance),
                    attendanceForm: {
                        absensi_id: '',
                        tanggal: '',
                        pertemuan_ke: '',
                        status: config.attendanceStatuses?.hadir ?? '',
                        topik: '',
                    },
                    quickItemTitle: '',
                    quickItemType: 'follow-up',
                    quickItems: [],
                    storageKey: config.storageKey,
                    doneStatus: config.doneStatus,
                    progressStatus: config.progressStatus,
                    openStatus: config.openStatus,
                    attendanceStatuses: config.attendanceStatuses ?? {},
                    attendanceFormDraft: config.attendanceFormDraft ?? {},
                    hasAttendanceFormDraft: Boolean(config.hasAttendanceFormDraft),
                    noteItemsDraft: Array.isArray(config.noteItemsDraft) ? config.noteItemsDraft : [],
                    hasNoteDraft: Boolean(config.hasNoteDraft),
                    totalAttendanceNotes: Number(config.totalAttendanceNotes || 1),

                    init() {
                        this.loadQuickItems();

                        if (!this.selectedTaskId && this.tasks.length > 0) {
                            this.selectedTaskId = Number(this.tasks[0].id);
                        }

                        if (this.selectedTaskId && !this.selectedTask) {
                            this.selectedTaskId = this.tasks[0] ? Number(this.tasks[0].id) : null;
                        }

                        if (this.selectedAttendanceId && this.selectedAttendance) {
                            this.loadSelectedAttendanceState();
                        } else if (this.attendances.length > 0) {
                            this.selectedAttendanceId = Number(this.attendances[0].id);
                            this.loadSelectedAttendanceState();
                        } else {
                            this.prepareNewAttendance();
                        }

                        if (this.hasAttendanceFormDraft) {
                            this.attendanceForm = this.normalizeAttendanceForm(this.attendanceFormDraft);
                            this.panels.attendance = true;
                        }

                        if (this.hasNoteDraft) {
                            this.noteItems = this.normalizeNoteItems(this.noteItemsDraft);
                            this.notesMode = 'edit';
                        }

                        if (!this.selectedAttendance && this.attendances.length === 0) {
                            this.linkTaskToAttendance = false;
                        }
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

                    focusTask(id) {
                        this.selectedTaskId = Number(id);
                        this.scrollToSection('task-board');
                    },

                    focusAttendance(id) {
                        this.selectAttendance(id);
                        this.activateWorkspaceTab('attendance', 'attendance');
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

                    selectedAttendanceTasks() {
                        if (!this.selectedAttendanceId) {
                            return [];
                        }

                        return this.tasks.filter((task) => {
                            return Number(task.absensi_id) === Number(this.selectedAttendanceId);
                        });
                    },

                    selectedAttendanceTaskCount() {
                        return this.selectedAttendanceTasks().length;
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
                            return 'Belum memilih absensi';
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
                        this.selectedAttendanceId = Number(id);
                        this.loadSelectedAttendanceState();
                        this.workspaceTab = 'attendance';
                        this.panels.attendance = true;
                    },

                    loadSelectedAttendanceState() {
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

                        this.noteItems = this.normalizeNoteItems(attendance.notes);
                    },

                    prepareNewAttendance() {
                        this.selectedAttendanceId = null;
                        this.notesMode = 'edit';
                        this.panels.attendance = true;
                        this.attendanceForm = this.normalizeAttendanceForm({
                            absensi_id: '',
                            tanggal: this.today(),
                            pertemuan_ke: this.nextMeetingNumber(),
                            status: this.attendanceStatuses.hadir,
                            topik: '',
                        });
                        this.noteItems = [this.makeEmptyNote()];
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

                    normalizeNoteItems(items) {
                        const normalized = (Array.isArray(items) ? items : [])
                            .map((note) => ({
                                localId: this.makeNoteId(),
                                judul: note?.judul ?? '',
                                isi: note?.isi ?? '',
                            }))
                            .filter((note) => {
                                return String(note.judul).trim() !== '' || String(note.isi).trim() !== '';
                            });

                        return normalized.length > 0 ? normalized : [this.makeEmptyNote()];
                    },

                    previewNotes() {
                        return this.noteItems.filter((note) => {
                            return String(note.judul).trim() !== '' || String(note.isi).trim() !== '';
                        });
                    },

                    filledNoteCount() {
                        return this.previewNotes().length;
                    },

                    addNote() {
                        this.noteItems.push(this.makeEmptyNote());
                        this.notesMode = 'edit';
                    },

                    removeNote(idx) {
                        if (this.noteItems.length <= 1) {
                            this.noteItems = [this.makeEmptyNote()];
                            return;
                        }

                        this.noteItems.splice(idx, 1);
                    },

                    makeEmptyNote() {
                        return {
                            localId: this.makeNoteId(),
                            judul: '',
                            isi: '',
                        };
                    },

                    makeNoteId() {
                        return Date.now().toString(36) + Math.random().toString(36).slice(2);
                    },

                    today() {
                        return new Date().toISOString().slice(0, 10);
                    },

                    nextMeetingNumber() {
                        const maxMeeting = this.attendances.reduce((max, attendance) => {
                            const current = Number(attendance.meeting_number) || 1;
                            return Math.max(max, current);
                        }, 0);

                        return maxMeeting + 1;
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
