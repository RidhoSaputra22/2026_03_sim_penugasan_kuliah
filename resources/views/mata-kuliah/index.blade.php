<x-layouts.app title="Jadwal Kuliah">
    <x-slot:header>
        <x-layouts.page-header title="Jadwal Kuliah" description="Kelola jadwal mata kuliah Anda">
            <x-slot:actions>
                <x-ui.import-export-buttons module="mata-kuliah" class="" />

                <x-ui.button type="primary" size="sm" :href="route('mata-kuliah.create')">
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
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-primary" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="Total SKS" :value="$totalSks" description="beban studi">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-secondary" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="Jumlah Dosen" :value="$totalDosen">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-accent" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </x-slot:icon>
        </x-ui.stat>

        <x-ui.stat title="Jam/Minggu" :value="$totalJamPerMinggu" description="jam kuliah">
            <x-slot:icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-info" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
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


    {{-- Table --}}

    <x-ui.card id="tabel-mata-kuliah">
          <x-ui.data-table
                    title="Daftar Mata Kuliah"
                    :data="$mataKuliah"
                    model="\App\Models\MataKuliah"
                    :exclude="[
                        'id',
                        'lms',
                        'lms_link',
                        'created_at',
                        'updated_at',
                        'warna',
                        'semester',
                        'tahun_ajaran',
                        'catatan',
                        'is_active',
                    ]" :labels="[
                        'kode' => 'Kode',
                        'nama' => 'Mata Kuliah',
                        'dosen' => 'Dosen',
                        'hari' => 'Hari',
                        'jam_mulai' => 'Jam Mulai',
                        'jam_selesai' => 'Jam Selesai',
                        'ruangan' => 'Ruangan',
                    ]" :formats="[
                        'hari' => 'badge',
                        'jam_mulai' => 'time',
                        'jam_selesai' => 'time',
                    ]" :sortable="['kode', 'nama', 'dosen', 'hari', 'jam_mulai', 'jam_selesai', 'ruangan']" :bulkActionRoute="route('mata-kuliah.bulk-action')"
                    :editRoute="fn($row) => route('mata-kuliah.edit', $row->id)" :deleteRoute="fn($row) => route('mata-kuliah.destroy', $row->id)">
                    <x-slot:filters>
                        <input type="text" name="dosen" value="{{ request('dosen') }}"
                            class="input input-bordered input-sm" placeholder="Filter dosen">
                        <x-ui.select size="sm" name="hari" :searchable="false" placeholder="Semua Hari"
                            :options="[
                                'Senin' => 'Senin',
                                'Selasa' => 'Selasa',
                                'Rabu' => 'Rabu',
                                'Kamis' => 'Kamis',
                                'Jumat' => 'Jumat',
                                'Sabtu' => 'Sabtu',
                            ]" :value="request('hari')" />
                    </x-slot:filters>

                    <x-slot:bulkActions>
                        <option value="set_senin">Set Hari = Senin</option>
                    </x-slot:bulkActions>
                </x-ui.data-table>
    </x-ui.card>
</x-layouts.app>
