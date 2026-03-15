@php
    $statusLabels = \App\Enums\Status::options();
    $statusColors = [
        \App\Enums\Status::BELUM->value => 'oklch(0.7 0.18 25)',
        \App\Enums\Status::PROGRESS->value => 'oklch(0.8 0.17 85)',
        \App\Enums\Status::SELESAI->value => 'oklch(0.7 0.17 145)',
        \App\Enums\Status::COMPLETED->value => 'oklch(0.7 0.17 145)',
        \App\Enums\Status::CANCELLED->value => 'oklch(0.55 0 0)',
    ];
@endphp

<x-layouts.app title="Statistik">
    <x-slot:header>
        <x-layouts.page-header title="Statistik" description="Analisis produktivitas dan perkembangan tugas Anda" />
    </x-slot:header>

    {{-- Summary Stats Row 1 --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
        <x-ui.stat title="Total Tugas" :value="$totalTugas">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Selesai" :value="$tugasSelesai" trend="up"
            :trendValue="$totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100) . '%' : '0%'">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Terlambat" :value="$tugasTerlambat" :trend="$tugasTerlambat > 0 ? 'down' : 'up'">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Avg Progress" :value="round($avgProgress) . '%'" :trend="$avgProgress >= 50 ? 'up' : 'down'">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Total Todos" :value="$totalTodos">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Todos Selesai" :value="$todosSelesai" trend="up"
            :trendValue="$totalTodos > 0 ? round(($todosSelesai / $totalTodos) * 100) . '%' : '0%'">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </x-slot:icon>
        </x-ui.stat>
    </div>

    {{-- Produktivitas Score + Progress Keseluruhan --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Produktivitas Score --}}
        <x-ui.card title="Skor Produktivitas">
            <div class="flex flex-col items-center py-4">
                <div class="radial-progress text-2xl font-bold {{ $produktivitas >= 70 ? 'text-success' : ($produktivitas >= 40 ? 'text-warning' : 'text-error') }}"
                     style="--value:{{ $produktivitas }}; --size:8rem; --thickness:0.7rem;"
                     role="progressbar">
                    {{ $produktivitas }}
                </div>
                <div class="mt-3 text-center">
                    <span class="badge {{ $produktivitas >= 70 ? 'badge-success' : ($produktivitas >= 40 ? 'badge-warning' : 'badge-error') }} badge-lg">
                        @if($produktivitas >= 80) Excellent
                        @elseif($produktivitas >= 60) Good
                        @elseif($produktivitas >= 40) Fair
                        @else Needs Improvement
                        @endif
                    </span>
                </div>
                <div class="mt-4 w-full space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Completion Rate</span>
                        <span class="font-medium">{{ $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100) : 0 }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">On-time Rate</span>
                        <span class="font-medium">{{ $totalTugas > 0 ? round((1 - ($tugasTerlambat / $totalTugas)) * 100) : 100 }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-base-content/60">Avg Progress</span>
                        <span class="font-medium">{{ round($avgProgress) }}%</span>
                    </div>
                </div>
            </div>
        </x-ui.card>

        {{-- Priority Distribution --}}
        <x-ui.card title="Distribusi Prioritas">
            <div class="h-72">
                <canvas id="priorityChart"></canvas>
            </div>
        </x-ui.card>

        {{-- Status Distribution --}}
        <x-ui.card title="Tugas per Status">
            <div class="h-72">
                <canvas id="statusChart"></canvas>
            </div>
        </x-ui.card>
    </div>

    {{-- Charts Row 2 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Weekly Activity --}}
        <x-ui.card title="Aktivitas Mingguan (4 Minggu Terakhir)">
            <div class="h-72">
                <canvas id="weeklyChart"></canvas>
            </div>
        </x-ui.card>

        {{-- Tugas per Mata Kuliah --}}
        <x-ui.card title="Tugas per Mata Kuliah">
            <div class="h-72">
                <canvas id="mataKuliahChart"></canvas>
            </div>
        </x-ui.card>
    </div>

    {{-- Progress per Mata Kuliah + Deadline Timeline --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Progress per Mata Kuliah --}}
        <x-ui.card title="Progress per Mata Kuliah">
            <div class="space-y-3 py-2">
                @forelse($progressPerMataKuliah as $nama => $progress)
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium truncate max-w-[70%]">{{ $nama }}</span>
                            <span class="text-base-content/60">{{ $progress }}%</span>
                        </div>
                        <progress class="progress {{ $progress >= 75 ? 'progress-success' : ($progress >= 40 ? 'progress-warning' : 'progress-error') }} w-full"
                                  value="{{ $progress }}" max="100"></progress>
                    </div>
                @empty
                    <div class="text-center py-4 text-base-content/50 text-sm">Belum ada data</div>
                @endforelse
            </div>
        </x-ui.card>

        {{-- Deadline Timeline --}}
        <x-ui.card title="Deadline 14 Hari Kedepan">
            <div class="space-y-2 max-h-80 overflow-y-auto py-2">
                @forelse($deadlineTimeline as $item)
                    <div class="flex items-center gap-3 p-2 rounded-lg bg-base-200/50">
                        <div class="text-center min-w-[3rem]">
                            <div class="text-lg font-bold {{ $item['days_left'] <= 2 ? 'text-error' : ($item['days_left'] <= 5 ? 'text-warning' : 'text-base-content') }}">
                                {{ max(0, $item['days_left']) }}
                            </div>
                            <div class="text-[10px] text-base-content/50 uppercase">hari</div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium truncate">{{ $item['judul'] }}</div>
                            <div class="text-xs text-base-content/60">{{ $item['mata_kuliah'] }} &bull; {{ $item['deadline'] }}</div>
                            <progress class="progress progress-sm {{ $item['progress'] >= 75 ? 'progress-success' : ($item['progress'] >= 40 ? 'progress-warning' : 'progress-error') }} w-full mt-1"
                                      value="{{ $item['progress'] }}" max="100"></progress>
                        </div>
                        <div class="badge badge-sm {{ $item['days_left'] <= 2 ? 'badge-error' : ($item['days_left'] <= 5 ? 'badge-warning' : 'badge-info') }}">
                            {{ $item['progress'] }}%
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-base-content/50 text-sm">
                        Tidak ada deadline dalam 14 hari kedepan
                    </div>
                @endforelse
            </div>
        </x-ui.card>
    </div>

    {{-- Todo Completion + Overall Progress Gauge --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Todo Completion --}}
        <x-ui.card title="Todo Completion Rate">
            <div class="flex items-center gap-6 py-4">
                @php $todoRate = $totalTodos > 0 ? round(($todosSelesai / $totalTodos) * 100) : 0; @endphp
                <div class="radial-progress text-accent text-xl font-bold"
                     style="--value:{{ $todoRate }}; --size:6rem; --thickness:6px;" role="progressbar">
                    {{ $todoRate }}%
                </div>
                <div class="flex-1 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span>Selesai</span>
                        <span class="font-bold text-success">{{ $todosSelesai }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Belum</span>
                        <span class="font-bold text-base-content/60">{{ $totalTodos - $todosSelesai }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>Total</span>
                        <span class="font-bold">{{ $totalTodos }}</span>
                    </div>
                    <progress class="progress progress-accent w-full" value="{{ $todosSelesai }}" max="{{ max($totalTodos, 1) }}"></progress>
                </div>
            </div>
        </x-ui.card>

        {{-- Overall Progress --}}
        <x-ui.card title="Progress Keseluruhan">
            <div class="flex flex-col items-center py-4">
                <div class="radial-progress text-primary text-2xl font-bold"
                     style="--value:{{ round($avgProgress) }}; --size:8rem; --thickness:0.8rem;"
                     role="progressbar">
                    {{ round($avgProgress) }}%
                </div>
                <p class="text-base-content/60 mt-3 text-center text-sm">
                    @if($avgProgress >= 80)
                        Luar biasa! Kamu hampir menyelesaikan semua tugas!
                    @elseif($avgProgress >= 50)
                        Bagus! Terus kerjakan tugas-tugas yang tersisa.
                    @elseif($avgProgress >= 25)
                        Ayo tingkatkan progress tugasmu!
                    @else
                        Mulai kerjakan tugasmu sekarang!
                    @endif
                </p>
            </div>
        </x-ui.card>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const gridColor = 'oklch(0.5 0 0 / 0.1)';

            // Status Chart (Donut)
            const statusData = @json($tugasPerStatus);
            const statusLabels = @json($statusLabels);
            const statusColors = @json($statusColors);

            new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(statusData).map(k => statusLabels[k] || k),
                    datasets: [{
                        data: Object.values(statusData),
                        backgroundColor: Object.keys(statusData).map(k => statusColors[k] || '#ccc'),
                        borderWidth: 0,
                        spacing: 2,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, font: { size: 11 } } }
                    }
                }
            });

            // Priority Chart (Pie)
            const priorityData = @json($tugasPerPrioritas);
            const priorityLabels = { tinggi: 'Tinggi', sedang: 'Sedang', rendah: 'Rendah' };
            const priorityColors = {
                tinggi: 'oklch(0.7 0.18 25)',
                sedang: 'oklch(0.8 0.17 85)',
                rendah: 'oklch(0.7 0.18 250)'
            };

            new Chart(document.getElementById('priorityChart'), {
                type: 'doughnut',
                data: {
                    labels: Object.keys(priorityData).map(k => priorityLabels[k] || k),
                    datasets: [{
                        data: Object.values(priorityData),
                        backgroundColor: Object.keys(priorityData).map(k => priorityColors[k] || '#ccc'),
                        borderWidth: 0,
                        spacing: 2,
                        borderRadius: 4,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '55%',
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, font: { size: 11 } } }
                    }
                }
            });

            // Weekly Activity Chart
            const weeklyData = @json($weeklyActivity);

            new Chart(document.getElementById('weeklyChart'), {
                type: 'bar',
                data: {
                    labels: weeklyData.map(w => w.week),
                    datasets: [
                        {
                            label: 'Dibuat',
                            data: weeklyData.map(w => w.dibuat),
                            backgroundColor: 'oklch(0.7 0.18 250 / 0.7)',
                            borderColor: 'oklch(0.7 0.18 250)',
                            borderWidth: 1,
                            borderRadius: 4,
                        },
                        {
                            label: 'Selesai',
                            data: weeklyData.map(w => w.selesai),
                            backgroundColor: 'oklch(0.7 0.17 145 / 0.7)',
                            borderColor: 'oklch(0.7 0.17 145)',
                            borderWidth: 1,
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: gridColor } },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: { position: 'top', labels: { usePointStyle: true, font: { size: 11 } } }
                    }
                }
            });

            // Mata Kuliah Chart (Bar)
            const mkData = @json($tugasPerMataKuliah);

            new Chart(document.getElementById('mataKuliahChart'), {
                type: 'bar',
                data: {
                    labels: Object.keys(mkData),
                    datasets: [{
                        label: 'Jumlah Tugas',
                        data: Object.values(mkData),
                        backgroundColor: 'oklch(0.7 0.18 250 / 0.7)',
                        borderColor: 'oklch(0.7 0.18 250)',
                        borderWidth: 1,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: gridColor } },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });
    </script>
    @endpush
</x-layouts.app>
