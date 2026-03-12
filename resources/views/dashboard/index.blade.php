<x-layouts.app title="Dashboard">
    <x-slot:header>
        <x-layouts.page-header title="Dashboard" description="Selamat datang, {{ auth()->user()->name }}! 👋" />
    </x-slot:header>

    {{-- Reminder Alerts --}}
    @foreach ($reminders as $reminder)
        @php
            $daysLeft = now()->diffInDays($reminder->deadline, false);
            $alertType = $daysLeft <= 0 ? 'error' : ($daysLeft <= 1 ? 'warning' : 'info');
            $alertMsg =
                $daysLeft <= 0
                    ? 'Deadline ' . $reminder->judul . ' sudah lewat!'
                    : ($daysLeft <= 1
                        ? 'Deadline ' . $reminder->judul . ' besok!'
                        : 'Deadline ' . $reminder->judul . ' ' . ceil($daysLeft) . ' hari lagi');
        @endphp
        <x-ui.alert :type="$alertType" :dismissible="true" class="mb-3">
            {{ $alertMsg }}
            <span class="text-xs opacity-70">— {{ $reminder->mataKuliah->nama ?? '' }}</span>
        </x-ui.alert>
    @endforeach

    {{-- Overdue Alert --}}
    @if ($tugasTerlambat > 0)
        <x-ui.alert type="error" :dismissible="true" class="mb-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <strong>{{ $tugasTerlambat }} tugas terlambat!</strong> Segera selesaikan tugas yang sudah melewati deadline.
        </x-ui.alert>
    @endif

    {{-- Stats Cards Row 1 --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
        <x-ui.stat title="Total Tugas" :value="$totalTugas">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="Tugas Selesai" :value="$tugasSelesai"
            description="{{ $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100) : 0 }}% selesai"
            trend="{{ $tugasSelesai > 0 ? 'up' : 'neutral' }}"
            trendValue="{{ $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100) : 0 }}%">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-success" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="Dalam Progress" :value="$tugasProgress">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-warning" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="Terlambat" :value="$tugasTerlambat"
            description="{{ $tugasTerlambat > 0 ? 'Segera selesaikan!' : 'Aman' }}">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 {{ $tugasTerlambat > 0 ? 'text-error' : 'text-success' }}" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>
    </div>

    {{-- Stats Cards Row 2 --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat title="Jadwal Hari Ini" :value="$jadwalHariIni->count()" description="mata kuliah">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-info" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="Mata Kuliah" :value="$totalMataKuliah" description="{{ $totalSks }} SKS total">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-secondary" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="Rata-rata Progress" :value="round($avgProgress) . '%'">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-accent" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="Todo Selesai" :value="$todosSelesai . '/' . $totalTodos"
            description="{{ $totalTodos > 0 ? round(($todosSelesai / $totalTodos) * 100) : 0 }}% checklist">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>
    </div>

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

    {{-- Two Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

        {{-- Jadwal Hari Ini --}}
        <x-ui.card title="Jadwal Hari Ini">
            @if ($jadwalHariIni->isEmpty())
                <div class="text-center py-8 text-base-content/50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 opacity-40" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <p class="text-sm">Tidak ada jadwal hari ini</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Jam</th>
                                <th>Mata Kuliah</th>
                                <th>Ruangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($jadwalHariIni as $jadwal)
                                <tr class="hover">
                                    <td class="font-mono text-sm">
                                        {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                    </td>
                                    <td>
                                        <div class="font-medium">{{ $jadwal->nama }}</div>
                                        <div class="text-xs text-base-content/60">{{ $jadwal->dosen }}</div>
                                    </td>
                                    <td>
                                        <x-ui.badge type="ghost" size="sm">{{ $jadwal->ruangan }}</x-ui.badge>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-ui.card>

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
    </div>

    {{-- Bottom Row: Jadwal Besok + Upcoming Events --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Jadwal Besok --}}
        <x-ui.card title="Jadwal Besok">
            @if ($jadwalBesok->isEmpty())
                <div class="text-center py-6 text-base-content/50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-2 opacity-40" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                    <p class="text-sm">Tidak ada jadwal besok</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach ($jadwalBesok as $jadwal)
                        <div class="flex items-center gap-3 p-2 rounded-lg bg-base-200/50">
                            <div class="text-xs font-mono text-base-content/70 w-24 shrink-0">
                                {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium truncate">{{ $jadwal->nama }}</div>
                                <div class="text-xs text-base-content/60">{{ $jadwal->dosen }}</div>
                            </div>
                            <x-ui.badge type="ghost" size="xs">{{ $jadwal->ruangan }}</x-ui.badge>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-ui.card>

        {{-- Upcoming Events --}}
        <x-ui.card title="Event 7 Hari Kedepan">
            @if ($upcomingEvents->isEmpty())
                <div class="text-center py-6 text-base-content/50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto mb-2 opacity-40" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm">Tidak ada event mendatang</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach ($upcomingEvents as $event)
                        <div class="flex items-center gap-3 p-2 rounded-lg bg-base-200/50">
                            <div class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $event->color ?? '#6366f1' }}"></div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium truncate">{{ $event->title }}</div>
                                <div class="text-xs text-base-content/60">
                                    {{ \Carbon\Carbon::parse($event->start)->format('d M Y, H:i') }}
                                    @if ($event->location)
                                        • {{ $event->location }}
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
            <x-slot:actions>
                <x-ui.button type="ghost" size="sm" :href="route('kalender.index')">
                    Lihat Kalender →
                </x-ui.button>
            </x-slot:actions>
        </x-ui.card>
    </div>

    {{-- Quick Actions --}}
    <div class="mt-6">
        <x-ui.card title="Aksi Cepat">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <a href="{{ route('tugas.create') }}" class="btn btn-outline btn-primary btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Tugas
                </a>
                <a href="{{ route('mata-kuliah.create') }}" class="btn btn-outline btn-secondary btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah MK
                </a>
                <a href="{{ route('kalender.index') }}" class="btn btn-outline btn-accent btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Kalender
                </a>
                <a href="{{ route('statistik.index') }}" class="btn btn-outline btn-info btn-sm gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Statistik
                </a>
            </div>
        </x-ui.card>
    </div>

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
