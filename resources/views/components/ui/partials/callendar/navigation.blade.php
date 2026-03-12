{{-- Navigation --}}
<x-ui.card class="mb-6">
    <div class="flex items-center justify-between">
        <button class="btn btn-ghost btn-sm" @click="prevMonth()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        <h3 class="text-lg font-bold" x-text="monthYear"></h3>
        <button class="btn btn-ghost btn-sm" @click="nextMonth()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    {{-- Legend --}}
    <div class="flex items-center justify-center flex-wrap gap-8 mt-3 text-xs md:text-sm">
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-primary"></span>
            <span>Jadwal Kuliah</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-error"></span>
            <span>Deadline Tugas</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-primary"></span>
            <span class="w-3 h-3 rounded-full bg-success"></span>
            <span class="w-3 h-3 rounded-full bg-info"></span>
            <span>Event Custom</span>
        </div>
        <div class="flex items-center gap-1.5">
            <span class="w-3 h-3 rounded-full bg-base-300"></span>
            <span>Hari Ini</span>
        </div>
    </div>
</x-ui.card>
