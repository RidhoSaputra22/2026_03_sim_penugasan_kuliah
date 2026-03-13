<div>
    <x-ui.card id="focus-hub" class="border border-base-300/50">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h3 class="text-base font-semibold sm:text-lg">Panel Fokus</h3>
                    <p class="text-xs text-base-content/60 sm:text-sm">
                        Semua alat kerja mode fokus diringkas ke dalam tab agar halaman tetap padat.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge badge-ghost badge-sm"
                        x-text="selectedAttendance ? attendanceLabel(selectedAttendance) : 'Belum pilih absensi'"></span>
                    <span class="badge badge-outline badge-sm"
                        x-text="selectedTask ? selectedTask.title : 'Belum pilih tugas'"></span>
                </div>
            </div>

            <x-ui.tabs layout="grid" compact class="-mx-1 px-1" nav-class="hidden grid-cols-3 sm:grid"
                panels-class="mt-3 sm:mt-4">
                <x-slot:nav>
                    <button type="button" class="{{ $tabButtonClass }}"
                        :class="{ 'tab-active': workspaceTab === 'attendance' }"
                        @click="workspaceTab = 'attendance'">
                        <x-heroicon-o-academic-cap class="h-4 w-4" />
                        Absensi
                    </button>
                    <button type="button" class="{{ $tabButtonClass }}"
                        :class="{ 'tab-active': workspaceTab === 'action' }"
                        @click="workspaceTab = 'action'">
                        <x-heroicon-o-bolt class="h-4 w-4" />
                        Aksi
                    </button>
                    <button type="button" class="{{ $tabButtonClass }}"
                        :class="{ 'tab-active': workspaceTab === 'parking' }"
                        @click="workspaceTab = 'parking'">
                        <x-heroicon-o-bookmark-square class="h-4 w-4" />
                        Parkir
                    </button>
                </x-slot:nav>

                <x-slot:panels>
                    <div x-show="workspaceTab === 'attendance'" class="space-y-6" role="tabpanel" x-cloak>
                        <div id="attendance-manager" class="space-y-5">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div>
                                    <h3 class="text-base font-semibold sm:text-lg">Manajemen Absensi</h3>
                                    <p class="text-xs text-base-content/60 sm:text-sm">
                                        Simpan pertemuan kuliah, status kehadiran, dan topik yang dibahas.
                                    </p>
                                </div>

                                <div class="hidden gap-2 sm:flex">
                                    <x-ui.button type="ghost" size="sm" :isSubmit="false" @click="prepareNewAttendance()">
                                        <x-heroicon-o-plus class="h-4 w-4" />
                                        Pertemuan Baru
                                    </x-ui.button>
                                    <x-ui.button type="ghost" size="sm" :isSubmit="false" @click="togglePanel('attendance')">
                                        <span x-text="panels.attendance ? 'Tutup Form' : 'Buka Form'"></span>
                                    </x-ui.button>
                                </div>
                            </div>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div class="rounded-md border border-base-300/70 bg-base-100 p-4">
                                    <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">Kehadiran</div>
                                    <div class="mt-2 text-xl font-semibold text-success sm:text-2xl">{{ $persentaseKehadiran }}%</div>
                                    <div class="text-xs text-base-content/60 sm:text-sm">{{ $hadirCount }} pertemuan hadir</div>
                                </div>

                                <div class="rounded-md border border-base-300/70 bg-base-100 p-4">
                                    <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">Catatan</div>
                                    <div class="mt-2 text-xl font-semibold text-primary sm:text-2xl">{{ $totalCatatanAbsensi }}</div>
                                    <div class="text-xs text-base-content/60 sm:text-sm">item catatan absensi</div>
                                </div>
                            </div>

                            <div>
                                <div class="mb-3 flex items-center justify-between">
                                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-base-content/45">
                                        Riwayat Pertemuan
                                    </div>
                                    <div class="badge badge-ghost badge-sm">{{ $totalAbsensi }} data</div>
                                </div>

                                <div x-show="attendances.length === 0"
                                    class="rounded-md border border-dashed border-base-300 p-5 text-sm text-base-content/60"
                                    x-cloak>
                                    Belum ada data absensi. Tambahkan pertemuan pertama untuk mulai mencatat kehadiran.
                                </div>

                                <div x-show="attendances.length > 0" class="max-h-72 space-y-2 overflow-y-auto pr-1" x-cloak>
                                    <template x-for="attendance in attendances" :key="attendance.id">
                                        <button type="button"
                                            class="w-full rounded-md border px-4 py-3 text-left transition"
                                            :class="selectedAttendanceId === attendance.id
                                                ? 'border-primary bg-primary/5 shadow-md shadow-primary/10'
                                                : 'border-base-300/70 bg-base-100 hover:border-primary/30 hover:bg-base-200/35'"
                                            @click="selectAttendance(attendance.id)">
                                            <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                                                <div class="min-w-0 flex-1">
                                                    <div class="font-semibold text-base-content"
                                                        x-text="attendanceLabel(attendance)"></div>
                                                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-base-content/60 sm:text-sm">
                                                        <span x-text="attendance.date_label"></span>
                                                        <span class="hidden sm:inline">•</span>
                                                        <span class="truncate"
                                                            x-text="attendance.topic || 'Topik pertemuan belum dicatat.'"></span>
                                                    </div>
                                                </div>

                                                <div class="flex flex-wrap items-center gap-2 xl:justify-end">
                                                    <span class="badge badge-sm"
                                                        :class="attendanceStatusClass(attendance.status)"
                                                        x-text="attendance.status_label"></span>
                                                    <span class="text-[11px] uppercase tracking-wide text-base-content/45"
                                                        x-text="attendance.meeting_number ? 'Pertemuan ke-' + attendance.meeting_number : 'Nomor pertemuan opsional'"></span>
                                                    <div class="flex items-center gap-3 text-[11px] uppercase tracking-wide text-base-content/45">
                                                        <span x-text="attendance.notes_count + ' catatan'"></span>
                                                        <span x-text="attendance.linked_task_count + ' tugas'"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <form method="POST" action="{{ route('mata-kuliah.focus-attendance.save', $mataKuliah) }}"
                                class="space-y-4 rounded-md border border-base-300/70 bg-base-100 p-4"
                                x-show="panels.attendance" x-cloak>
                                @csrf

                                <input type="hidden" name="absensi_id" x-model="attendanceForm.absensi_id">

                                @if ($errors->attendanceManager->any())
                                    <x-ui.alert type="error">
                                        {{ $errors->attendanceManager->first() }}
                                    </x-ui.alert>
                                @endif

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <x-ui.input name="tanggal" type="date" label="Tanggal Pertemuan"
                                        x-model="attendanceForm.tanggal" :error="$errors->attendanceManager->first('tanggal')" />
                                    <x-ui.input name="pertemuan_ke" type="number" min="1" max="32" label="Pertemuan Ke"
                                        placeholder="Contoh: 6" x-model="attendanceForm.pertemuan_ke"
                                        :error="$errors->attendanceManager->first('pertemuan_ke')" />
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <x-ui.select name="status" label="Status Kehadiran" :searchable="false"
                                        :options="$attendanceStatusOptions"
                                        x-model="attendanceForm.status" :error="$errors->attendanceManager->first('status')" />
                                    <x-ui.input name="topik" label="Topik Pertemuan" placeholder="Contoh: UTS dan evaluasi proyek"
                                        x-model="attendanceForm.topik" :error="$errors->attendanceManager->first('topik')" />
                                </div>

                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="text-xs uppercase tracking-wide text-base-content/45">
                                        <span x-text="attendanceForm.absensi_id ? 'Mode edit absensi' : 'Mode tambah absensi'"></span>
                                    </div>

                                    <div class="flex gap-2">
                                        <x-ui.button type="ghost" size="sm" :isSubmit="false" @click="prepareNewAttendance()">
                                            Reset
                                        </x-ui.button>
                                        <x-ui.button type="primary" size="sm">
                                            <x-heroicon-o-check class="h-4 w-4" />
                                            <span x-text="attendanceForm.absensi_id ? 'Update Absensi' : 'Simpan Absensi'"></span>
                                        </x-ui.button>
                                    </div>
                                </div>
                            </form>

                            <form method="POST" x-show="selectedAttendance && selectedAttendance.delete_url" x-cloak
                                :action="selectedAttendance ? selectedAttendance.delete_url : '#'">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-error btn-sm btn-outline w-full">
                                    <x-heroicon-o-trash class="h-4 w-4" />
                                    Hapus Absensi Terpilih
                                </button>
                            </form>
                        </div>

                        <div class="border-t border-base-300/60 pt-6">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-semibold sm:text-lg">Catatan Pertemuan</h3>
                                    <p class="text-xs text-base-content/60 sm:text-sm">
                                        Catatan akan disimpan sebagai array JSON pada absensi yang sedang dipilih.
                                    </p>
                                </div>

                                <x-ui.tabs compact class="hidden w-auto sm:block">
                                    <button type="button" class="{{ $miniTabButtonClass }}"
                                        :class="{ 'tab-active': notesMode === 'edit' }"
                                        @click="notesMode = 'edit'">
                                        Edit
                                    </button>
                                    <button type="button" class="{{ $miniTabButtonClass }}"
                                        :class="{ 'tab-active': notesMode === 'preview' }"
                                        @click="notesMode = 'preview'">
                                        Preview
                                    </button>
                                </x-ui.tabs>
                            </div>

                            <div x-show="selectedAttendance" class="mt-5" x-cloak>
                                <div class="rounded-md border border-base-300/70 bg-base-100 p-4">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="badge badge-sm badge-outline"
                                            :class="attendanceStatusClass(selectedAttendance?.status)"
                                            x-text="selectedAttendance?.status_label"></span>
                                        <span class="badge badge-sm badge-ghost"
                                            x-text="attendanceLabel(selectedAttendance)"></span>
                                    </div>
                                    <div class="mt-2 text-sm text-base-content/65"
                                        x-text="selectedAttendance?.date_label + ' • ' + (selectedAttendance?.topic || 'Topik belum dicatat')">
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('mata-kuliah.focus-attendance-notes.update', $mataKuliah) }}"
                                    class="mt-5 space-y-4">
                                    @csrf
                                    @method('PUT')

                                    <input type="hidden" name="absensi_id" x-model="selectedAttendanceId">

                                    @if ($errors->attendanceNotes->any())
                                        <x-ui.alert type="error">
                                            {{ $errors->attendanceNotes->first() }}
                                        </x-ui.alert>
                                    @endif

                                    <div x-show="notesMode === 'edit'" class="space-y-4" x-cloak>
                                        <template x-for="(note, idx) in noteItems" :key="note.localId">
                                            <div class="rounded-md border border-base-300/70 bg-base-100 p-4">
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-base-content/45"
                                                        x-text="'Catatan ' + (idx + 1)"></div>

                                                    <button type="button" class="btn btn-ghost btn-xs text-error"
                                                        @click="removeNote(idx)">
                                                        <x-heroicon-o-trash class="h-4 w-4" />
                                                    </button>
                                                </div>

                                                <div class="mt-3 space-y-4">
                                                    <x-ui.input name="catatan_dummy[][judul]" label="Judul Catatan"
                                                        placeholder="Contoh: insight dosen"
                                                        x-bind:name="`catatan[${idx}][judul]`"
                                                        x-model="note.judul" />

                                                    <x-ui.textarea name="catatan_dummy[][isi]" label="Isi Catatan" rows="3"
                                                        placeholder="Tulis poin-poin penting dari pertemuan ini..."
                                                        x-bind:name="`catatan[${idx}][isi]`"
                                                        x-model="note.isi" />
                                                </div>
                                            </div>
                                        </template>

                                        <div class="flex flex-wrap items-center justify-between gap-2">
                                            <x-ui.button type="ghost" size="sm" :isSubmit="false" @click="addNote()">
                                                <x-heroicon-o-plus class="h-4 w-4" />
                                                Tambah Baris Catatan
                                            </x-ui.button>

                                            <div class="flex items-center gap-3">
                                                <div class="text-xs uppercase tracking-wide text-base-content/45">
                                                    <span x-text="filledNoteCount()"></span> item terisi
                                                </div>
                                                <x-ui.button type="primary" size="sm">
                                                    <x-heroicon-o-check class="h-4 w-4" />
                                                    Simpan Catatan
                                                </x-ui.button>
                                            </div>
                                        </div>
                                    </div>

                                    <div x-show="notesMode === 'preview'" class="space-y-3" x-cloak>
                                        <div x-show="filledNoteCount() === 0"
                                            class="rounded-md border border-dashed border-base-300 p-5 text-sm text-base-content/60">
                                            Belum ada catatan yang terisi untuk pertemuan ini.
                                        </div>

                                        <template x-for="(note, idx) in previewNotes()" :key="note.localId">
                                            <div class="rounded-md border border-base-300/70 bg-base-100 p-4">
                                                <div class="font-semibold text-base-content"
                                                    x-text="note.judul || 'Catatan ' + (idx + 1)"></div>
                                                <pre class="mt-2 whitespace-pre-wrap font-sans text-sm leading-6 text-base-content/70"
                                                    x-text="note.isi || 'Tidak ada isi catatan.'"></pre>
                                            </div>
                                        </template>
                                    </div>
                                </form>
                            </div>

                            <div x-show="!selectedAttendance"
                                class="mt-5 rounded-md border border-dashed border-base-300 p-5 text-sm text-base-content/60"
                                x-cloak>
                                Pilih data absensi terlebih dahulu agar catatan tersimpan ke pertemuan yang benar.
                            </div>
                        </div>
                    </div>

                    <div x-show="workspaceTab === 'action'" class="space-y-6" role="tabpanel" x-cloak>
                        <div>
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-semibold sm:text-lg">Tugas Terkait Absensi</h3>
                                    <p class="text-xs text-base-content/60 sm:text-sm">
                                        Tugas pada panel ini otomatis mengikuti absensi yang sedang dipilih.
                                    </p>
                                </div>

                                <div class="badge badge-ghost badge-sm">
                                    <span x-text="selectedAttendanceTaskCount()"></span>&nbsp;tugas
                                </div>
                            </div>

                            <div x-show="!selectedAttendance"
                                class="mt-5 rounded-md border border-dashed border-base-300 p-5 text-sm text-base-content/60"
                                x-cloak>
                                Pilih absensi terlebih dahulu untuk melihat tugas per pertemuan.
                            </div>

                            <div x-show="selectedAttendance && selectedAttendanceTasks().length === 0"
                                class="mt-5 rounded-md border border-dashed border-base-300 p-5 text-sm text-base-content/60"
                                x-cloak>
                                Belum ada tugas yang ditautkan ke pertemuan ini.
                                <button type="button" class="btn btn-link btn-sm hidden px-0 sm:inline-flex"
                                    @click="linkTaskToAttendance = true; panels.task = true">
                                    Tautkan tugas baru ke pertemuan ini
                                </button>
                            </div>

                            <div x-show="selectedAttendanceTasks().length > 0" class="mt-5 space-y-3" x-cloak>
                                <template x-for="task in selectedAttendanceTasks()" :key="task.id">
                                    <button type="button"
                                        class="w-full rounded-md border border-base-300/70 bg-base-100 px-4 py-3 text-left transition hover:border-primary/30 hover:bg-base-200/35"
                                        @click="focusTask(task.id)">
                                        <div class="flex flex-col gap-3 xl:flex-row xl:items-center xl:justify-between">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="badge badge-outline badge-sm"
                                                        :class="statusClass(task.status)" x-text="task.status_label"></span>
                                                    <span class="badge badge-sm" :class="priorityClass(task.priority)"
                                                        x-text="priorityLabel(task.priority)"></span>
                                                </div>
                                                <div class="mt-2 truncate font-semibold text-base-content" x-text="task.title"></div>
                                                <p class="mt-0.5 truncate text-xs text-base-content/65 sm:text-sm"
                                                    x-text="truncate(task.description || 'Belum ada deskripsi tugas.', 80)"></p>
                                            </div>

                                            <div class="rounded-md bg-base-200/60 px-3 py-2 text-left text-xs xl:min-w-36 xl:text-right">
                                                <div x-text="task.deadline_label"></div>
                                                <div class="mt-0.5"
                                                    :class="task.is_overdue ? 'text-error' : (task.is_due_soon ? 'text-warning' : 'text-base-content/55')"
                                                    x-text="task.deadline_relative"></div>
                                            </div>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div id="quick-task" class="border-t border-base-300/60 pt-6">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-semibold sm:text-lg">Quick Add Tugas</h3>
                                    <p class="text-xs text-base-content/60 sm:text-sm">
                                        Tambah tugas baru tanpa keluar dari mode fokus.
                                    </p>
                                </div>

                                <x-ui.button type="ghost" size="sm" :isSubmit="false" class="hidden sm:inline-flex"
                                    @click="togglePanel('task')">
                                    <span x-text="panels.task ? 'Tutup' : 'Buka'"></span>
                                </x-ui.button>
                            </div>

                            <form method="POST" action="{{ route('mata-kuliah.focus-task', $mataKuliah) }}" class="mt-5 space-y-4"
                                x-show="panels.task" x-cloak>
                                @csrf

                                <input type="hidden" name="task_absensi_id"
                                    :value="linkTaskToAttendance && selectedAttendanceId ? selectedAttendanceId : ''">

                                @if ($errors->quickTask->has('task_absensi_id'))
                                    <x-ui.alert type="error">
                                        {{ $errors->quickTask->first('task_absensi_id') }}
                                    </x-ui.alert>
                                @endif

                                <div class="rounded-md border border-base-300/70 bg-base-100 p-4">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div>
                                            <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">
                                                Koneksi Absensi
                                            </div>
                                            <div class="mt-2 text-sm font-semibold text-base-content sm:text-base">
                                                <span x-text="linkTaskToAttendance && selectedAttendance
                                                    ? 'Tugas akan ditautkan ke ' + attendanceLabel(selectedAttendance)
                                                    : 'Tugas hanya terhubung ke mata kuliah ini'"></span>
                                            </div>
                                            <div class="mt-1 text-xs text-base-content/60 sm:text-sm"
                                                x-text="linkTaskToAttendance && selectedAttendance
                                                    ? [selectedAttendance.date_label, selectedAttendance.topic].filter(Boolean).join(' • ') || 'Pilih absensi untuk menyimpan konteks per pertemuan.'
                                                    : 'Aktifkan tautan jika tugas ini lahir dari satu pertemuan tertentu.'">
                                            </div>
                                        </div>

                                        <label class="flex items-center gap-3 rounded-md border border-base-300/70 px-3 py-2">
                                            <input type="checkbox" class="checkbox checkbox-primary"
                                                x-model="linkTaskToAttendance" :disabled="attendances.length === 0">
                                            <span class="text-xs font-medium text-base-content sm:text-sm">Tautkan ke absensi aktif</span>
                                        </label>
                                    </div>

                                    <div x-show="linkTaskToAttendance && !selectedAttendance"
                                        class="mt-3 rounded-md border border-dashed border-base-300 p-3 text-sm text-base-content/60"
                                        x-cloak>
                                        Pilih absensi dari panel manajemen absensi terlebih dahulu agar tugas bisa ditautkan ke pertemuan.
                                    </div>
                                </div>

                                <x-ui.input name="task_judul" label="Judul Tugas" placeholder="Contoh: Resume pertemuan 4"
                                    :value="old('task_judul')" :error="$errors->quickTask->first('task_judul')" />

                                <x-ui.textarea name="task_deskripsi" label="Deskripsi" rows="3"
                                    placeholder="Tambahkan konteks singkat agar tugas mudah dipahami saat dibuka lagi."
                                    :value="old('task_deskripsi')" :error="$errors->quickTask->first('task_deskripsi')" />

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <x-ui.input name="task_deadline" type="date" label="Deadline"
                                        :value="old('task_deadline')" :error="$errors->quickTask->first('task_deadline')" />

                                    <x-ui.select name="task_prioritas" label="Prioritas" :searchable="false"
                                        :options="['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi']"
                                        :value="old('task_prioritas', 'sedang')" :error="$errors->quickTask->first('task_prioritas')" />
                                </div>

                                <x-ui.textarea name="task_catatan" label="Catatan Internal" rows="3"
                                    placeholder="Contoh: fokus ke referensi jurnal terbaru atau cek rubrik penilaian."
                                    :value="old('task_catatan')" :error="$errors->quickTask->first('task_catatan')" />

                                <div class="flex justify-end">
                                    <x-ui.button type="primary" size="sm">
                                        <x-heroicon-o-plus class="h-4 w-4" />
                                        Tambah Tugas
                                    </x-ui.button>
                                </div>
                            </form>
                        </div>

                        <div id="quick-todo" class="border-t border-base-300/60 pt-6">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-semibold sm:text-lg">Quick Add Checklist</h3>
                                    <p class="text-xs text-base-content/60 sm:text-sm">
                                        Checklist akan ditempelkan ke tugas yang sedang dipilih pada papan fokus.
                                    </p>
                                </div>

                                <x-ui.button type="ghost" size="sm" :isSubmit="false" class="hidden sm:inline-flex"
                                    @click="togglePanel('todo')">
                                    <span x-text="panels.todo ? 'Tutup' : 'Buka'"></span>
                                </x-ui.button>
                            </div>

                            <div x-show="tasks.length === 0"
                                class="mt-5 rounded-md border border-dashed border-base-300 p-5 text-sm text-base-content/60"
                                x-cloak>
                                Tambahkan tugas terlebih dahulu sebelum membuat checklist.
                            </div>

                            <form method="POST" action="{{ route('mata-kuliah.focus-todo', $mataKuliah) }}"
                                class="mt-5 space-y-4" x-show="tasks.length > 0 && panels.todo" x-cloak>
                                @csrf

                                <input type="hidden" name="tugas_id" x-model="selectedTaskId">

                                <div class="rounded-md border border-base-300/70 bg-base-100 p-4">
                                    <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">Tugas Aktif</div>
                                    <div class="mt-2 text-sm font-semibold text-base-content sm:text-base"
                                        x-text="selectedTask ? selectedTask.title : 'Pilih tugas terlebih dahulu'"></div>
                                    <div class="mt-1 text-xs text-base-content/60 sm:text-sm"
                                        x-text="selectedTask ? selectedTask.deadline_label + ' • ' + selectedTask.deadline_relative : 'Checklist akan aktif setelah tugas dipilih.'">
                                    </div>
                                </div>

                                <x-ui.input name="todo_judul" label="Judul Checklist" placeholder="Contoh: Cari 3 referensi jurnal"
                                    :value="old('todo_judul')" :error="$errors->quickTodo->first('todo_judul')" />

                                <x-ui.textarea name="todo_deskripsi" label="Deskripsi" rows="3"
                                    placeholder="Opsional, gunakan jika perlu penjelasan tambahan."
                                    :value="old('todo_deskripsi')" :error="$errors->quickTodo->first('todo_deskripsi')" />

                                <x-ui.input name="todo_deadline" type="date" label="Deadline Checklist"
                                    :value="old('todo_deadline')" :error="$errors->quickTodo->first('todo_deadline')" />

                                <div class="flex justify-end">
                                    <button type="submit" class="btn btn-primary btn-sm" :disabled="!selectedTaskId">
                                        <x-heroicon-o-plus class="h-4 w-4" />
                                        Tambah Checklist
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div x-show="workspaceTab === 'parking'" class="space-y-5" role="tabpanel" x-cloak>
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold sm:text-lg">Parkir Fokus</h3>
                                <p class="text-xs text-base-content/60 sm:text-sm">
                                    Simpan hal-hal terkait lain yang perlu diingat saat belajar untuk mata kuliah ini.
                                </p>
                            </div>

                            <div class="badge badge-ghost badge-sm">
                                <span x-text="quickItems.length"></span>&nbsp;item
                            </div>
                        </div>

                        <div class="grid gap-4">
                            <x-ui.input name="focus_item_draft" label="Item Terkait"
                                placeholder="Contoh: tanya dosen soal format presentasi" x-ref="quickItemInput"
                                x-model="quickItemTitle" @keydown.enter.prevent="addQuickItem()" />

                            <div class="grid gap-4 sm:grid-cols-[1fr_auto]">
                                <x-ui.select name="focus_item_type" label="Jenis Item" :searchable="false"
                                    :options="[
                                        'follow-up' => 'Follow Up',
                                        'referensi' => 'Referensi',
                                        'ide' => 'Ide',
                                    ]"
                                    x-model="quickItemType" />

                                <div class="flex items-end">
                                    <x-ui.button type="secondary" size="sm" class="w-full sm:w-auto" :isSubmit="false"
                                        @click="addQuickItem()">
                                        <x-heroicon-o-plus class="h-4 w-4" />
                                        Tambah Item
                                    </x-ui.button>
                                </div>
                            </div>
                        </div>

                        <div x-show="quickItems.length === 0"
                            class="rounded-md border border-dashed border-base-300 p-5 text-sm text-base-content/60"
                            x-cloak>
                            Belum ada item. Gunakan panel ini untuk parkir referensi, ide, atau tindak lanjut cepat.
                        </div>

                        <div x-show="quickItems.length > 0" class="space-y-3" x-cloak>
                            <template x-for="item in quickItemsSorted" :key="item.id">
                                <div class="flex items-start gap-3 rounded-md border border-base-300/70 bg-base-100 p-4">
                                    <input type="checkbox" class="checkbox checkbox-secondary mt-0.5"
                                        :checked="item.done" @change="toggleQuickItem(item.id)">

                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <div class="font-medium text-base-content"
                                                :class="item.done ? 'line-through text-base-content/45' : ''"
                                                x-text="item.title"></div>
                                            <span class="badge badge-xs" :class="relatedItemClass(item.type)"
                                                x-text="relatedItemLabel(item.type)"></span>
                                        </div>
                                        <div class="mt-2 text-xs uppercase tracking-wide text-base-content/45"
                                            x-text="item.done ? 'Sudah ditandai selesai' : 'Masih perlu ditindaklanjuti'"></div>
                                    </div>

                                    <button type="button" class="btn btn-ghost btn-xs text-error"
                                        @click="removeQuickItem(item.id)">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </x-slot:panels>
            </x-ui.tabs>
        </div>
    </x-ui.card>
</div>
