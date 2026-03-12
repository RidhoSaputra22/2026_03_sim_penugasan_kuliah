<x-layouts.app title="Statistik">
    <x-slot:header>
        <x-layouts.page-header title="Statistik" description="Analisis produktivitas dan perkembangan tugas Anda" />
    </x-slot:header>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

            <x-ui.stat title="Total Tugas" :value="$totalTugas">
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </x-slot:icon>
            </x-ui.stat>



            <x-ui.stat title="Tugas Selesai" :value="$tugasSelesai" trend="up"
                :trendValue="$totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100) . '%' : '0%'">
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </x-slot:icon>
            </x-ui.stat>



            <x-ui.stat title="Terlambat" :value="$tugasTerlambat">
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-error" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </x-slot:icon>
            </x-ui.stat>



            <x-ui.stat title="Rata-rata Progress" :value="round($avgProgress) . '%'">
                <x-slot:icon>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                    </svg>
                </x-slot:icon>
            </x-ui.stat>

    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Tugas per Status (Donut Chart) --}}
        <x-ui.card title="Tugas per Status">
            <div class="h-72">
                <canvas id="statusChart"></canvas>
            </div>
        </x-ui.card>

        {{-- Tugas per Mata Kuliah (Bar Chart) --}}
        <x-ui.card title="Tugas per Mata Kuliah">
            <div class="h-72">
                <canvas id="mataKuliahChart"></canvas>
            </div>
        </x-ui.card>

        {{-- Overall Progress (Gauge-like) --}}
        <x-ui.card title="Progress Keseluruhan" class="lg:col-span-2">
            <div class="flex flex-col items-center py-4">
                <div class="radial-progress text-primary text-2xl font-bold"
                     style="--value:{{ round($avgProgress) }}; --size:10rem; --thickness:0.8rem;"
                     role="progressbar">
                    {{ round($avgProgress) }}%
                </div>
                <p class="text-base-content/60 mt-4 text-center">
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
            // Status Chart (Donut)
            const statusData = @json($tugasPerStatus);
            const statusLabels = { belum: 'Belum', progress: 'Progress', selesai: 'Selesai' };
            const statusColors = {
                belum: 'oklch(0.7 0.18 25)',
                progress: 'oklch(0.8 0.17 85)',
                selesai: 'oklch(0.7 0.17 145)'
            };

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
                        legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true } }
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
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 },
                            grid: { color: 'oklch(0.5 0 0 / 0.1)' }
                        },
                        x: {
                            grid: { display: false }
                        }
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
