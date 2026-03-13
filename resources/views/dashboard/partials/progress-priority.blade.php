{{-- Progress Overview + Priority Widget --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Overall Progress Ring --}}
    <x-ui.card title="Progress Keseluruhan">
        <div class="flex flex-col items-center py-2">
            <div class="radial-progress text-primary text-xl font-bold"
                 style="--value:{{ round($avgProgress) }}; --size:8rem; --thickness:0.6rem;"
                 role="progressbar">
                {{ round($avgProgress) }}%
            </div>
            <p class="text-base-content/60 mt-3 text-center text-sm">
                @if($avgProgress >= 80)
                    Luar biasa! Hampir selesai semua!
                @elseif($avgProgress >= 50)
                    Bagus! Terus semangat!
                @elseif($avgProgress >= 25)
                    Ayo tingkatkan progress!
                @else
                    Mulai kerjakan tugasmu!
                @endif
            </p>
            <div class="w-full mt-3 space-y-1.5">
                <div class="flex justify-between text-xs">
                    <span class="text-base-content/60">Selesai</span>
                    <span class="font-medium text-success">{{ $tugasSelesai }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-base-content/60">Progress</span>
                    <span class="font-medium text-warning">{{ $tugasProgress }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-base-content/60">Belum</span>
                    <span class="font-medium text-error">{{ $tugasBelum }}</span>
                </div>
            </div>
        </div>
    </x-ui.card>

    {{-- Prioritas Tugas --}}
    <x-ui.card title="Tugas per Prioritas">
        <div class="space-y-4 py-2">
            @php
                $prioritasConfig = [
                    'tinggi' => ['label' => 'Tinggi', 'color' => 'error', 'progressClass' => 'progress-error'],
                    'sedang' => ['label' => 'Sedang', 'color' => 'warning', 'progressClass' => 'progress-warning'],
                    'rendah' => ['label' => 'Rendah', 'color' => 'info', 'progressClass' => 'progress-info'],
                ];
            @endphp
            @foreach ($prioritasConfig as $key => $config)
                @php $count = $tugasPerPrioritas[$key] ?? 0; @endphp
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <div class="flex items-center gap-2">
                            <x-ui.badge :type="$config['color']" size="xs">{{ $config['label'] }}</x-ui.badge>
                        </div>
                        <span class="text-sm font-semibold">{{ $count }}</span>
                    </div>
                    <progress class="progress {{ $config['progressClass'] }} w-full"
                        value="{{ $count }}" max="{{ max($totalTugas, 1) }}"></progress>
                </div>
            @endforeach
        </div>
        @if ($totalTugas > 0)
            <div class="text-xs text-base-content/50 mt-2 text-center">
                Dari total {{ $totalTugas }} tugas
            </div>
        @endif
    </x-ui.card>

    {{-- Weekly Activity Chart --}}
    <x-ui.card title="Aktivitas Mingguan">
        <div class="h-48">
            <canvas id="weeklyChart"></canvas>
        </div>
    </x-ui.card>
</div>
