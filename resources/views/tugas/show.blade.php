@php
    use App\Enums\Status;

    $selesai = Status::SELESAI->value;
    $belum = Status::BELUM->value;
    $progress = Status::PROGRESS->value;
    $statusOptions = Status::options();
    $tugasStatusValue = $tugas->status instanceof Status ? $tugas->status->value : (string) $tugas->status;
    $tugasStatusLabel = $tugas->status instanceof Status
        ? $tugas->status->label()
        : (Status::tryFrom((string) $tugas->status)?->label() ?? (string) $tugas->status);
    $priorityValue = $tugas->prioritas ?? 'sedang';
    $priorityLabel = match ($priorityValue) {
        'tinggi' => 'Prioritas Tinggi',
        'sedang' => 'Prioritas Sedang',
        'rendah' => 'Prioritas Rendah',
        default => ucfirst((string) $priorityValue),
    };
    $priorityBadge = match ($priorityValue) {
        'tinggi' => 'error',
        'sedang' => 'warning',
        'rendah' => 'info',
        default => 'ghost',
    };
    $deadline = \Carbon\Carbon::parse($tugas->deadline);
    $today = now()->startOfDay();
    $daysLeft = (int) $today->diffInDays($deadline->copy()->startOfDay(), false);
    $isOverdue = $daysLeft < 0 && $tugasStatusValue !== $selesai;
    $deadlineLabel = $deadline->translatedFormat('d F Y');
    $deadlineRelativeLabel = match (true) {
        $tugasStatusValue === $selesai => 'Tugas sudah selesai',
        $isOverdue => 'Terlambat ' . abs($daysLeft) . ' hari',
        $daysLeft === 0 => 'Deadline hari ini',
        $daysLeft === 1 => 'Deadline besok',
        $daysLeft > 1 => $daysLeft . ' hari lagi',
        default => 'Perlu ditinjau ulang',
    };
    $deadlineBadgeType = $tugasStatusValue === $selesai
        ? 'success'
        : ($isOverdue ? 'error' : ($daysLeft <= 3 ? 'warning' : 'info'));
    $deadlineSurfaceClass = $tugasStatusValue === $selesai
        ? 'border-success/20 bg-success/10'
        : ($isOverdue ? 'border-error/20 bg-error/10' : ($daysLeft <= 3 ? 'border-warning/20 bg-warning/10' : 'border-info/20 bg-info/10'));
    $deadlineTextClass = $tugasStatusValue === $selesai
        ? 'text-success'
        : ($isOverdue ? 'text-error' : ($daysLeft <= 3 ? 'text-warning' : 'text-base-content/70'));
    $totalTodos = $tugas->todos?->count() ?? 0;
    $completedTodos = $tugas->todos
        ? $tugas->todos
            ->filter(function ($todo) use ($selesai) {
                $todoStatusValue = $todo->status instanceof Status ? $todo->status->value : (string) $todo->status;

                return $todoStatusValue === $selesai;
            })
            ->count()
        : 0;
    $remainingTodos = max($totalTodos - $completedTodos, 0);
    $todoSummary = $totalTodos > 0
        ? $completedTodos . ' dari ' . $totalTodos . ' todo selesai'
        : 'Belum ada todo checklist untuk tugas ini.';
    $fileUrl = $tugas->attachmentUrl();
    $fileName = $tugas->attachmentName();
    $isImageAttachment = $tugas->attachmentIsImage();
@endphp

<x-layouts.app title="Detail Tugas">
    <x-slot:header>
        <x-layouts.page-header title="Detail Tugas" description="{{ $tugas->judul }}">
            <x-slot:actions>
                <x-ui.button type="ghost" size="sm" :href="route('tugas.index')">
                    <x-heroicon-o-arrow-left class="h-4 w-4" />
                    Kembali
                </x-ui.button>
                <x-ui.button type="primary" size="sm" :href="route('tugas.edit', $tugas->id)">
                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                    Edit
                </x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    <div x-data="{
        progress: {{ (int) $tugas->progress }},
        status: '{{ $tugasStatusValue }}',
        completedTodos: {{ $completedTodos }},
        totalTodos: {{ $totalTodos }},
        labels: @js($statusOptions),
        remainingTodos() {
            return Math.max(this.totalTodos - this.completedTodos, 0);
        },
        checklistSummary() {
            if (this.totalTodos <= 0) {
                return 'Belum ada todo checklist untuk tugas ini.';
            }

            return this.completedTodos + ' dari ' + this.totalTodos + ' todo selesai';
        },
        init() {
            window.addEventListener('todo-progress-updated', (event) => {
                this.progress = Number(event.detail.progress ?? 0);
                this.status = event.detail.tugas_status || this.status;

                if (!event.detail.hasOwnProperty('old_status') || !event.detail.hasOwnProperty('todo_status')) {
                    return;
                }

                if (event.detail.old_status === event.detail.todo_status) {
                    return;
                }

                if (event.detail.old_status === '{{ $selesai }}' && this.completedTodos > 0) {
                    this.completedTodos -= 1;
                }

                if (event.detail.todo_status === '{{ $selesai }}') {
                    this.completedTodos += 1;
                }
            });
        }
    }" class="space-y-6">
        <section class="relative overflow-visible rounded-md border border-base-200/80 bg-gradient-to-br from-primary/10 via-base-100 to-info/10 shadow-xl">
            <div class="absolute -top-16 right-8 h-40 w-40 rounded-full bg-primary/15 blur-3xl"></div>
            <div class="absolute -bottom-20 left-10 h-44 w-44 rounded-full bg-info/15 blur-3xl"></div>

            <div class="relative grid gap-8 p-6 sm:p-8 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="badge badge-lg" :class="{
                            'badge-success': status === '{{ $selesai }}',
                            'badge-warning': status === '{{ $progress }}',
                            'badge-error': status === '{{ $belum }}',
                            'badge-ghost': !['{{ $selesai }}', '{{ $progress }}', '{{ $belum }}'].includes(status)
                        }" x-text="labels[status] || status">{{ $tugasStatusLabel }}</span>
                        <x-ui.badge :type="$priorityBadge" size="sm">{{ $priorityLabel }}</x-ui.badge>
                        <x-ui.badge :type="$deadlineBadgeType" size="sm" outline>{{ $deadlineRelativeLabel }}</x-ui.badge>
                    </div>

                    <div class="space-y-3">
                        <h1 class="text-3xl font-black tracking-tight text-base-content sm:text-4xl">
                            {{ $tugas->judul }}
                        </h1>
                        <p class="max-w-3xl text-sm leading-6 text-base-content/70 sm:text-base">
                            {{ $tugas->deskripsi
                                ? \Illuminate\Support\Str::limit($tugas->deskripsi, 180)
                                : 'Tugas ini belum memiliki deskripsi. Gunakan checklist dan catatan untuk memecah pekerjaan menjadi langkah yang jelas.' }}
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        <div class="rounded-md border border-base-200/80 bg-base-100/80 p-4 backdrop-blur">
                            <div class="flex items-start gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-primary/10 text-primary">
                                    <x-heroicon-o-calendar-days class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-widest text-base-content/45">Deadline</p>
                                    <div class="mt-1 text-base font-semibold {{ $isOverdue ? 'text-error' : 'text-base-content' }}">
                                        {{ $deadline->format('d M Y') }}
                                    </div>
                                    <p class="text-xs {{ $deadlineTextClass }}">{{ $deadlineRelativeLabel }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-md border border-base-200/80 bg-base-100/80 p-4 backdrop-blur">
                            <div class="flex items-start gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-success/10 text-success">
                                    <x-heroicon-o-check-badge class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-widest text-base-content/45">Checklist</p>
                                    <div class="mt-1 text-base font-semibold text-base-content" x-text="completedTodos + '/' + totalTodos">
                                        {{ $completedTodos }}/{{ $totalTodos }}
                                    </div>
                                    <p class="text-xs text-base-content/60" x-text="totalTodos > 0 ? remainingTodos() + ' todo masih tersisa' : 'Belum ada checklist'">
                                        {{ $totalTodos > 0 ? $remainingTodos . ' todo masih tersisa' : 'Belum ada checklist' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-md border border-base-200/80 bg-base-100/80 p-4 backdrop-blur sm:col-span-2 xl:col-span-1">
                            <div class="flex items-start gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-info/10 text-info">
                                    <x-heroicon-o-academic-cap class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-widest text-base-content/45">Mata Kuliah</p>
                                    <div class="mt-1 text-base font-semibold text-base-content">
                                        {{ $tugas->mataKuliah->nama ?? '-' }}
                                    </div>
                                    <p class="text-xs text-base-content/60">
                                        {{ $tugas->mataKuliah->dosen ?? 'Dosen belum tersedia' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-md border border-base-200/80 bg-base-100/70 p-4">
                        <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                            <span class="font-medium text-base-content/70">Progress otomatis dari todo list</span>
                            <span class="font-bold text-primary" x-text="progress + '%'">{{ (int) $tugas->progress }}%</span>
                        </div>
                        <progress class="progress h-3 w-full transition-all"
                            :class="{
                                'progress-success': progress >= 100,
                                'progress-warning': progress >= 40 && progress < 100,
                                'progress-error': progress < 40
                            }"
                            value="{{ (int) $tugas->progress }}"
                            :value="progress"
                            max="100"></progress>
                    </div>
                </div>

                <div class="flex items-center justify-center lg:justify-end">
                    <div class="w-full max-w-xs rounded-md border border-base-100/70 bg-base-100/75 p-6 text-center shadow-inner backdrop-blur">
                        <div class="radial-progress text-primary text-3xl font-black"
                            :style="'--value:' + progress + '; --size:10rem; --thickness:0.85rem;'"
                            role="progressbar">
                            <span x-text="progress + '%'">{{ (int) $tugas->progress }}%</span>
                        </div>

                        <div class="mt-5">
                            <p class="text-sm font-semibold text-base-content">Status pengerjaan</p>
                            <span class="badge badge-lg mt-3" :class="{
                                'badge-success': status === '{{ $selesai }}',
                                'badge-warning': status === '{{ $progress }}',
                                'badge-error': status === '{{ $belum }}',
                                'badge-ghost': !['{{ $selesai }}', '{{ $progress }}', '{{ $belum }}'].includes(status)
                            }" x-text="labels[status] || status">{{ $tugasStatusLabel }}</span>
                        </div>

                        <div class="mt-4 text-xs leading-6 text-base-content/60">
                            <template x-if="status === '{{ $selesai }}'">
                                <span>Semua checklist selesai. Tugas siap ditutup.</span>
                            </template>
                            <template x-if="status === '{{ $progress }}'">
                                <span>Pekerjaan sedang berjalan. Pertahankan ritmenya.</span>
                            </template>
                            <template x-if="status === '{{ $belum }}'">
                                <span>Mulai dari checklist kecil untuk menaikkan progress dengan cepat.</span>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <div class="space-y-6 xl:col-span-2">
                <x-ui.card class="border border-base-200/70">
                    <div class="space-y-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-base-content/45">Rangkuman Konten</p>
                                <h3 class="mt-1 text-xl font-bold text-base-content">Deskripsi, Catatan, dan Lampiran</h3>
                                <p class="mt-1 text-sm text-base-content/60">Semua konteks utama tugas dikumpulkan dalam satu tempat.</p>
                            </div>

                            @if ($fileUrl)
                                <x-ui.button type="ghost" size="sm" :href="$fileUrl" target="_blank" rel="noreferrer" :isSubmit="false">
                                    <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                                    Unduh Lampiran
                                </x-ui.button>
                            @endif
                        </div>

                        <div class="grid gap-4 lg:grid-cols-2">
                            <section class="rounded-md bg-base-200/45 p-5">
                                <div class="mb-3 flex items-center gap-2">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                        <x-heroicon-o-document-text class="h-4 w-4" />
                                    </div>
                                    <h4 class="text-sm font-semibold uppercase tracking-widest text-base-content/55">Deskripsi</h4>
                                </div>

                                @if ($tugas->deskripsi)
                                    <p class="whitespace-pre-line text-sm leading-7 text-base-content/75">
                                        {{ $tugas->deskripsi }}
                                    </p>
                                @else
                                    <p class="text-sm italic text-base-content/50">Belum ada deskripsi untuk tugas ini.</p>
                                @endif
                            </section>

                            <section class="rounded-md bg-base-200/45 p-5">
                                <div class="mb-3 flex items-center gap-2">
                                    <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-warning/10 text-warning">
                                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                                    </div>
                                    <h4 class="text-sm font-semibold uppercase tracking-widest text-base-content/55">Catatan</h4>
                                </div>

                                @if ($tugas->catatan)
                                    <p class="whitespace-pre-line text-sm leading-7 text-base-content/75">
                                        {{ $tugas->catatan }}
                                    </p>
                                @else
                                    <p class="text-sm italic text-base-content/50">Belum ada catatan tambahan.</p>
                                @endif
                            </section>
                        </div>

                        <div class="rounded-md border border-dashed border-base-300/80 bg-base-100 p-4">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex items-start gap-3">
                                    <div class="flex h-11 w-11 items-center justify-center rounded-md bg-info/10 text-info">
                                        <x-heroicon-o-paper-clip class="h-5 w-5" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-base-content">Lampiran tugas</p>
                                        <p class="mt-1 text-xs text-base-content/60">
                                            {{ $fileName ? $fileName : 'Belum ada file yang dilampirkan pada tugas ini.' }}
                                        </p>
                                    </div>
                                </div>

                                @if ($fileUrl)
                                    <x-ui.button type="info" size="sm" :href="$fileUrl" target="_blank" rel="noreferrer" :isSubmit="false">
                                        <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                                        Buka File
                                    </x-ui.button>
                                @endif
                            </div>

                            @if ($fileUrl && $isImageAttachment)
                                <div class="mt-4 overflow-hidden rounded-md border border-base-300/70 bg-base-200/40">
                                    <img src="{{ $fileUrl }}" alt="Preview lampiran {{ $fileName }}"
                                        class="max-h-96 w-full object-contain object-center">
                                </div>
                            @endif
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="border border-base-200/70">
                    <div class="space-y-5">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-base-content/45">Checklist Eksekusi</p>
                                <h3 class="mt-1 text-xl font-bold text-base-content">Todo List</h3>
                                <p class="mt-1 text-sm text-base-content/60" x-text="checklistSummary()">{{ $todoSummary }}</p>
                            </div>

                            @if ($totalTodos > 0)
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="badge badge-success badge-sm" x-text="completedTodos + ' selesai'">{{ $completedTodos }} selesai</span>
                                    <span class="badge badge-outline badge-sm" x-text="remainingTodos() + ' tersisa'">{{ $remainingTodos }} tersisa</span>
                                </div>
                            @endif
                        </div>

                        @if ($tugas->todos && $tugas->todos->count())
                            <ul class="space-y-3">
                                @foreach ($tugas->todos as $todo)
                                    @php
                                        $todoStatusValue = $todo->status instanceof Status ? $todo->status->value : (string) $todo->status;
                                        $todoAttachmentUrl = $todo->attachmentUrl();
                                        $todoAttachmentName = $todo->attachmentName();
                                        $todoAttachmentIsImage = $todo->attachmentIsImage();
                                    @endphp
                                    <li class="rounded-md border p-4 transition duration-200"
                                        x-data="{
                                            status: '{{ $todoStatusValue }}',
                                            labels: @js($statusOptions),
                                            updateStatus(event) {
                                                const checked = event.target.checked;
                                                const newStatus = checked ? '{{ $selesai }}' : '{{ $belum }}';
                                                const oldStatus = this.status;

                                                fetch('{{ route('todo.updateStatus', ['todo' => $todo->id]) }}', {
                                                    method: 'PATCH',
                                                    headers: {
                                                        'Content-Type': 'application/json',
                                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                        'Accept': 'application/json',
                                                    },
                                                    body: JSON.stringify({ status: newStatus })
                                                })
                                                .then(res => res.json())
                                                .then(data => {
                                                    if (data.success) {
                                                        this.status = data.status;
                                                        window.dispatchEvent(new CustomEvent('todo-progress-updated', {
                                                            detail: {
                                                                progress: data.progress,
                                                                tugas_status: data.tugas_status,
                                                                old_status: oldStatus,
                                                                todo_status: data.status
                                                            }
                                                        }));
                                                    }
                                                });
                                            }
                                        }"
                                        :class="status === '{{ $selesai }}'
                                            ? 'border-success/20 bg-success/5'
                                            : 'border-base-300/70 bg-base-100 hover:border-primary/20 hover:shadow-sm'">
                                        <div class="flex items-start gap-3">
                                            <x-ui.checkbox :bare="true"
                                                id="todo-check-{{ $todo->id }}"
                                                class="checkbox checkbox-primary mt-1"
                                                x-bind:checked="status === '{{ $selesai }}'"
                                                @change="updateStatus" />

                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-start gap-2">
                                                    <label for="todo-check-{{ $todo->id }}"
                                                        class="cursor-pointer text-sm font-semibold leading-6 text-base-content"
                                                        :class="status === '{{ $selesai }}' ? 'line-through text-base-content/50' : ''">
                                                        {{ \Illuminate\Support\Str::limit($todo->judul, 80) }}
                                                    </label>

                                                    <span class="badge badge-sm ml-auto" :class="status === '{{ $selesai }}' ? 'badge-success' : 'badge-warning'"
                                                        x-text="labels[status] || status"></span>
                                                </div>

                                                @if ($todo->deskripsi)
                                                    <p class="mt-2 text-sm leading-6 text-base-content/70"
                                                        :class="status === '{{ $selesai }}' ? 'text-base-content/45' : 'text-base-content/70'">
                                                        {{ $todo->deskripsi }}
                                                    </p>
                                                @endif

                                                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs text-base-content/60">
                                                    @if ($todo->deadline)
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-base-200 px-3 py-1">
                                                            <x-heroicon-o-calendar-days class="h-3.5 w-3.5" />
                                                            {{ \Carbon\Carbon::parse($todo->deadline)->translatedFormat('d M Y') }}
                                                        </span>
                                                    @endif
                                                    <span class="inline-flex items-center gap-1 rounded-full bg-base-200 px-3 py-1">
                                                        <x-heroicon-o-clipboard-document-check class="h-3.5 w-3.5" />
                                                        Checklist tugas
                                                    </span>
                                                    @if ($todoAttachmentUrl)
                                                        <a href="{{ $todoAttachmentUrl }}" target="_blank" rel="noreferrer"
                                                            class="inline-flex items-center gap-1 rounded-full bg-base-200 px-3 py-1 hover:bg-base-300 transition">
                                                            <x-heroicon-o-camera class="h-3.5 w-3.5" />
                                                            {{ $todoAttachmentName ?? 'Foto checklist' }}
                                                        </a>
                                                    @endif
                                                </div>

                                                @if ($todoAttachmentUrl && $todoAttachmentIsImage)
                                                    <div class="mt-4 overflow-hidden rounded-md border border-base-300/70 bg-base-200/40">
                                                        <img src="{{ $todoAttachmentUrl }}"
                                                            alt="Preview foto checklist {{ $todoAttachmentName }}"
                                                            class="max-h-72 w-full object-contain object-center">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="rounded-md border border-dashed border-base-300/80 bg-base-100 px-6 py-10 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-md bg-base-200 text-base-content/60">
                                    <x-heroicon-o-list-bullet class="h-6 w-6" />
                                </div>
                                <h4 class="mt-4 text-lg font-semibold text-base-content">Belum ada todo</h4>
                                <p class="mt-2 text-sm text-base-content/60">
                                    Tambahkan checklist di halaman edit supaya progres tugas lebih mudah dilacak.
                                </p>
                                <x-ui.button type="primary" size="sm" class="mt-4" :href="route('tugas.edit', $tugas->id)" :isSubmit="false">
                                    <x-heroicon-o-plus class="h-4 w-4" />
                                    Tambah Todo
                                </x-ui.button>
                            </div>
                        @endif
                    </div>
                </x-ui.card>
            </div>

            <div class="space-y-6">
                <x-ui.card class="border border-base-200/70">
                    <div class="space-y-5">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-base-content/45">Informasi Tugas</p>
                                <h3 class="mt-1 text-xl font-bold text-base-content">Ringkasan Akademik</h3>
                            </div>
                            <x-ui.badge :type="$deadlineBadgeType" size="sm">{{ $deadlineRelativeLabel }}</x-ui.badge>
                        </div>

                        <div class="rounded-md border p-4 {{ $deadlineSurfaceClass }}">
                            <div class="flex items-start gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-md bg-base-100/80 text-base-content">
                                    <x-heroicon-o-clock class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-widest text-base-content/50">Deadline utama</p>
                                    <div class="mt-1 text-lg font-bold text-base-content">{{ $deadlineLabel }}</div>
                                    <p class="text-sm {{ $deadlineTextClass }}">{{ $deadlineRelativeLabel }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-md bg-info/10 text-info">
                                    <x-heroicon-o-academic-cap class="h-5 w-5" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold uppercase tracking-widest text-base-content/45">Mata Kuliah</p>
                                    <div class="mt-1 font-semibold text-base-content">{{ $tugas->mataKuliah->nama ?? '-' }}</div>
                                    <p class="text-sm text-base-content/60">{{ $tugas->mataKuliah->dosen ?? 'Dosen belum tersedia' }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-md bg-warning/10 text-warning">
                                    <x-heroicon-o-flag class="h-5 w-5" />
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-widest text-base-content/45">Prioritas</p>
                                    <div class="mt-1 font-semibold text-base-content">{{ $priorityLabel }}</div>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-md bg-secondary/10 text-secondary">
                                    <x-heroicon-o-user-group class="h-5 w-5" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-semibold uppercase tracking-widest text-base-content/45">Absensi Terkait</p>
                                    @if ($tugas->absensi)
                                        <div class="mt-1 font-semibold text-base-content">
                                            {{ $tugas->absensi->pertemuan_ke ? 'Pertemuan ' . $tugas->absensi->pertemuan_ke : 'Pertemuan terkait' }}
                                        </div>
                                        <p class="text-sm text-base-content/60">
                                            {{ $tugas->absensi->tanggal?->translatedFormat('d F Y') ?? '-' }}
                                            @if ($tugas->absensi->topik)
                                                • {{ $tugas->absensi->topik }}
                                            @endif
                                        </p>
                                    @else
                                        <div class="mt-1 font-semibold text-base-content/60">Belum ditautkan ke absensi tertentu</div>
                                    @endif
                                </div>
                            </div>

                            <div class="grid gap-4 sm:grid-cols-2">
                                <div class="rounded-md bg-base-200/45 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-widest text-base-content/45">Dibuat</p>
                                    <div class="mt-1 text-sm font-semibold text-base-content">
                                        {{ $tugas->created_at->format('d M Y, H:i') }}
                                    </div>
                                </div>
                                <div class="rounded-md bg-base-200/45 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-widest text-base-content/45">Diperbarui</p>
                                    <div class="mt-1 text-sm font-semibold text-base-content">
                                        {{ $tugas->updated_at->format('d M Y, H:i') }}
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-md bg-base-200/45 p-4">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-semibold uppercase tracking-widest text-base-content/45">Reminder</p>
                                        <div class="mt-1 text-sm font-semibold text-base-content">
                                            {{ $tugas->reminders->count() }} pengingat
                                        </div>
                                    </div>
                                    <span class="badge badge-outline">{{ $totalTodos }} todo</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card class="border border-base-200/70">
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-widest text-base-content/45">Aksi Cepat</p>
                            <h3 class="mt-1 text-xl font-bold text-base-content">Kelola Tugas</h3>
                            <p class="mt-1 text-sm text-base-content/60">Edit detail, buka mode fokus, atau hapus tugas dari panel ini.</p>
                        </div>

                        <x-ui.button type="primary" class="w-full justify-between" :href="route('tugas.edit', $tugas->id)" :isSubmit="false">
                            <span class="flex items-center gap-2">
                                <x-heroicon-o-pencil-square class="h-4 w-4" />
                                Edit Tugas
                            </span>
                            <x-heroicon-o-chevron-right class="h-4 w-4" />
                        </x-ui.button>

                        @if ($tugas->mataKuliah)
                            <x-ui.button type="secondary" class="w-full justify-between" :href="route('mata-kuliah.show', $tugas->mataKuliah)" :isSubmit="false">
                                <span class="flex items-center gap-2">
                                    <x-heroicon-o-link class="h-4 w-4" />
                                    Buka Mode Fokus
                                </span>
                                <x-heroicon-o-arrow-top-right-on-square class="h-4 w-4" />
                            </x-ui.button>
                        @endif

                        @if ($fileUrl)
                            <x-ui.button type="ghost" class="w-full justify-between" :href="$fileUrl" target="_blank" rel="noreferrer" :isSubmit="false">
                                <span class="flex items-center gap-2">
                                    <x-heroicon-o-paper-clip class="h-4 w-4" />
                                    Lihat Lampiran
                                </span>
                                <x-heroicon-o-arrow-down-tray class="h-4 w-4" />
                            </x-ui.button>
                        @endif

                        <x-ui.button type="error" class="w-full justify-between" :isSubmit="false" outline
                            @click="$dispatch('confirm-delete', { action: '{{ route('tugas.destroy', $tugas->id) }}', message: 'Hapus tugas {{ $tugas->judul }}?' })">
                            <span class="flex items-center gap-2">
                                <x-heroicon-o-trash class="h-4 w-4" />
                                Hapus Tugas
                            </span>
                            <x-heroicon-o-chevron-right class="h-4 w-4" />
                        </x-ui.button>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.app>
