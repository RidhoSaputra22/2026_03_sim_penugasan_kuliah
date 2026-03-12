<x-layouts.app title="Kalender Akademik">
    <x-slot:header>
        <x-layouts.page-header title="Kalender Akademik"
            description="Jadwal kuliah dan deadline tugas dalam satu tampilan" />
    </x-slot:header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Jadwal Hari Ini --}}
        <x-ui.callendar :events="$events" class="lg:col-span-2"/>

        {{-- Upcoming list --}}
        <div class="lg:col-span-1 space-y-6" x-data="kalenderApp()" >
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
