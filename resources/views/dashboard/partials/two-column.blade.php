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
