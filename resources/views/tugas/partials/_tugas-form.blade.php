@php
    $attendanceOptions = collect($absensi ?? [])->map(function ($attendance) {
        $meetingLabel = $attendance->pertemuan_ke
            ? 'Pertemuan ' . $attendance->pertemuan_ke
            : 'Pertemuan tanpa nomor';
        $dateLabel = $attendance->tanggal?->translatedFormat('d M Y');
        $courseLabel = $attendance->mataKuliah?->nama ?? 'Mata kuliah';
        $topicLabel = $attendance->topik ? ' • ' . $attendance->topik : '';

        return [
            'id' => (string) $attendance->id,
            'course_id' => (string) $attendance->mata_kuliah_id,
            'label' => trim($courseLabel . ' • ' . $meetingLabel . ($dateLabel ? ' • ' . $dateLabel : '') . $topicLabel),
            'meeting_label' => $meetingLabel,
            'date_label' => $dateLabel,
            'topic' => $attendance->topik,
            'status_label' => $attendance->status?->label() ?? null,
        ];
    })->values();

    $todoItems = old(
        'todos',
        isset($tugas) && $tugas->todos
            ? $tugas->todos->map(fn($todo) => [
                'judul' => $todo->judul,
                'deskripsi' => $todo->deskripsi,
                'status' => optional($todo->status)->value ?? (string) $todo->status,
                'deadline' => $todo->deadline ? \Carbon\Carbon::parse($todo->deadline)->format('Y-m-d') : '',
            ])->toArray()
            : [[
                'judul' => '',
                'deskripsi' => '',
                'status' => \App\Enums\Status::BELUM->value,
                'deadline' => '',
            ]]
    );

    $formConfig = [
        'selectedCourseId' => (string) (optional($tugas)->mata_kuliah_id ?? old('mata_kuliah_id') ?? ''),
        'selectedAttendanceId' => (string) (optional($tugas)->absensi_id ?? old('absensi_id') ?? ''),
        'attendanceOptions' => $attendanceOptions,
        'todos' => $todoItems,
        'openStatus' => \App\Enums\Status::BELUM->value,
    ];
@endphp

<form method="POST" action="{{ $formAction }}" class="space-y-4" enctype="multipart/form-data"
    x-data="tugasForm(@js($formConfig))" x-init="init()">
    @csrf
    @if (isset($method) && $method === 'PUT')
        @method('PUT')
    @endif
    <x-ui.input type="hidden" name="MAX_FILE_SIZE" value="10485760" />

    <x-ui.select name="mata_kuliah_id" label="Mata Kuliah" :required="true" placeholder="Pilih mata kuliah"
        :options="$mataKuliah->pluck('nama', 'id')->toArray()"
        :value="optional($tugas)->mata_kuliah_id ?? old('mata_kuliah_id') ?? ''"
        x-model="selectedCourseId" @change="syncAttendanceSelection()" />

    <div class="space-y-3">
        <div class="w-full">
            <x-ui.select name="absensi_id" label="Absensi Terkait" placeholder=""
                :error="$errors->first('absensi_id')"
                x-model="selectedAttendanceId" x-bind:disabled="!selectedCourseId">
                <option value="">Tidak ditautkan ke pertemuan tertentu</option>
                <template x-for="attendance in filteredAttendances()" :key="attendance.id">
                    <option :value="attendance.id" x-text="attendance.label"></option>
                </template>
            </x-ui.select>

            <label class="label">
                <span class="label-text-alt text-base-content/70" x-text="attendanceHelperText()"></span>
            </label>
        </div>

        <div x-show="selectedAttendance()" class="rounded-md border border-base-300/70 bg-base-100 p-4" x-cloak>
            <div class="flex flex-wrap items-center gap-2">
                <span class="badge badge-info badge-sm" x-text="selectedAttendance()?.meeting_label"></span>
                <span class="badge badge-outline badge-sm" x-text="selectedAttendance()?.status_label"></span>
            </div>
            <div class="mt-2 text-sm text-base-content/65"
                x-text="[selectedAttendance()?.date_label, selectedAttendance()?.topic].filter(Boolean).join(' • ') || 'Absensi ini akan menjadi konteks tugas.'">
            </div>
        </div>
    </div>

    <x-ui.input name="judul" label="Judul Tugas" placeholder="Contoh: Makalah Kecerdasan Buatan"
        :required="true" :value="optional($tugas)->judul ?? old('judul') ?? ''" />

    <x-ui.textarea name="deskripsi" label="Deskripsi" placeholder="Deskripsi tugas (opsional)"
        :rows="4" :value="optional($tugas)->deskripsi ?? old('deskripsi') ?? ''" />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <x-ui.input name="deadline" label="Deadline" type="date" :required="true"
            :value="optional($tugas)->deadline ? \Carbon\Carbon::parse(optional($tugas)->deadline)->format('Y-m-d') : old('deadline') ?? ''" />
        <x-ui.select name="status" label="Status" :searchable="false" :required="true"
            placeholder="Pilih status"
            :options="\App\Enums\Status::taskOptions()"
            :value="optional(optional($tugas)->status)->value ?? old('status', \App\Enums\Status::BELUM->value)" />
        <x-ui.input name="progress" label="Progress (%)" type="number" placeholder="0" :required="true"
            :value="optional($tugas)->progress ?? old('progress', 0)" />
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <x-ui.select name="prioritas" label="Prioritas" :required="true" placeholder="Pilih prioritas"
            :options="['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi']"
            :value="optional($tugas)->prioritas ?? old('prioritas', 'sedang')" />
        <x-ui.input name="file" label="Upload File (PDF/IMG)" type="file"
            accept="application/pdf,image/*" :value="optional($tugas)->file ?? ''" />
    </div>

    <x-ui.textarea name="catatan" label="Catatan" placeholder="Catatan tambahan (opsional)"
        :value="optional($tugas)->catatan ?? old('catatan') ?? ''" />

    <div class="space-y-4">
        <label class="block font-semibold text-base-content/80">Todo List</label>

        <template x-for="(todo, idx) in todos" :key="idx">
            <div class="relative rounded-md border border-base-300/70 bg-base-100 p-4">
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-ui.input name="todo_dummy[]" x-bind:name="`todos[${idx}][judul]`" label="Judul Todo"
                        x-model="todo.judul" placeholder="Judul todo" :required="false" />
                </div>

                <x-ui.textarea name="todo_dummy[]" x-bind:name="`todos[${idx}][deskripsi]`" label="Deskripsi Todo"
                    x-model="todo.deskripsi" placeholder="Deskripsi todo (opsional)" :rows="2" />

                <div class="mt-2 flex items-center gap-2">
                    <x-ui.button type="ghost" size="sm" :isSubmit="false" class="ml-auto text-error"
                        @click="removeTodo(idx)" x-show="todos.length > 1">
                        <x-heroicon-o-x-mark class="h-4 w-4" />
                        Hapus
                    </x-ui.button>
                </div>
            </div>
        </template>

        <x-ui.button type="primary" size="sm" @click.prevent="addTodo" class="mt-2">
            <x-heroicon-o-plus class="h-4 w-4 mr-1" />
            Tambah Todo
        </x-ui.button>
    </div>

    <div class="flex justify-end gap-2 pt-4">
        <x-ui.button type="ghost" :href="$cancelUrl ?? route('tugas.index')" :isSubmit="false">Batal</x-ui.button>
        <x-ui.button type="primary">
            <x-heroicon-o-check class="h-4 w-4" />
            {{ $submitLabel ?? 'Simpan Tugas' }}
        </x-ui.button>
    </div>
</form>

@once
    @push('scripts')
        <script>
            function tugasForm(config) {
                return {
                    selectedCourseId: config.selectedCourseId ? String(config.selectedCourseId) : '',
                    selectedAttendanceId: config.selectedAttendanceId ? String(config.selectedAttendanceId) : '',
                    attendanceOptions: Array.isArray(config.attendanceOptions) ? config.attendanceOptions : [],
                    todos: Array.isArray(config.todos) ? config.todos : [],
                    openStatus: config.openStatus || @js(\App\Enums\Status::BELUM->value),

                    init() {
                        if (this.todos.length === 0) {
                            this.todos = [this.makeEmptyTodo()];
                        }

                        this.syncAttendanceSelection();
                    },

                    filteredAttendances() {
                        if (!this.selectedCourseId) {
                            return [];
                        }

                        return this.attendanceOptions.filter((attendance) => {
                            return String(attendance.course_id) === String(this.selectedCourseId);
                        });
                    },

                    selectedAttendance() {
                        return this.filteredAttendances().find((attendance) => {
                            return String(attendance.id) === String(this.selectedAttendanceId);
                        }) || null;
                    },

                    syncAttendanceSelection() {
                        if (!this.selectedAttendanceId) {
                            return;
                        }

                        const exists = this.filteredAttendances().some((attendance) => {
                            return String(attendance.id) === String(this.selectedAttendanceId);
                        });

                        if (!exists) {
                            this.selectedAttendanceId = '';
                        }
                    },

                    attendanceHelperText() {
                        if (!this.selectedCourseId) {
                            return 'Pilih mata kuliah terlebih dahulu agar daftar absensi muncul.';
                        }

                        const total = this.filteredAttendances().length;

                        if (total === 0) {
                            return 'Belum ada absensi untuk mata kuliah ini.';
                        }

                        return total + ' absensi tersedia untuk mata kuliah terpilih.';
                    },

                    addTodo() {
                        this.todos.push(this.makeEmptyTodo());
                    },

                    removeTodo(idx) {
                        if (this.todos.length <= 1) {
                            this.todos = [this.makeEmptyTodo()];
                            return;
                        }

                        this.todos.splice(idx, 1);
                    },

                    makeEmptyTodo() {
                        return {
                            judul: '',
                            deskripsi: '',
                            status: this.openStatus,
                            deadline: '',
                        };
                    },
                };
            }
        </script>
    @endpush
@endonce
