{{-- Stats Cards Row 2 --}}
<div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-ui.stat title="Jadwal Hari Ini" :value="$jadwalHariIni->count()" description="mata kuliah"
        :href="route('kalender.index')">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-info" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </x-slot:icon>
    </x-ui.stat>

    <x-ui.stat title="Mata Kuliah" :value="$totalMataKuliah" description="{{ $totalSks }} SKS total"
        :href="route('mata-kuliah.index')">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-secondary" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
        </x-slot:icon>
    </x-ui.stat>

    <x-ui.stat title="Todo Selesai" :value="$todosSelesai . '/' . $totalTodos"
        :href="route('statistik.index')"
        description="{{ $totalTodos > 0 ? round(($todosSelesai / $totalTodos) * 100) : 0 }}% checklist">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </svg>
        </x-slot:icon>
    </x-ui.stat>

    <x-ui.stat title="Rata-rata Progress" :value="round($avgProgress) . '%'" description="progress keseluruhan"
        :href="route('statistik.index')">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-accent -ml-9" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
        </x-slot:icon>
    </x-ui.stat>
</div>
