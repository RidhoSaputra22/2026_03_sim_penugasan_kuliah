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

    {{-- Filters --}}
    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('tugas.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <x-ui.input name="search" label="Cari" placeholder="Cari judul tugas..." :value="request('search')" />
            </div>
            <div class="w-full">
                <x-ui.select name="status" label="Status" :searchable="false" placeholder="Semua Status"
                    :options="['belum' => 'Belum', 'progress' => 'Progress', 'selesai' => 'Selesai']"
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
                        'belum' => 'error',
                        'progress' => 'warning',
                        'selesai' => 'success',
                        default => 'ghost',
                    };
                    $statusLabel = match($item->status) {
                        'belum' => 'Belum',
                        'progress' => 'Progress',
                        'selesai' => 'Selesai',
                        default => $item->status,
                    };
                    $daysLeft = now()->diffInDays($item->deadline, false);
                    $isOverdue = $daysLeft < 0 && $item->status !== 'selesai';
                @endphp
                <x-ui.card class="{{ $isOverdue ? 'border-l-4 border-l-error' : '' }}" :href="route('tugas.show', $item->id)">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex-1 min-w-0">
                            <h3 class="font-semibold truncate">{{ $item->judul }}</h3>
                            <p class="text-sm text-base-content/60 mt-0.5">{{ $item->mataKuliah->nama ?? '-' }}</p>
                        </div>
                        <x-ui.badge :type="$statusBadge" size="sm">{{ $statusLabel }}</x-ui.badge>
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
                        @elseif($item->status !== 'selesai')
                            <span class="{{ $daysLeft <= 3 ? 'text-warning' : 'text-base-content/50' }}">
                                {{ ceil($daysLeft) }} hari lagi
                            </span>
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
