{{-- Stats Cards Row 1 --}}
<div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-0 sm:mb-4 hidden sm:grid">
    <x-ui.stat title="Total Tugas" :value="$totalTugas" :href="route('tugas.index')">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
        </x-slot:icon>
    </x-ui.stat>

    <x-ui.stat title="Tugas Selesai" :value="$tugasSelesai"
        :href="route('tugas.index', ['status' => \App\Enums\Status::SELESAI->value])"
        description="{{ $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100) : 0 }}% selesai"
        trend="{{ $tugasSelesai > 0 ? 'up' : 'neutral' }}"
        trendValue="{{ $totalTugas > 0 ? round(($tugasSelesai / $totalTugas) * 100) : 0 }}%">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-success -ml-8" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </x-slot:icon>
    </x-ui.stat>

    <x-ui.stat title="Dalam Progress" :value="$tugasProgress"
        :href="route('tugas.index', ['status' => \App\Enums\Status::PROGRESS->value])">
        <x-slot:icon>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-warning" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </x-slot:icon>
    </x-ui.stat>

    <x-ui.stat title="Terlambat" :value="$tugasTerlambat"
        :href="route('tugas.index', ['deadline_state' => 'terlambat'])"
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
