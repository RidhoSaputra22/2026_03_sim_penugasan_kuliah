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
