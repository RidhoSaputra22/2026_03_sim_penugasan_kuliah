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
                'id' => $todo->id,
                'judul' => $todo->judul,
                'deskripsi' => $todo->deskripsi,
                'status' => optional($todo->status)->value ?? (string) $todo->status,
                'deadline' => $todo->deadline ? \Carbon\Carbon::parse($todo->deadline)->format('Y-m-d') : '',
                'attachment_name' => $todo->attachmentName(),
                'attachment_url' => $todo->attachmentUrl(),
                'attachment_is_image' => $todo->attachmentIsImage(),
            ])->toArray()
            : [[
                'id' => null,
                'judul' => '',
                'deskripsi' => '',
                'status' => \App\Enums\Status::BELUM->value,
                'deadline' => '',
                'attachment_name' => null,
                'attachment_url' => null,
                'attachment_is_image' => false,
            ]]
    );

    $formConfig = [
        'selectedCourseId' => (string) (optional($tugas)->mata_kuliah_id ?? old('mata_kuliah_id') ?? ''),
        'selectedAttendanceId' => (string) (optional($tugas)->absensi_id ?? old('absensi_id') ?? ''),
        'attendanceOptions' => $attendanceOptions,
        'todos' => $todoItems,
        'openStatus' => \App\Enums\Status::BELUM->value,
    ];

    $currentAttachmentUrl = isset($tugas) ? $tugas->attachmentUrl() : null;
    $currentAttachmentName = isset($tugas) ? $tugas->attachmentName() : null;
    $currentAttachmentIsImage = isset($tugas) ? $tugas->attachmentIsImage() : false;
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
                <span class=" text-wrap label-text-alt text-base-content/70" x-text="attendanceHelperText()"></span>
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
        <x-ui.input name="file" label="Foto / Lampiran Tugas" type="file"
            accept=".pdf,image/*" helpText="Unggah foto tugas, screenshot LMS, atau file PDF hingga 10 MB." />
    </div>

    @if ($currentAttachmentUrl)
        <div class="rounded-md border border-base-300/70 bg-base-100 p-4">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-base-content/45">
                        Lampiran Saat Ini
                    </div>
                    <div class="mt-2 text-sm font-semibold text-base-content">
                        {{ $currentAttachmentName }}
                    </div>
                    <p class="mt-1 text-xs text-base-content/60">
                        Unggah file baru hanya jika ingin mengganti lampiran yang sekarang.
                    </p>
                </div>

                <a href="{{ $currentAttachmentUrl }}" target="_blank" rel="noreferrer"
                    class="btn btn-ghost btn-sm w-full sm:w-auto">
                    <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                    Buka Lampiran
                </a>
            </div>

            @if ($currentAttachmentIsImage)
                <div class="mt-4 overflow-hidden rounded-md border border-base-300/70 bg-base-200/40">
                    <img src="{{ $currentAttachmentUrl }}" alt="Preview lampiran {{ $currentAttachmentName }}"
                        class="h-56 w-full object-contain object-center">
                </div>
            @endif
        </div>
    @endif

    <x-ui.textarea name="catatan" label="Catatan" placeholder="Catatan tambahan (opsional)"
        :value="optional($tugas)->catatan ?? old('catatan') ?? ''" />

    <div class="space-y-4">
        <label class="block font-semibold text-base-content/80">Todo List</label>

        <template x-for="(todo, idx) in todos" :key="idx">
            <div class="relative rounded-md border border-base-300/70 bg-base-100 p-4">
                <input type="hidden" name="todo_id_dummy[]" x-bind:name="`todos[${idx}][id]`" x-model="todo.id">

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <x-ui.input name="todo_dummy[]" x-bind:name="`todos[${idx}][judul]`" label="Judul Todo"
                        x-model="todo.judul" placeholder="Judul todo" :required="false" />
                </div>

                <x-ui.textarea name="todo_dummy[]" x-bind:name="`todos[${idx}][deskripsi]`" label="Deskripsi Todo"
                    x-model="todo.deskripsi" placeholder="Deskripsi todo (opsional)" :rows="2" />

                <x-ui.input name="todo_file_dummy[]" x-bind:name="`todos[${idx}][file]`" label="Foto Checklist"
                    type="file" accept="image/*"
                    helpText="Opsional. Gunakan untuk bukti pengerjaan atau referensi visual checklist." />

                <div x-show="todo.attachment_url" class="mt-3 rounded-md border border-base-300/70 bg-base-200/40 p-3"
                    x-cloak>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-base-content/45">
                                Foto Saat Ini
                            </div>
                            <div class="mt-1 text-sm font-semibold text-base-content" x-text="todo.attachment_name || 'Foto checklist'"></div>
                            <p class="mt-1 text-xs text-base-content/60">
                                Unggah foto baru hanya jika ingin mengganti gambar checklist ini.
                            </p>
                        </div>

                        <a x-bind:href="todo.attachment_url" target="_blank" rel="noreferrer"
                            class="btn btn-ghost btn-xs w-full sm:w-auto">
                            <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                            Buka Foto
                        </a>
                    </div>

                    <div x-show="todo.attachment_is_image"
                        class="mt-3 overflow-hidden rounded-md border border-base-300/70 bg-base-100" x-cloak>
                        <img x-bind:src="todo.attachment_url"
                            x-bind:alt="'Preview foto checklist ' + (todo.attachment_name || todo.judul || 'item')"
                            class="h-40 w-full object-contain object-center">
                    </div>
                </div>

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
                            id: null,
                            judul: '',
                            deskripsi: '',
                            status: this.openStatus,
                            deadline: '',
                            attachment_name: null,
                            attachment_url: null,
                            attachment_is_image: false,
                        };
                    },
                };
            }
        </script>
    @endpush
@endonce
