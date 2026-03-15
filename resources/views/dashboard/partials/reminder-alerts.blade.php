{{-- Reminder Alerts --}}
@foreach ($mataKuliahBerlangsung as $mataKuliahAktif)
    <x-ui.alert type="success" :dismissible="true" class="mb-3">
        <div>
            <div class="font-medium">
                {{ $mataKuliahAktif->nama }} sedang berlangsung sekarang.
            </div>
            <div class="text-xs opacity-70">
                {{ $mataKuliahAktif->jam_mulai }} - {{ $mataKuliahAktif->jam_selesai }}
                @if ($mataKuliahAktif->ruangan)
                    <span class="mx-1">•</span>{{ $mataKuliahAktif->ruangan }}
                @endif
            </div>
        </div>

        <x-slot:actions>
            <x-ui.button
                type="ghost"
                size="sm"
                :href="route('mata-kuliah.show', $mataKuliahAktif) . '#focus-hub'">
                <x-heroicon-o-bolt class="h-4 w-4" />
                Masuk mode fokus
            </x-ui.button>
        </x-slot:actions>
    </x-ui.alert>
@endforeach

@foreach ($reminders as $reminder)
    @php
        $daysLeft = now()->diffInDays($reminder->deadline, false);
        $alertType = $daysLeft <= 0 ? 'error' : ($daysLeft <= 1 ? 'warning' : 'info');
        $alertMsg =
            $daysLeft <= 0
                ? 'Deadline ' . $reminder->judul . ' sudah lewat!'
                : ($daysLeft <= 1
                    ? 'Deadline ' . $reminder->judul . ' besok!'
                    : 'Deadline ' . $reminder->judul . ' ' . ceil($daysLeft) . ' hari lagi');
    @endphp
    <x-ui.alert :type="$alertType" :dismissible="true" class="mb-3">
        {{ $alertMsg }}
        <span class="text-xs opacity-70">— {{ $reminder->mataKuliah->nama ?? '' }}</span>
    </x-ui.alert>
@endforeach
