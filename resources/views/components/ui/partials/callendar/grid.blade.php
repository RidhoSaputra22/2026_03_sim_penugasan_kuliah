{{-- Calendar Grid --}}
<x-ui.card>
    {{-- Day Headers --}}
    <div class="grid grid-cols-7 gap-px mb-1 sm:mb-2">
        <template x-for="day in ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min']" :key="day">
            <div class="text-center text-xs sm:text-sm font-semibold text-base-content/60 py-1 sm:py-2" x-text="day">
            </div>
        </template>
    </div>

    {{-- Date Grid --}}
    <div class="grid grid-cols-7 gap-px border border-base-200 rounded-lg overflow-hidden text-[11px] sm:text-xs">
        <template x-for="(cell, index) in calendarCells" :key="index">
            <div class="bg-base-100 min-h-[60px] sm:min-h-[100px] p-1 sm:p-1.5 relative group border-r border-b border-base-200 focus-within:z-30"
                :class="{
                    'bg-primary/5': cell.isToday && !isInDragSelection(index),
                    'opacity-40': !cell.currentMonth,
                    'cursor-pointer': cell.currentMonth,
                    'cursor-default': !cell.currentMonth,
                    'bg-success/10 ring-1 ring-inset ring-success/40': isInDragSelection(index) && isDragging && cell
                        .currentMonth,
                }"
                @mousedown.prevent="onCellMouseDown(index, cell)" @mouseenter="onCellMouseEnter(index, cell)">

                <div class="font-medium mb-1 -mx-1 flex items-center gap-1"
                    :class="cell.isToday ? 'text-primary font-bold' : 'text-base-content/70'">
                    <div :class="cell.isToday ? 'badge badge-primary badge-xs' : ''">
                        <span x-text="cell.day"></span>
                        <span x-show="cell.isToday">Hari Ini</span>
                    </div>
                </div>

                {{-- Multi-day span bars --}}
                <template x-for="(bar, bi) in (cell.spanBars || [])" :key="bar.eventId || bi">
                    <div class="relative mb-0.5" @mouseenter="bar._hover = true" @mouseleave="bar._hover = false">
                        <div class="h-4 flex items-center text-[9px] font-medium -mx-1 sm:-mx-1.5 px-1 overflow-hidden truncate"
                            :class="[
                                getEventPillClass(bar.color),
                                bar.isStart ? 'rounded-l-full' : '',
                                bar.isEnd ? 'rounded-r-full' : ''
                            ]">
                            <span x-show="bar.showLabel" class="truncate block w-full px-0.5 font-semibold"
                                x-text="bar.title"></span>
                        </div>

                        <div x-cloak x-show="bar._hover" x-transition.opacity
                            class="pointer-events-none absolute left-0 top-full mt-1 z-50 w-48 rounded-md bg-neutral px-2 py-1.5 text-[10px] text-neutral-content shadow-lg">
                            <div class="font-semibold" x-text="bar.title"></div>
                            <div x-show="bar.location" class="opacity-80" x-text="'Lokasi: ' + bar.location"></div>
                            <div x-show="bar.start" class="opacity-80" x-text="'Mulai: ' + bar.start"></div>
                            <div x-show="bar.end" class="opacity-80" x-text="'Selesai: ' + bar.end"></div>
                        </div>
                    </div>
                </template>

                {{-- Events --}}
                <div class="space-y-0.5">
                    <template x-for="(event, ei) in cell.events" :key="event.eventId || ei">
                        <div class="relative" @mouseenter="event._hover = true" @mouseleave="event._hover = false">
                            <div class="text-[9px] sm:text-[10px] leading-tight px-1 py-0.5 rounded truncate cursor-default"
                                :class="event.type === 'jadwal' ?
                                    'bg-primary/15 text-primary' :
                                    event.type === 'custom' ?
                                    getEventPillClass(event.color) :
                                    'bg-error/15 text-error'"
                                x-text="event.title">
                            </div>

                            <div x-cloak x-show="event._hover" x-transition.opacity
                                class="pointer-events-none absolute left-0 top-full mt-1 z-50 w-52 rounded-md bg-neutral px-2 py-1.5 text-[10px] text-neutral-content shadow-lg">
                                <div class="font-semibold" x-text="event.title"></div>
                                <div x-show="event.jam_mulai || event.jam_selesai" class="opacity-80"
                                    x-text="`${event.jam_mulai || '-'} - ${event.jam_selesai || '-'}`">
                                </div>
                                <div x-show="event.ruangan" class="opacity-80" x-text="'Ruangan: ' + event.ruangan">
                                </div>
                                <div x-show="event.location" class="opacity-80" x-text="'Lokasi: ' + event.location">
                                </div>
                                <div x-show="event.description" class="opacity-80" x-text="event.description"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</x-ui.card>
