@php
    $selesai = \App\Enums\Status::SELESAI->value;
    $belum = \App\Enums\Status::BELUM->value;
    $progress = \App\Enums\Status::PROGRESS->value;
@endphp

<x-layouts.app title="Detail Tugas">
    <x-slot:header>
        <x-layouts.page-header title="Detail Tugas" description="{{ $tugas->judul }}">
            <x-slot:actions>
                <x-ui.button type="ghost" size="sm" :href="route('tugas.index')">← Kembali</x-ui.button>
                <x-ui.button type="primary" size="sm" :href="route('tugas.edit', $tugas->id)">
                    <x-heroicon-o-pencil-square class="h-4 w-4" />
                    Edit
                </x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main Info --}}
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card title="{{ $tugas->judul }}">
                @php
                    $statusBadge = match($tugas->status) {
                        $belum => 'error',
                        $progress => 'warning',
                        $selesai => 'success',
                        default => 'ghost',
                    };
                    $statusLabel = match($tugas->status) {
                        $belum => 'Belum Dikerjakan',
                        $progress => 'Sedang Dikerjakan',
                        $selesai => $selesai,
                        default => $tugas->status,
                    };
                @endphp

                <div class="flex items-center gap-2 mb-4">
                    <x-ui.badge :type="$statusBadge">{{ $statusLabel }}</x-ui.badge>
                    <x-ui.badge type="info">{{ $tugas->mataKuliah->nama ?? '-' }}</x-ui.badge>
                </div>

                @if($tugas->deskripsi)
                    <div class="prose prose-sm max-w-none">
                        <h4 class="text-sm font-semibold text-base-content/70 uppercase tracking-wide mb-2">Deskripsi</h4>
                        <p class="text-base-content/80">{{ $tugas->deskripsi }}</p>
                    </div>
                @else
                    <p class="text-base-content/50 italic">Tidak ada deskripsi</p>
                @endif

                {{-- Todo List Section --}}
                <div class="mt-6">
                    <h4 class="text-sm font-semibold text-base-content/70 uppercase tracking-wide mb-2">Todo List</h4>
                    @if($tugas->todos && $tugas->todos->count())
                        <ul class="space-y-2">
                            @foreach($tugas->todos as $todo)
                                <li class="border rounded p-3 bg-base-100 flex flex-col gap-1"
                                    x-data="{
                                        status: '{{ $todo->status }}',
                                        updateStatus(e) {
                                            const checked = e.target.checked;
                                            const newStatus = checked ? '{{ $selesai }}' : '{{ $belum }}';
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
                                                    window.dispatchEvent(new CustomEvent('todo-progress-updated', { detail: { progress: data.progress, tugas_status: data.tugas_status } }));
                                                }
                                            });
                                        }
                                    }"
                                >
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox"
                                            :checked="status === '{{$selesai}}'"
                                            @change="updateStatus"
                                            class="checkbox checkbox-primary"
                                            id="todo-check-{{ $todo->id }}"
                                        />
                                        <label :for="'todo-check-{{ $todo->id }}'" class="font-semibold cursor-pointer uppercase">
                                            {{ Str::limit($todo->judul, 50) }}
                                        </label>
                                        <div class="ml-auto">
                                            <template x-if="status === '{{$selesai}}'">
                                            <x-ui.badge type="success">Selesai</x-ui.badge>
                                        </template>
                                        <template x-if="status !== '{{$selesai}}'">
                                            <x-ui.badge type="warning">Pending</x-ui.badge>
                                        </template>
                                        </div>
                                    </div>
                                    @if($todo->deskripsi)
                                        <div class="text-xs text-base-content/70">{{ $todo->deskripsi }}</div>
                                    @endif
                                    @if($todo->deadline)
                                        <span class="text-xs text-base-content/60 ">
                                            Deadline: {{ \Carbon\Carbon::parse($todo->deadline)->format('d M Y') }}
                                        </span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-base-content/50 italic">Tidak ada todo</div>
                    @endif
                </div>
            </x-ui.card>

            {{-- Progress Update Section --}}
            <x-ui.card title="Progress">
                <div class="mb-4" x-data="{ progress: {{ $tugas->progress }}, status: '{{ $tugas->status }}' }" x-init="
                    window.addEventListener('todo-progress-updated', e => {
                        progress = e.detail.progress;
                        status = e.detail.tugas_status;
                    });
                ">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium">Progress Tugas</span>
                        <span class="text-2xl font-bold text-primary" x-text="progress + '%'">{{ $tugas->progress }}%</span>
                    </div>
                    <progress class="progress progress-primary w-full h-4" :value="progress" max="100"></progress>
                    <div class="mt-2">
                        <span class="badge" :class="{
                            'badge-success': status === '{{$selesai}}',
                            'badge-warning': status === '{{$progress}}',
                            'badge-error': status === '{{$belum}}'
                        }" x-text="status.charAt(0).toUpperCase() + status.slice(1)"></span>
                    </div>
                </div>
            </x-ui.card>
        </div>

        {{-- Sidebar Info --}}
        <div class="space-y-6">

            {{-- Info Card --}}
            <x-ui.card title="Informasi">
                <div class="space-y-4">
                    <div>
                        <div class="text-xs text-base-content/50 uppercase tracking-wide">Mata Kuliah</div>
                        <div class="font-medium mt-0.5">{{ $tugas->mataKuliah->nama ?? '-' }}</div>
                    </div>
                    <div class="divider my-0"></div>
                    <div>
                        <div class="text-xs text-base-content/50 uppercase tracking-wide">Absensi Terkait</div>
                        @if($tugas->absensi)
                            <div class="mt-1.5 flex flex-wrap items-center gap-2">
                                <x-ui.badge type="info">
                                    {{ $tugas->absensi->pertemuan_ke ? 'Pertemuan ' . $tugas->absensi->pertemuan_ke : 'Pertemuan terkait' }}
                                </x-ui.badge>
                                <x-ui.badge type="ghost">
                                    {{ $tugas->absensi->status?->label() ?? '-' }}
                                </x-ui.badge>
                            </div>
                            <div class="text-sm mt-2 text-base-content/70">
                                {{ $tugas->absensi->tanggal?->translatedFormat('d F Y') ?? '-' }}
                            </div>
                            @if($tugas->absensi->topik)
                                <div class="text-sm mt-1 text-base-content/60">{{ $tugas->absensi->topik }}</div>
                            @endif
                            <a href="{{ route('mata-kuliah.show', $tugas->mataKuliah) }}"
                                class="btn btn-ghost btn-sm mt-3 px-0">
                                <x-heroicon-o-link class="h-4 w-4" />
                                Buka Mode Fokus
                            </a>
                        @else
                            <div class="font-medium mt-0.5 text-base-content/60">Belum ditautkan ke absensi tertentu</div>
                        @endif
                    </div>
                    <div class="divider my-0"></div>
                    <div>
                        <div class="text-xs text-base-content/50 uppercase tracking-wide">Dosen</div>
                        <div class="font-medium mt-0.5">{{ $tugas->mataKuliah->dosen ?? '-' }}</div>
                    </div>
                    <div class="divider my-0"></div>
                    <div>
                        @php
                            $daysLeft = now()->diffInDays($tugas->deadline, false);
                            $isOverdue = $daysLeft < 0 && $tugas->status !== $selesai;
                        @endphp
                        <div class="text-xs text-base-content/50 uppercase tracking-wide">Deadline</div>
                        <div class="font-medium mt-0.5 {{ $isOverdue ? 'text-error' : '' }}">
                            {{ \Carbon\Carbon::parse($tugas->deadline)->format('d F Y') }}
                        </div>
                        @if($tugas->status !== $selesai)
                            <div class="text-xs mt-1 {{ $isOverdue ? 'text-error' : ($daysLeft <= 3 ? 'text-warning' : 'text-base-content/60') }}">
                                {{ $isOverdue ? 'Terlambat ' . abs(ceil($daysLeft)) . ' hari' : ceil($daysLeft) . ' hari lagi' }}
                            </div>
                        @endif
                    </div>
                    <div class="divider my-0"></div>
                    <div>
                        <div class="text-xs text-base-content/50 uppercase tracking-wide">Dibuat</div>
                        <div class="text-sm mt-0.5">{{ $tugas->created_at->format('d M Y, H:i') }}</div>
                    </div>
                </div>
            </x-ui.card>

            {{-- Actions --}}
            <x-ui.card title="Aksi">
                <div class="space-y-2">
                    <x-ui.button type="primary" class="w-full" :href="route('tugas.edit', $tugas->id)" :isSubmit="false">
                        <x-heroicon-o-pencil-square class="h-4 w-4" />
                        Edit Tugas
                    </x-ui.button>
                    <x-ui.button type="error" class="w-full" :isSubmit="false" outline
                        @click="$dispatch('confirm-delete', { action: '{{ route('tugas.destroy', $tugas->id) }}', message: 'Hapus tugas {{ $tugas->judul }}?' })">
                        <x-heroicon-o-trash class="h-4 w-4" />
                        Hapus Tugas
                    </x-ui.button>
                </div>
            </x-ui.card>
        </div>
    </div>
</x-layouts.app>
