<x-layouts.app title="Kalender Akademik">
    <x-slot:header>
        <x-layouts.page-header title="Kalender Akademik"
            description="Jadwal kuliah dan deadline tugas dalam satu tampilan" />
    </x-slot:header>

    {{-- Quick Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <x-ui.stat title="Mata Kuliah" :value="$totalMataKuliah" trend="up">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Tugas Aktif" :value="$totalTugasAktif" trend="{{ $totalTugasAktif > 5 ? 'down' : 'up' }}">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-warning" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Total Event" :value="$totalEvents" trend="up">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </x-slot:icon>
        </x-ui.stat>
        <x-ui.stat title="Rata-rata Progress" :value="round($avgProgress) . '%'" trend="{{ $avgProgress >= 50 ? 'up' : 'down' }}">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </x-slot:icon>
        </x-ui.stat>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Kalender --}}
        <x-ui.callendar :events="$events" class="lg:col-span-2"/>

        {{-- Upcoming list --}}
        <div class="lg:col-span-1 space-y-6" x-data="kalenderApp()" >
            {{-- Progress Overview Mini --}}
            <x-ui.card title="Progress Overview">
                <div class="flex items-center justify-center gap-6">
                    <div class="radial-progress text-primary" style="--value:{{ round($avgProgress) }}; --size:5rem; --thickness:6px;" role="progressbar">
                        <span class="text-sm font-bold">{{ round($avgProgress) }}%</span>
                    </div>
                    <div class="space-y-1 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-warning"></div>
                            <span>{{ $totalTugasAktif }} tugas aktif</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-info"></div>
                            <span>{{ $totalEvents }} event</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-primary"></div>
                            <span>{{ $totalMataKuliah }} mata kuliah</span>
                        </div>
                    </div>
                </div>
            </x-ui.card>
            @php
                $dayOfWeek = \App\Enums\DayOfWeek::toArray();

            @endphp
            <x-ui.card title="Jadwal Hari Ini">
                <div class="space-y-2" x-data="{
                    today: '{{ $dayOfWeek[date('N') - 1] }}',
                    jadwalHariIni() {
                        return weekSchedule.filter(j => j.hari === this.today);
                    }
                }">
                    <template x-for="j in jadwalHariIni()" :key="j.title + j.hari">
                        <div class="flex items-center gap-3 p-2 rounded-lg bg-base-200/50">
                            <div class="w-2 h-2 rounded-full bg-primary shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium truncate" x-text="j.title"></div>
                                <div class="text-xs text-base-content/60">
                                    <span x-text="j.hari"></span> •
                                    <span x-text="j.jam_mulai + ' - ' + j.jam_selesai"></span> •
                                    <span x-text="j.ruangan"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="jadwalHariIni().length === 0" class="text-center py-4 text-base-content/50 text-sm">
                        Tidak ada jadwal minggu ini
                    </div>
                </div>
            </x-ui.card>

            {{-- Deadline Mendatang --}}
            <x-ui.card title="Deadline Mendatang">
                <div class="space-y-2">
                    <template x-for="d in upcomingDeadlines" :key="d.title + d.date">
                        <div class="flex items-center gap-3 p-2 rounded-lg bg-base-200/50">
                            <div class="w-2 h-2 rounded-full bg-error shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs sm:text-sm font-medium truncate" x-text="d.title"></div>
                                <div class="text-[10px] sm:text-xs text-base-content/60">
                                    <span x-text="d.mata_kuliah"></span> •
                                    <span x-text="formatDate(d.date)"></span>
                                </div>
                            </div>
                            <div class="badge badge-xs sm:badge-sm"
                                :class="d.status === 'progress' ? 'badge-warning' : 'badge-error'"
                                x-text="d.progress + '%'">
                            </div>
                        </div>
                    </template>
                    <div x-show="upcomingDeadlines.length === 0"
                        class="text-center py-4 text-base-content/50 text-xs sm:text-sm">
                        Tidak ada deadline mendatang
                    </div>
                </div>
            </x-ui.card>

            {{-- Event Mendatang --}}
            <x-ui.card title="Event Mendatang">
                <div class="space-y-2">
                    <template x-for="e in upcomingEvents" :key="e.title + e.start">
                        <div class="flex items-center gap-3 p-2 rounded-lg bg-base-200/50">
                            <div class="w-2 h-2 rounded-full" :class="'bg-' + (e.color ?? 'info') + ' shrink-0'"></div>
                            <div class="flex-1 min-w-0">
                                <div class="text-xs sm:text-sm font-medium truncate" x-text="e.title"></div>
                                <div class="text-[10px] sm:text-xs text-base-content/60">
                                    <span x-text="formatDate(e.start)"></span>
                                    <template x-if="e.extendedProps && e.extendedProps.location">
                                        <span> • <span x-text="e.extendedProps.location"></span></span>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="upcomingEvents.length === 0"
                        class="text-center py-4 text-base-content/50 text-xs sm:text-sm">
                        Tidak ada event mendatang
                    </div>
                </div>
            </x-ui.card>

        </div>
    </div>
</x-layouts.app>
