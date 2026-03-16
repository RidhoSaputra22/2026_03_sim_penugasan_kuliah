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
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Jam</th>
                            <th>Mata Kuliah</th>
                            <th class="hidden sm:block">Ruangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jadwalBesok as $jadwal)
                            <tr class="hover">
                                <td class="font-mono text-sm">
                                    {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                                    <div class="sm:hidden text-xs text-base-content/60">Ruangan {{ $jadwal->ruangan }}</div>
                                </td>
                                <td>
                                    <div class="font-medium">{{ $jadwal->nama }}</div>
                                    <div class="text-xs text-base-content/60">{{ $jadwal->dosen }}</div>
                                </td>
                                <td class="hidden sm:block">
                                    <x-ui.badge type="ghost" size="sm">{{ $jadwal->ruangan }}</x-ui.badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
