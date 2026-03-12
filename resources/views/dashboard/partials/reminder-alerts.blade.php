{{-- Reminder Alerts --}}
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
