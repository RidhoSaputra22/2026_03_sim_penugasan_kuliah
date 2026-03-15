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
                        <div id="quick-task">
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

                                <x-ui.input type="hidden" name="tugas_id" x-model="selectedTaskId" />

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
                                    <x-ui.button type="primary" size="sm" x-bind:disabled="!selectedTaskId">
                                        <x-heroicon-o-plus class="h-4 w-4" />
                                        Tambah Checklist
                                    </x-ui.button>
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
