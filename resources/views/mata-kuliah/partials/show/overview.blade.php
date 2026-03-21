@php
    $hariLabel = $mataKuliah->hari instanceof \App\Enums\DayOfWeek
        ? $mataKuliah->hari->label()
        : $mataKuliah->hari;
@endphp

<x-ui.card class="relative overflow-visible border border-base-300/70 bg-base-100">
    <div class="absolute -left-12 top-0 h-40 w-40 rounded-md bg-primary/10 blur-3xl"></div>
    <div class="absolute -right-12 bottom-0 h-48 w-48 rounded-md bg-warning/10 blur-3xl"></div>

    <div class="relative grid gap-6 xl:grid-cols-[1.6fr_1fr]">
        <div class="space-y-5">
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge type="primary">{{ $mataKuliah->kode }}</x-ui.badge>

                @if ($mataKuliah->kelas)
                    <x-ui.badge type="secondary">Kelas {{ $mataKuliah->kelas }}</x-ui.badge>
                @endif

                @if ($mataKuliah->semester)
                    <x-ui.badge type="accent">Semester {{ $mataKuliah->semester }}</x-ui.badge>
                @endif

                @if ($mataKuliah->tahun_ajaran)
                    <x-ui.badge type="info">TA {{ $mataKuliah->tahun_ajaran }}</x-ui.badge>
                @endif

                <x-ui.badge :type="$mataKuliah->is_active ? 'success' : 'warning'">
                    {{ $mataKuliah->is_active ? 'Aktif' : 'Tidak Aktif' }}
                </x-ui.badge>
            </div>

            <div>
                <h1 class="text-xl font-black tracking-tight text-base-content sm:text-2xl md:text-3xl">
                    {{ $mataKuliah->nama }}
                </h1>
                <p class="mt-2 max-w-2xl text-xs leading-5 text-base-content/65 sm:text-sm sm:leading-6">
                    Workspace fokus ini dirapikan agar kamu bisa memilih tugas, mengejar deadline,
                    dan menjaga progres belajar dari satu halaman kerja yang sama.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                <div class="rounded-md border border-base-300/70 bg-base-100/75 p-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">Jadwal</div>
                    <div class="mt-2 text-base font-semibold sm:text-lg">{{ $hariLabel }}</div>
                    <div class="text-xs text-base-content/60 sm:text-sm">
                        {{ \Carbon\Carbon::parse($mataKuliah->jam_mulai)->format('H:i') }} -
                        {{ \Carbon\Carbon::parse($mataKuliah->jam_selesai)->format('H:i') }}
                    </div>
                </div>

                <div class="rounded-md border border-base-300/70 bg-base-100/75 p-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">Durasi</div>
                    <div class="mt-2 text-base font-semibold sm:text-lg">{{ $durasiKuliah }}</div>
                    <div class="text-xs text-base-content/60 sm:text-sm">{{ $mataKuliah->sks ?? '-' }} SKS</div>
                </div>

                <div class="rounded-md border border-base-300/70 bg-base-100/75 p-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">Ruangan</div>
                    <div class="mt-2 text-base font-semibold sm:text-lg">{{ $mataKuliah->ruangan }}</div>
                    <div class="text-xs text-base-content/60 sm:text-sm">Lokasi tatap muka</div>
                </div>

                <div class="rounded-md border border-base-300/70 bg-base-100/75 p-4">
                    <div class="text-xs uppercase tracking-[0.2em] text-base-content/45">Dosen</div>
                    <div class="mt-2 text-base font-semibold sm:text-lg">{{ $mataKuliah->dosen }}</div>
                    <div class="text-xs text-base-content/60 sm:text-sm">{{ $mataKuliah->lms ?: 'Belum ada LMS' }}</div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-md border border-base-300/70 bg-base-100/85 p-5">
                <div class="text-xs font-semibold uppercase tracking-[0.22em] text-base-content/45">
                    Fokus Berikutnya
                </div>

                @if ($nextDeadlineTask)
                    <div class="mt-3 rounded-md border border-warning/30 bg-warning/10 p-4">
                        <div class="text-xs font-semibold text-base-content sm:text-sm">
                            {{ $nextDeadlineTask['title'] }}
                        </div>
                        <div class="mt-1 text-xs text-base-content/65 sm:text-sm">
                            Deadline {{ $nextDeadlineTask['deadline_label'] }}
                        </div>
                        <div class="mt-2 inline-flex rounded-md bg-base-100 px-3 py-1 text-xs font-medium">
                            {{ $nextDeadlineTask['deadline_relative'] }}
                        </div>
                    </div>
                @else
                    <div class="mt-3 rounded-md border border-success/30 bg-success/10 p-4">
                        <div class="text-xs font-semibold text-base-content sm:text-sm">Semua tugas utama aman</div>
                        <div class="mt-1 text-xs text-base-content/65 sm:text-sm">
                            Belum ada deadline aktif yang perlu dikejar.
                        </div>
                    </div>
                @endif

                <div class="mt-4 space-y-3">
                    <div class="flex items-center justify-between rounded-md bg-base-200/70 px-4 py-3">
                        <div>
                            <div class="text-xs uppercase tracking-wide text-base-content/45">Tugas Aktif</div>
                            <div class="font-medium text-base-content">{{ $tugasAktif }} dari {{ $totalTugas }} tugas</div>
                        </div>
                        <div class="text-right text-xs text-base-content/65 sm:text-sm">
                            {{ $tugasSelesai }} selesai
                        </div>
                    </div>

                    <div class="flex items-center justify-between rounded-md bg-base-200/70 px-4 py-3">
                        <div>
                            <div class="text-xs uppercase tracking-wide text-base-content/45">Checklist</div>
                            <div class="font-medium text-base-content">{{ $todoSelesai }}/{{ $totalTodo }} item selesai</div>
                        </div>
                        <div class="text-right text-xs text-base-content/65 sm:text-sm">
                            {{ $rataRataProgress }}% progres rata-rata
                        </div>
                    </div>

                    <div class="hidden grid-cols-2 gap-2 sm:grid">
                        <x-ui.button type="ghost" size="sm" :isSubmit="false" class="flex-1"
                            @click="activateWorkspaceTab('action', 'task')">
                            <x-heroicon-o-plus class="h-4 w-4" />
                            Tambah Tugas
                        </x-ui.button>
                        <x-ui.button type="ghost" size="sm" :isSubmit="false" class="flex-1"
                            @click="activateWorkspaceTab('action', 'todo')">
                            <x-heroicon-o-list-bullet class="h-4 w-4" />
                            Tambah Checklist
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-ui.card>

<div class="hidden grid-cols-2 gap-4 sm:grid md:grid-cols-3 xl:grid-cols-5">
    <x-ui.stat title="Total Tugas" :value="$totalTugas" description="semua tugas mata kuliah">
        <x-slot:icon>
            <x-heroicon-o-rectangle-stack class="h-8 w-8 text-primary" />
        </x-slot:icon>
    </x-ui.stat>

    <x-ui.stat title="Tugas Aktif" :value="$tugasAktif" description="masih perlu dikerjakan">
        <x-slot:icon>
            <x-heroicon-o-bolt class="h-8 w-8 text-warning" />
        </x-slot:icon>
    </x-ui.stat>

    <x-ui.stat title="Selesai" :value="$tugasSelesai" description="{{ $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100) : 0 }}% dari total tugas">
        <x-slot:icon>
            <x-heroicon-o-check-circle class="h-8 w-8 text-success" />
        </x-slot:icon>
    </x-ui.stat>

    <x-ui.stat title="Checklist" :value="$todoSelesai . '/' . $totalTodo" description="item checklist selesai">
        <x-slot:icon>
            <x-heroicon-o-list-bullet class="h-8 w-8 text-secondary" />
        </x-slot:icon>
    </x-ui.stat>

    <x-ui.stat title="Deadline Dekat" :value="$tugasMendekat" description="3 hari ke depan">
        <x-slot:icon>
            <x-heroicon-o-calendar-days class="h-8 w-8 text-info" />
        </x-slot:icon>
    </x-ui.stat>
</div>
