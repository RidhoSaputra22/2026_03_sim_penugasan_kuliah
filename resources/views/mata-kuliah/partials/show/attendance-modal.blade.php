<x-ui.modal id="attendance-focus-modal" size="2xl" :closeButton="true" :centered="false">
    <x-slot:titleSlot>
        <div>
            <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">Workspace Absensi</div>
            <h3 class="mt-1 text-lg font-semibold sm:text-xl">Kelola Absensi {{ $mataKuliah->nama }}</h3>
        </div>
    </x-slot:titleSlot>

    <div class="grid gap-6 lg:grid-cols-[24rem_minmax(0,1fr)]">
        <div class="space-y-5">
            <div x-effect="dispatchAttendanceCalendarSelection(attendanceForm.tanggal, false)" class="hidden"></div>

            <form method="POST" action="{{ route('mata-kuliah.focus-attendance.save', $mataKuliah) }}"
                class="space-y-4 rounded-md border border-base-300/70 bg-base-100 p-4"
                @submit.prevent="submitAttendanceForm()">
                @csrf

                <input type="hidden" name="absensi_id" x-model="attendanceForm.absensi_id">

                <div x-show="attendanceNotice" x-cloak class="alert"
                    :class="attendanceNotice?.type === 'error' ? 'alert-error' : 'alert-success'">
                    <svg x-show="attendanceNotice?.type === 'error'" xmlns="http://www.w3.org/2000/svg"
                        class="stroke-current h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg x-show="attendanceNotice?.type !== 'error'" xmlns="http://www.w3.org/2000/svg"
                        class="stroke-current h-6 w-6 shrink-0" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-text="attendanceNotice?.message"></span>
                </div>

                @if ($errors->attendanceManager->any())
                    <x-ui.alert type="error" x-show="!attendanceNotice">
                        {{ $errors->attendanceManager->first() }}
                    </x-ui.alert>
                @endif

                <div class="rounded-md border border-base-300/70 bg-base-200/60 p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">Mode Form</div>
                            <div class="mt-1 text-sm font-semibold text-base-content sm:text-base"
                                x-text="attendanceForm.absensi_id ? 'Edit absensi terpilih' : 'Tambah absensi baru'"></div>
                        </div>

                        <span class="badge badge-outline badge-sm"
                            :class="attendanceStatusClass(attendanceForm.status)"
                            x-text="attendanceForm.status || 'Status'"></span>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-ui.input name="tanggal" type="date" label="Tanggal Pertemuan"
                            x-model="attendanceForm.tanggal" :error="$errors->attendanceManager->first('tanggal')" readonly disabled />
                        <p x-show="attendanceError('tanggal')" class="mt-1 text-xs text-error" x-text="attendanceError('tanggal')"></p>
                    </div>
                    <div>
                        <x-ui.input name="pertemuan_ke" type="number" min="1" max="32" label="Pertemuan Ke"
                            placeholder="Contoh: 6" x-model="attendanceForm.pertemuan_ke"
                            :error="$errors->attendanceManager->first('pertemuan_ke')" />
                        <p x-show="attendanceError('pertemuan_ke')" class="mt-1 text-xs text-error" x-text="attendanceError('pertemuan_ke')"></p>
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-ui.select name="status" label="Status Kehadiran" :searchable="false"
                            :options="$attendanceStatusOptions"
                            x-model="attendanceForm.status" :error="$errors->attendanceManager->first('status')" />
                        <p x-show="attendanceError('status')" class="mt-1 text-xs text-error" x-text="attendanceError('status')"></p>
                    </div>
                    <div>
                        <x-ui.input name="topik" label="Topik Pertemuan" placeholder="Contoh: Pengantar probabilitas"
                            x-model="attendanceForm.topik" :error="$errors->attendanceManager->first('topik')" />
                        <p x-show="attendanceError('topik')" class="mt-1 text-xs text-error" x-text="attendanceError('topik')"></p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-end gap-2">


                    <div class="flex gap-2">
                        <x-ui.button type="ghost" size="sm" :isSubmit="false" @click="closeDialog('attendance-focus-modal')">
                            Tutup
                        </x-ui.button>
                        <x-ui.button type="primary" size="sm" x-bind:disabled="attendanceSubmitting">
                            <span x-show="attendanceSubmitting" class="loading loading-spinner loading-xs"></span>
                            <x-heroicon-o-check class="h-4 w-4" />
                            <span x-text="attendanceForm.absensi_id ? 'Update Absensi' : 'Simpan Absensi'"></span>
                        </x-ui.button>
                    </div>
                </div>

                <div x-show="selectedAttendance && selectedAttendance.delete_url" x-cloak
                    class="border-t border-base-300/70 pt-4">
                    <button type="button" class="btn btn-error btn-sm btn-outline w-full"
                        :disabled="attendanceDeleting" @click="deleteSelectedAttendance()">
                        <span x-show="attendanceDeleting" class="loading loading-spinner loading-xs"></span>
                        <x-heroicon-o-trash class="h-4 w-4" />
                        Hapus Absensi Terpilih
                    </button>
                </div>
            </form>

            <div class="rounded-md border border-dashed border-base-300/80 bg-base-100 p-4">
                <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">Pilih Dari Kalender</div>
                <p class="mt-2 text-sm text-base-content/75">
                    Klik tanggal kosong untuk menyiapkan absensi baru, atau klik label absensi untuk memuat data edit.
                </p>
                <p class="mt-2 text-xs text-base-content/55">
                    <span x-text="attendances.length"></span> pertemuan tersimpan untuk mata kuliah ini.
                </p>
            </div>
        </div>

        <div class="space-y-4">
            <x-ui.callendar :events="$attendanceCalendarEvents"
                mode="slim"
                :showScheduleLegend="false"
                :showDeadlineLegend="false"
                customLegendLabel="Absensi"
                :allowEventCrud="false"
                :interactive="false"
                syncEventName="attendance-calendar-sync"
                eventClickName="attendance-calendar-select"
                dateClickName="attendance-calendar-date-select"
                selectionSyncEvent="attendance-calendar-selection-sync" />
        </div>
    </div>
</x-ui.modal>
