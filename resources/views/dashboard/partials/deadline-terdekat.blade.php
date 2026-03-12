{{-- Deadline Terdekat --}}
<x-ui.card title="Deadline Terdekat">
    @if ($deadlineTerdekat->isEmpty())
        <div class="text-center py-8 text-base-content/50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 opacity-40" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm">Tidak ada deadline mendekat</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($deadlineTerdekat as $tugas)
                @php
                    $daysLeft = now()->diffInDays($tugas->deadline, false);
                    $badgeType = $daysLeft <= 1 ? 'error' : ($daysLeft <= 3 ? 'warning' : 'info');
                @endphp
                <a href="{{ route('tugas.show', $tugas->id) }}"
                    class="flex items-center gap-3 p-3 rounded-lg hover:bg-base-200 transition-colors">
                    <div class="flex-1 min-w-0">
                        <div class="font-medium truncate">{{ $tugas->judul }}</div>
                        <div class="text-xs text-base-content/60">{{ $tugas->mataKuliah->nama ?? '-' }}</div>
                    </div>
                    <div class="flex flex-col items-end gap-1">
                        <x-ui.badge :type="$badgeType" size="sm">
                            {{ $daysLeft <= 0 ? 'Lewat!' : ceil($daysLeft) . ' hari' }}
                        </x-ui.badge>
                        <div class="w-20">
                            <progress class="progress progress-primary w-full" value="{{ $tugas->progress }}"
                                max="100"></progress>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif

    <x-slot:actions>
        <x-ui.button type="ghost" size="sm" :href="route('tugas.index')">
            Lihat Semua Tugas →
        </x-ui.button>
    </x-slot:actions>
</x-ui.card>
