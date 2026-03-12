<x-layouts.app title="Jadwal Kuliah">
    <x-slot:header>
        <x-layouts.page-header title="Jadwal Kuliah" description="Kelola jadwal mata kuliah Anda">
            <x-slot:actions>
                <x-ui.import-export-buttons module="mata-kuliah" class="" />

                <x-ui.button type="primary" size="sm" :href="route('mata-kuliah.create')" >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Mata Kuliah
                </x-ui.button>

            </x-slot:actions>
        </x-layouts.page-header>
    </x-slot:header>

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-ui.stat title="Total Mata Kuliah" :value="$totalMataKuliah">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="Total SKS" :value="$totalSks" description="beban studi">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
            </x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="Jumlah Dosen" :value="$totalDosen">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-accent" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="Jam/Minggu" :value="$totalJamPerMinggu" description="jam kuliah">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-info" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </x-slot:icon>
        </x-ui.stat>
    </div>

    {{-- Jadwal per Hari Distribution --}}
    <x-ui.card class="mb-6">
        <h3 class="font-semibold text-sm mb-3">Distribusi Jadwal per Hari</h3>
        <div class="flex flex-wrap gap-2">
            @php
                $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                $hariColors = ['primary', 'secondary', 'accent', 'info', 'success', 'warning'];
            @endphp
            @foreach ($hariList as $i => $hari)
                @php $count = $jadwalPerHari[$hari] ?? 0; @endphp
                <div class="flex-1 min-w-[80px] text-center p-3 rounded-lg bg-base-200/50">
                    <div class="text-xs text-base-content/60 mb-1">{{ $hari }}</div>
                    <div class="text-xl font-bold text-{{ $hariColors[$i] }}">{{ $count }}</div>
                    <div class="text-[10px] text-base-content/50">mata kuliah</div>
                </div>
            @endforeach
        </div>
    </x-ui.card>

    {{-- Filters --}}
    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('mata-kuliah.index') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <x-ui.input name="search" label="Cari" placeholder="Cari mata kuliah, kode, dosen..."
                    :value="request('search')" />
            </div>
            <div class="w-full">
                <x-ui.select name="hari" label="Filter Hari" :searchable="false" placeholder="Semua Hari"
                    :options="[
                        'Senin' => 'Senin',
                        'Selasa' => 'Selasa',
                        'Rabu' => 'Rabu',
                        'Kamis' => 'Kamis',
                        'Jumat' => 'Jumat',
                        'Sabtu' => 'Sabtu',
                    ]" :value="request('hari')" />
            </div>
            <x-ui.button type="primary" size="md">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                Cari
            </x-ui.button>
            @if (request('search') || request('hari'))
                <x-ui.button type="ghost" size="md" :href="route('mata-kuliah.index')" :isSubmit="false">
                    Reset
                </x-ui.button>
            @endif
        </form>
    </x-ui.card>

    {{-- Table --}}
    <x-ui.card>
        @if ($mataKuliah->isEmpty())
            <div class="text-center py-12 text-base-content/50">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto mb-4 opacity-30" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
                <p class="text-lg font-medium">Belum ada mata kuliah</p>
                <p class="text-sm mt-1">Tambahkan mata kuliah untuk melihat jadwal</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Mata Kuliah</th>
                            <th>Dosen</th>
                            <th>Hari</th>
                            <th>Jam</th>
                            <th>Ruangan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mataKuliah as $mk)
                            <tr class="hover">
                                <td class="font-mono text-sm">{{ $mk->kode }}</td>
                                <td class="font-medium">{{ $mk->nama }}</td>
                                <td>{{ $mk->dosen }}</td>
                                <td>
                                    <x-ui.badge type="info" size="sm">{{ $mk->hari }}</x-ui.badge>
                                </td>
                                <td class="font-mono text-sm">
                                    {{ \Carbon\Carbon::parse($mk->jam_mulai)->format('H:i') }} -
                                    {{ \Carbon\Carbon::parse($mk->jam_selesai)->format('H:i') }}
                                </td>
                                <td>{{ $mk->ruangan }}</td>
                                <td>
                                    <div class="flex items-center justify-center gap-1">
                                        <x-ui.button type="ghost" size="xs" :href="route('mata-kuliah.edit', $mk->id)">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </x-ui.button>
                                        <x-ui.button type="ghost" size="xs" :isSubmit="false"
                                            @click="$dispatch('confirm-delete', { action: '{{ route('mata-kuliah.destroy', $mk->id) }}', message: 'Hapus mata kuliah {{ $mk->nama }}?' })">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-error"
                                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </x-ui.button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $mataKuliah->withQueryString()->links() }}
            </div>
        @endif
    </x-ui.card>
</x-layouts.app>
