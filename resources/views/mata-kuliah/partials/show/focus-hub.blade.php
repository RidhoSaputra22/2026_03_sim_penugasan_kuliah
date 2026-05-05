<div class="hidden sm:block">
    <x-ui.card id="focus-hub" class="border border-base-300/50">
        <div class="flex flex-col gap-4">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h3 class="text-base font-semibold sm:text-lg">Panel Fokus</h3>

                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <span class="badge badge-outline badge-sm"
                        x-text="selectedTask ? selectedTask.title : 'Belum pilih tugas'"></span>
                </div>
            </div>

            <x-ui.tabs layout="grid" compact class="-mx-1 px-1" nav-class="hidden grid-cols-2 sm:grid"
                panels-class="mt-3 sm:mt-4">
                <x-slot:nav>
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
                    <div x-show="workspaceTab === 'action'" class="space-y-6" role="tabpanel" x-cloak>
                        <div id="quick-wizard">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h3 class="text-base font-semibold sm:text-lg">Quick Wizard</h3>

                                </div>

                                <x-ui.button type="ghost" size="sm" :isSubmit="false" class="hidden sm:inline-flex"
                                    @click="togglePanel('wizard')">
                                    <span x-text="panels.wizard ? 'Tutup' : 'Mulai'"></span>
                                </x-ui.button>
                            </div>

                            <div class="mt-5 space-y-5" x-show="panels.wizard" x-cloak>
                                <div x-show="quickNotice" x-cloak class="alert py-3"
                                    :class="quickNotice?.type === 'error' ? 'alert-error' : 'alert-success'">
                                    <span x-text="quickNotice?.message"></span>
                                </div>

                                <div class="grid gap-2 sm:grid-cols-2">
                                    <button type="button"
                                        class="rounded-md border px-4 py-3 text-left transition duration-200"
                                        :class="quickWizardStep === 1
                                            ? 'border-primary/70 bg-primary/[0.05] shadow-lg shadow-primary/10'
                                            : 'border-base-300/70 bg-base-100 hover:border-base-300 hover:bg-base-200/35'"
                                        @click="setQuickWizardStep(1)">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-base-content/45">
                                                Langkah 1
                                            </div>
                                            <span class="badge badge-sm"
                                                :class="quickWizardStep > 1 ? 'badge-success' : 'badge-primary'">
                                                Tugas
                                            </span>
                                        </div>
                                        <div class="mt-2 text-sm font-semibold text-base-content"
                                            x-text="quickTaskMode === 'edit' ? 'Edit tugas' : 'Buat tugas baru'"></div>
                                        <p class="mt-1 text-xs leading-5 text-base-content/60"
                                            x-text="quickTaskMode === 'edit'
                                                ? 'Perbarui judul, deadline, prioritas, dan catatan internal tugas.'
                                                : 'Isi judul, deadline, prioritas, dan catatan internal tugas.'">
                                        </p>
                                    </button>

                                    <button type="button"
                                        class="rounded-md border px-4 py-3 text-left transition duration-200"
                                        :class="quickWizardStep === 2
                                            ? 'border-primary/70 bg-primary/[0.05] shadow-lg shadow-primary/10'
                                            : 'border-base-300/70 bg-base-100 hover:border-base-300 hover:bg-base-200/35'"
                                        @click="setQuickWizardStep(2)">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="text-[11px] font-semibold uppercase tracking-[0.18em] text-base-content/45">
                                                Langkah 2
                                            </div>
                                            <span class="badge badge-sm"
                                                :class="quickWizardStep === 2 ? 'badge-primary' : 'badge-ghost'">
                                                Checklist
                                            </span>
                                        </div>
                                        <div class="mt-2 text-sm font-semibold text-base-content"
                                            x-text="quickTodoMode === 'edit' ? 'Edit checklist' : 'Tambah checklist'"></div>
                                        <p class="mt-1 text-xs leading-5 text-base-content/60"
                                            x-text="quickTodoMode === 'edit'
                                                ? 'Perbarui judul, deskripsi, atau deadline checklist terpilih.'
                                                : 'Tempelkan checklist ke tugas yang sedang aktif di papan fokus.'">
                                        </p>
                                    </button>
                                </div>

                                <form method="POST" action="{{ route('mata-kuliah.focus-task', $mataKuliah) }}"
                                    enctype="multipart/form-data"
                                    class="space-y-4" x-show="quickWizardStep === 1" x-cloak x-ref="quickTaskForm"
                                    @submit.prevent="submitQuickTask($event)">
                                    @csrf
                                    <input type="hidden" name="MAX_FILE_SIZE" value="10485760">

                                    <div x-show="quickTaskMode === 'edit'" x-cloak
                                        class="rounded-md border border-primary/25 bg-primary/[0.04] p-4">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <div class="text-[11px] font-semibold uppercase tracking-[0.2em] text-base-content/45">
                                                    Edit Tugas
                                                </div>
                                                <div class="mt-1 text-sm font-semibold text-base-content"
                                                    x-text="selectedTask ? selectedTask.title : ''"></div>
                                                <div class="mt-1 text-xs text-base-content/60">
                                                    Perubahan akan disimpan ke tugas yang sedang dipilih di papan fokus.
                                                </div>
                                            </div>

                                            <x-ui.button type="ghost" size="sm" :isSubmit="false"
                                                @click="cancelQuickTaskEdit()">
                                                <x-heroicon-o-x-mark class="h-4 w-4" />
                                                Batal edit
                                            </x-ui.button>
                                        </div>
                                    </div>

                                    <x-ui.input name="task_judul" label="Judul Tugas" placeholder="Contoh: Resume pertemuan 4"
                                        :value="old('task_judul')" :error="$errors->quickTask->first('task_judul')" :required="true"
                                        ::class="quickTaskErrors.task_judul ? 'input-error' : ''" />
                                    <div x-show="quickTaskErrors.task_judul" class="label" x-cloak>
                                        <span class="label-text-alt text-error" x-text="quickTaskErrors.task_judul"></span>
                                    </div>

                                    <x-ui.textarea name="task_deskripsi" label="Deskripsi" rows="3"
                                        placeholder="Tambahkan konteks singkat agar tugas mudah dipahami saat dibuka lagi."
                                        :value="old('task_deskripsi')" :error="$errors->quickTask->first('task_deskripsi')"
                                        ::class="quickTaskErrors.task_deskripsi ? 'textarea-error' : ''" />
                                    <div x-show="quickTaskErrors.task_deskripsi" class="label" x-cloak>
                                        <span class="label-text-alt text-error" x-text="quickTaskErrors.task_deskripsi"></span>
                                    </div>

                                    <div class="grid gap-4 sm:grid-cols-2">
                                        <x-ui.input name="task_deadline" type="date" label="Deadline"
                                            :value="old('task_deadline', now()->format('Y-m-d'))"
                                            :error="$errors->quickTask->first('task_deadline')"
                                            ::class="quickTaskErrors.task_deadline ? 'input-error' : ''" />
                                        <div x-show="quickTaskErrors.task_deadline" class="label sm:col-span-2" x-cloak>
                                            <span class="label-text-alt text-error" x-text="quickTaskErrors.task_deadline"></span>
                                        </div>

                                        <x-ui.select name="task_prioritas" label="Prioritas" :searchable="false"
                                            :options="['rendah' => 'Rendah', 'sedang' => 'Sedang', 'tinggi' => 'Tinggi']"
                                            :value="old('task_prioritas', 'sedang')"
                                            :error="$errors->quickTask->first('task_prioritas')"
                                            ::class="quickTaskErrors.task_prioritas ? 'select-error' : ''" />
                                        <div x-show="quickTaskErrors.task_prioritas" class="label sm:col-span-2" x-cloak>
                                            <span class="label-text-alt text-error" x-text="quickTaskErrors.task_prioritas"></span>
                                        </div>
                                    </div>

                                    <x-ui.textarea name="task_catatan" label="Catatan Internal" rows="3"
                                        placeholder="Contoh: fokus ke referensi jurnal terbaru atau cek rubrik penilaian."
                                        :value="old('task_catatan')" :error="$errors->quickTask->first('task_catatan')"
                                        ::class="quickTaskErrors.task_catatan ? 'textarea-error' : ''" />
                                    <div x-show="quickTaskErrors.task_catatan" class="label" x-cloak>
                                        <span class="label-text-alt text-error" x-text="quickTaskErrors.task_catatan"></span>
                                    </div>

                                    <x-ui.input name="task_file" label="Foto Tugas" type="file"
                                        accept=".pdf,image/*"
                                        helpText="Tambahkan foto referensi, screenshot LMS, atau file PDF pendukung."
                                        :error="$errors->quickTask->first('task_file')"
                                        ::class="quickTaskErrors.task_file ? 'file-input-error' : ''" />
                                    <div x-show="quickTaskErrors.task_file" class="label" x-cloak>
                                        <span class="label-text-alt text-error" x-text="quickTaskErrors.task_file"></span>
                                    </div>

                                    <div x-show="quickTaskMode === 'edit' && selectedTask?.attachment_url"
                                        class="rounded-md border border-base-300/70 bg-base-100 p-4" x-cloak>
                                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <div class="text-[11px] uppercase tracking-[0.18em] text-base-content/45">
                                                    Lampiran Saat Ini
                                                </div>
                                                <div class="mt-2 text-sm font-semibold text-base-content"
                                                    x-text="selectedTask?.attachment_name || 'Lampiran tugas'"></div>
                                                <p class="mt-1 text-xs text-base-content/60">
                                                    Biarkan kosong jika foto/lampiran tidak ingin diganti.
                                                </p>
                                            </div>

                                            <x-ui.button type="ghost" size="sm" :isSubmit="false"
                                                @click="goTo(selectedTask?.attachment_url, true)">
                                                <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                                                Buka
                                            </x-ui.button>
                                        </div>

                                        <div x-show="selectedTask?.attachment_is_image"
                                            class="mt-4 overflow-hidden rounded-md border border-base-300/70 bg-base-200/40"
                                            x-cloak>
                                            <img :src="selectedTask?.attachment_url"
                                                :alt="'Preview lampiran ' + (selectedTask?.attachment_name || selectedTask?.title || 'tugas')"
                                                class="h-48 w-full object-contain object-center">
                                        </div>
                                    </div>

                                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <x-ui.button type="ghost" size="sm" :isSubmit="false"
                                            @click="setQuickWizardStep(2)">
                                            <span x-text="quickTaskMode === 'edit' ? 'Ke checklist' : 'Lewati ke checklist'"></span>
                                            <x-heroicon-o-arrow-right class="h-4 w-4" />
                                        </x-ui.button>

                                        <x-ui.button type="primary" size="sm"
                                            x-bind:disabled="quickTaskSubmitting"
                                            ::class="quickTaskSubmitting ? 'loading' : ''">
                                            <x-heroicon-o-plus class="h-4 w-4" x-show="quickTaskMode !== 'edit'" x-cloak />
                                            <x-heroicon-o-check class="h-4 w-4" x-show="quickTaskMode === 'edit'" x-cloak />
                                            <span x-text="quickTaskMode === 'edit' ? 'Simpan Perubahan' : 'Simpan Tugas'"></span>
                                        </x-ui.button>
                                    </div>

                                    <p x-show="quickTaskMode !== 'edit'" class="text-xs text-base-content/55" x-cloak>
                                        Setelah tugas tersimpan, wizard akan otomatis membuka langkah checklist.
                                    </p>
                                    <p x-show="quickTaskMode === 'edit'" class="text-xs text-base-content/55" x-cloak>
                                        Setelah perubahan tersimpan, lanjutkan ke langkah checklist jika ingin menambah item kerja.
                                    </p>
                                </form>

                                <div x-show="quickWizardStep === 2 && tasks.length === 0"
                                    class="rounded-md border border-dashed border-base-300 p-5 text-sm text-base-content/60"
                                    x-cloak>
                                    Tambahkan tugas terlebih dahulu sebelum membuat checklist.
                                    <div class="mt-3">
                                        <x-ui.button type="ghost" size="sm" :isSubmit="false"
                                            @click="setQuickWizardStep(1)">
                                            <x-heroicon-o-arrow-left class="h-4 w-4" />
                                            Buat tugas dulu
                                        </x-ui.button>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('mata-kuliah.focus-todo', $mataKuliah) }}"
                                    enctype="multipart/form-data"
                                    class="space-y-4" x-show="quickWizardStep === 2 && tasks.length > 0" x-cloak
                                    x-ref="quickTodoForm"
                                    @submit.prevent="submitQuickTodo($event)">
                                    @csrf
                                    <input type="hidden" name="MAX_FILE_SIZE" value="10485760">

                                    <x-ui.input type="hidden" name="tugas_id" x-model="selectedTaskId" />

                                    <div x-show="quickTodoMode === 'edit'" x-cloak
                                        class="rounded-md border border-primary/25 bg-primary/[0.04] p-4">
                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <div class="text-[11px] font-semibold uppercase tracking-[0.2em] text-base-content/45">
                                                    Edit Checklist
                                                </div>
                                                <div class="mt-1 text-sm font-semibold text-base-content"
                                                    x-text="editingTodo ? editingTodo.title : ''"></div>
                                                <div class="mt-1 text-xs text-base-content/60">
                                                    Perubahan akan disimpan ke checklist terpilih.
                                                </div>
                                            </div>

                                            <x-ui.button type="ghost" size="sm" :isSubmit="false"
                                                @click="cancelQuickTodoEdit()">
                                                <x-heroicon-o-x-mark class="h-4 w-4" />
                                                Batal edit
                                            </x-ui.button>
                                        </div>
                                    </div>

                                    <div class="rounded-md border border-base-300/70 bg-base-100 p-4"
                                        :class="selectedTask ? 'border-base-300/70 bg-base-100' : 'border-warning/40 bg-warning/5'">
                                        <div class="text-[11px] uppercase tracking-[0.2em] text-base-content/45">Tugas Aktif</div>
                                        <div class="mt-2 text-sm font-semibold text-base-content sm:text-base"
                                            x-text="selectedTask ? selectedTask.title : 'Pilih tugas terlebih dahulu'"></div>
                                        <div class="mt-1 text-xs text-base-content/60 sm:text-sm"
                                            x-text="selectedTask ? selectedTask.deadline_label + ' • ' + selectedTask.deadline_relative : 'Checklist akan aktif setelah tugas dipilih.'">
                                        </div>
                                    </div>

                                    <x-ui.input name="todo_judul" label="Judul Checklist"
                                        placeholder="Contoh: Cari 3 referensi jurnal" :value="old('todo_judul')"
                                        :error="$errors->quickTodo->first('todo_judul')" :required="true"
                                        ::class="quickTodoErrors.todo_judul ? 'input-error' : ''" />
                                    <div x-show="quickTodoErrors.todo_judul" class="label" x-cloak>
                                        <span class="label-text-alt text-error" x-text="quickTodoErrors.todo_judul"></span>
                                    </div>

                                    <x-ui.textarea name="todo_deskripsi" label="Deskripsi" rows="3"
                                        placeholder="Opsional, gunakan jika perlu penjelasan tambahan."
                                        :value="old('todo_deskripsi')" :error="$errors->quickTodo->first('todo_deskripsi')"
                                        ::class="quickTodoErrors.todo_deskripsi ? 'textarea-error' : ''" />
                                    <div x-show="quickTodoErrors.todo_deskripsi" class="label" x-cloak>
                                        <span class="label-text-alt text-error" x-text="quickTodoErrors.todo_deskripsi"></span>
                                    </div>

                                    <x-ui.input name="todo_deadline" type="date" label="Deadline Checklist"
                                        :value="old('todo_deadline')" :error="$errors->quickTodo->first('todo_deadline')"
                                        ::class="quickTodoErrors.todo_deadline ? 'input-error' : ''" />
                                    <div x-show="quickTodoErrors.todo_deadline" class="label" x-cloak>
                                        <span class="label-text-alt text-error" x-text="quickTodoErrors.todo_deadline"></span>
                                    </div>

                                    <x-ui.input name="todo_file" label="Foto Checklist" type="file"
                                        accept="image/*"
                                        helpText="Tambahkan foto bukti atau referensi visual untuk checklist ini."
                                        :error="$errors->quickTodo->first('todo_file')"
                                        ::class="quickTodoErrors.todo_file ? 'file-input-error' : ''" />
                                    <div x-show="quickTodoErrors.todo_file" class="label" x-cloak>
                                        <span class="label-text-alt text-error" x-text="quickTodoErrors.todo_file"></span>
                                    </div>

                                    <div x-show="quickTodoMode === 'edit' && editingTodo?.attachment_url"
                                        class="rounded-md border border-base-300/70 bg-base-100 p-4" x-cloak>
                                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                            <div>
                                                <div class="text-[11px] uppercase tracking-[0.18em] text-base-content/45">
                                                    Foto Saat Ini
                                                </div>
                                                <div class="mt-2 text-sm font-semibold text-base-content"
                                                    x-text="editingTodo?.attachment_name || 'Foto checklist'"></div>
                                                <p class="mt-1 text-xs text-base-content/60">
                                                    Biarkan kosong jika foto checklist tidak ingin diganti.
                                                </p>
                                            </div>

                                            <x-ui.button type="ghost" size="sm" :isSubmit="false"
                                                @click="goTo(editingTodo?.attachment_url, true)">
                                                <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                                                Buka
                                            </x-ui.button>
                                        </div>

                                        <div x-show="editingTodo?.attachment_is_image"
                                            class="mt-4 overflow-hidden rounded-md border border-base-300/70 bg-base-200/40"
                                            x-cloak>
                                            <img :src="editingTodo?.attachment_url"
                                                :alt="'Preview foto checklist ' + (editingTodo?.attachment_name || editingTodo?.title || 'item')"
                                                class="h-48 w-full object-contain object-center">
                                        </div>
                                    </div>

                                    <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-between">
                                        <x-ui.button type="ghost" size="sm" :isSubmit="false"
                                            @click="setQuickWizardStep(1)">
                                            <x-heroicon-o-arrow-left class="h-4 w-4" />
                                            Kembali
                                        </x-ui.button>

                                        <x-ui.button type="primary" size="sm"
                                            x-bind:disabled="!selectedTaskId || quickTodoSubmitting"
                                            ::class="quickTodoSubmitting ? 'loading' : ''">
                                            <x-heroicon-o-plus class="h-4 w-4" x-show="quickTodoMode !== 'edit'" x-cloak />
                                            <x-heroicon-o-check class="h-4 w-4" x-show="quickTodoMode === 'edit'" x-cloak />
                                            <span x-text="quickTodoMode === 'edit' ? 'Simpan Perubahan' : 'Tambah Checklist'"></span>
                                        </x-ui.button>
                                    </div>
                                </form>
                            </div>
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
                                    <x-ui.checkbox :bare="true" class="checkbox-secondary mt-0.5"
                                        x-bind:checked="item.done"
                                        x-bind:aria-label="'Tandai item ' + item.title"
                                        @change="toggleQuickItem(item.id)" />

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

                                    <x-ui.button type="ghost" size="xs" :isSubmit="false" class="text-error"
                                        @click="removeQuickItem(item.id)">
                                        <x-heroicon-o-trash class="h-4 w-4" />
                                    </x-ui.button>
                                </div>
                            </template>
                        </div>
                    </div>
                </x-slot:panels>
            </x-ui.tabs>
        </div>
    </x-ui.card>
</div>
