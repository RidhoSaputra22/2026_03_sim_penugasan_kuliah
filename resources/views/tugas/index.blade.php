@php
    use App\Enums\Status;
@endphp
<x-layouts.app title="Tugas">
    <x-slot:header>
        <x-layouts.page-header title="Manajemen Tugas" description="Kelola tugas dan deadline Anda">
            <x-slot:actions>
                <x-ui.button type="primary" size="sm" :href="route('tugas.create')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Tugas
                </x-ui.button>
            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    {{-- Stats Summary --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <x-ui.stat title="Total" :value="$totalTugas">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Selesai" :value="$tugasSelesai">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Progress" :value="$tugasProgress">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Belum" :value="$tugasBelum">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Terlambat" :value="$tugasTerlambat"
            description="{{ $tugasTerlambat > 0 ? 'Perlu perhatian!' : '' }}">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ $tugasTerlambat > 0 ? 'text-error' : 'text-success' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Avg Progress" :value="round($avgProgress) . '%'">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </x-slot:icon>
        </x-ui.stat>
    </div>

    {{-- Priority & Deadline Summary --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        {{-- Prioritas breakdown --}}
        <x-ui.card>
            <h3 class="font-semibold text-sm mb-3">Distribusi Prioritas</h3>
            <div class="flex gap-4 items-center">
                @php
                    $priConfig = [
                        'tinggi' => ['label' => 'Tinggi', 'color' => 'error'],
                        'sedang' => ['label' => 'Sedang', 'color' => 'warning'],
                        'rendah' => ['label' => 'Rendah', 'color' => 'info'],
                    ];
                @endphp
                @foreach ($priConfig as $key => $cfg)
                    @php $cnt = $tugasPerPrioritas[$key] ?? 0; @endphp
                    <div class="flex-1 text-center">
                        <div class="radial-progress text-{{ $cfg['color'] }} text-sm font-bold"
                            style="--value:{{ $totalTugas > 0 ? round(($cnt / $totalTugas) * 100) : 0 }}; --size:3.5rem; --thickness:0.3rem;"
                            role="progressbar">
                            {{ $cnt }}
                        </div>
                        <div class="text-xs text-base-content/60 mt-1">{{ $cfg['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </x-ui.card>

        {{-- Deadline info --}}
        <x-ui.card>
            <h3 class="font-semibold text-sm mb-3">Ringkasan Deadline</h3>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-base-200/50 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold text-warning">{{ $deadlineMingguIni }}</div>
                    <div class="text-xs text-base-content/60">Deadline Minggu Ini</div>
                </div>
                <div class="bg-base-200/50 rounded-lg p-3 text-center">
                    <div class="text-2xl font-bold {{ $tugasTerlambat > 0 ? 'text-error' : 'text-success' }}">{{ $tugasTerlambat }}</div>
                    <div class="text-xs text-base-content/60">Sudah Terlambat</div>
                </div>
            </div>
            @if ($totalTugas > 0)
                <div class="mt-3">
                    <div class="flex justify-between text-xs text-base-content/60 mb-1">
                        <span>Completion Rate</span>
                        <span class="font-mono">{{ round(($tugasSelesai / $totalTugas) * 100) }}%</span>
                    </div>
                    <progress class="progress progress-success w-full" value="{{ $tugasSelesai }}" max="{{ $totalTugas }}"></progress>
                </div>
            @endif
        </x-ui.card>
    </div>

    {{-- Filters --}}
    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('tugas.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <x-ui.input name="search" label="Cari" placeholder="Cari judul tugas..." :value="request('search')" />
            </div>
            <div class="w-full">
                <x-ui.select name="status" label="Status" :searchable="false" placeholder="Semua Status"
                    :options="collect(App\Enums\Status::cases())->mapWithKeys(fn($s) => [$s->value => $s->label()])->toArray()"
                    :value="request('status')" />
            </div>
            <div class="flex-1 min-w-[180px]">
                <x-ui.select name="mata_kuliah_id" label="Mata Kuliah" :searchable="true" placeholder="Semua Mata Kuliah"
                    :options="$mataKuliah->pluck('nama', 'id')->toArray()"
                    :value="request('mata_kuliah_id')" />
            </div>
            <x-ui.button type="primary" size="md">Cari</x-ui.button>
            @if(request()->hasAny(['search', 'status', 'mata_kuliah_id']))
                <x-ui.button type="ghost" size="md" :href="route('tugas.index')" :isSubmit="false">Reset</x-ui.button>
            @endif
        </form>
    </x-ui.card>

    {{-- Tugas Cards / Table --}}
    @if($tugas->isEmpty())
        <x-ui.card>
            <div class="text-center py-12 text-base-content/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
                <p class="text-lg font-medium">Belum ada tugas</p>
                <p class="text-sm mt-1">Tambahkan tugas baru untuk mulai tracking</p>
            </div>
        </x-ui.card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($tugas as $item)
                @php
                    

                    $statusBadge = match($item->status) {
                        Status::BELUM => 'error',
                        Status::PROGRESS => 'warning',
                        Status::SELESAI => 'success',
                        Status::COMPLETED => 'success',
                        Status::CANCELLED => 'ghost',
                        default => 'ghost',
                    };
                    $statusLabel = $item->status instanceof Status ? $item->status->label() : $item->status;
                    $prioritasBadge = match($item->prioritas ?? '') {
                        'tinggi' => 'error',
                        'sedang' => 'warning',
                        'rendah' => 'info',
                        default => 'ghost',
                    };
                    $daysLeft = now()->diffInDays($item->deadline, false);
                    $isOverdue = $daysLeft < 0 && ($item->status instanceof Status ? $item->status !== Status::SELESAI : $item->status !== 'selesai');
                @endphp
                <x-ui.card class="{{ $isOverdue ? 'border-l-4 border-l-error' : '' }}" :href="route('tugas.show', $item->id)">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold truncate">{{ $item->judul }}</h3>
                            <p class="text-sm text-base-content/60 mt-0.5">{{ $item->mataKuliah->nama ?? '-' }}</p>
                        </div>
                        <div class="flex flex-col items-end gap-1">
                            <x-ui.badge :type="$statusBadge" size="sm">{{ $statusLabel }}</x-ui.badge>
                            @if ($item->prioritas)
                                <x-ui.badge :type="$prioritasBadge" size="xs" :outline="true">{{ ucfirst($item->prioritas) }}</x-ui.badge>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="flex items-center justify-between text-xs text-base-content/60 mb-1">
                            <span>Progress</span>
                            <span class="font-mono">{{ $item->progress }}%</span>
                        </div>
                        <progress class="progress progress-primary w-full" value="{{ $item->progress }}" max="100"></progress>
                    </div>

                    <div class="flex items-center justify-between mt-3 text-xs">
                        <span class="text-base-content/60">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            {{ \Carbon\Carbon::parse($item->deadline)->format('d M Y') }}
                        </span>
                        @if($isOverdue)
                            <span class="text-error font-medium">Terlambat!</span>
                        @elseif(($item->status instanceof Status ? $item->status !== Status::SELESAI : $item->status !== 'selesai'))
                            <span class="{{ $daysLeft <= 3 ? 'text-warning' : 'text-base-content/50' }}">
                                {{ ceil($daysLeft) }} hari lagi
                            </span>
                        @else
                            <span class="text-success font-medium">Selesai</span>
                        @endif
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $tugas->withQueryString()->links() }}
        </div>
    @endif
</x-layouts.app>
