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

    {{-- Stats Cards --}}
    <div class=" grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6 ">
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

        <x-ui.stat title="Jadwal Hari Ini" :value="$jadwalHariIni->count()" description="mata kuliah">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-info" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>
    </div>

    {{-- Two Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Jadwal Hari Ini --}}
        <x-ui.card title="Jadwal Hari Ini">
            @if ($jadwalHariIni->isEmpty())
                <div class="text-center py-8 text-base-content/50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 opacity-40" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <p class="text-sm">Tidak ada jadwal hari ini </p>
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
</x-layouts.app>
