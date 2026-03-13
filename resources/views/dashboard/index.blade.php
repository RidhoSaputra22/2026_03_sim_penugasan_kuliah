<x-layouts.app title="Dashboard">
    <x-slot:header>
        <x-layouts.page-header title="Dashboard" description="Selamat datang, {{ auth()->user()->name }}! 👋" />
    </x-slot:header>

    @include('dashboard.partials.reminder-alerts')

    @include('dashboard.partials.overdue-alert')

    @include('dashboard.partials.stats-row1')

    @include('dashboard.partials.stats-row2')

    @include('dashboard.partials.two-column')

    @include('dashboard.partials.progress-priority')


    @include('dashboard.partials.bottom-row')

    @include('dashboard.partials.quick-actions')

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const weeklyData = @json($weeklyProgress);
            new Chart(document.getElementById('weeklyChart'), {
                type: 'bar',
                data: {
                    labels: weeklyData.map(w => w.week),
                    datasets: [
                        {
                            label: 'Dibuat',
                            data: weeklyData.map(w => w.dibuat),
                            backgroundColor: 'oklch(0.7 0.18 250 / 0.6)',
                            borderRadius: 4,
                        },
                        {
                            label: 'Selesai',
                            data: weeklyData.map(w => w.selesai),
                            backgroundColor: 'oklch(0.7 0.17 145 / 0.6)',
                            borderRadius: 4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'oklch(0.5 0 0 / 0.08)' } },
                        x: { grid: { display: false } }
                    },
                    plugins: {
                        legend: { position: 'bottom', labels: { padding: 12, usePointStyle: true, pointStyleWidth: 8, font: { size: 11 } } }
                    }
                }
            });
        });
    </script>
    @endpush
</x-layouts.app>
