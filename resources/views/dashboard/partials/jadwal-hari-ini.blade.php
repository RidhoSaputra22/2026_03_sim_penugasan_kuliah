{{-- Jadwal Hari Ini --}}
<x-ui.card title="Jadwal Hari Ini">
    @if ($jadwalHariIni->isEmpty())
        <div class="text-center py-8 text-base-content/50">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-3 opacity-40" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <p class="text-sm">Tidak ada jadwal hari ini </p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Jam</th>
                        <th>Mata Kuliah</th>
                        <th>Ruangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($jadwalHariIni as $jadwal)
                        <tr class="hover">
                            <td class="font-mono text-sm">
                                {{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }} -
                                {{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}
                            </td>
                            <td>
                                <div class="font-medium">{{ $jadwal->nama }}</div>
                                <div class="text-xs text-base-content/60">{{ $jadwal->dosen }}</div>
                            </td>
                            <td>
                                <x-ui.badge type="ghost" size="sm">{{ $jadwal->ruangan }}</x-ui.badge>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-ui.card>
