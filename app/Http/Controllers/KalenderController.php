<?php

namespace App\Http\Controllers;

use App\Enums\DayOfWeek;
use App\Models\Event;
use App\Models\MataKuliah;
use App\Models\Tugas;
use \App\Enums\Status;

class KalenderController extends Controller
{
    // FullCalendar day-of-week: 0=Sun, 1=Mon, 2=Tue, 3=Wed, 4=Thu, 5=Fri, 6=Sat

    public function index()
    {
        $user = auth()->user();

        $jadwalEvents = MataKuliah::all()->map(fn($mk) => [
            'id'          => 'jadwal-' . $mk->id,
            'title'       => $mk->nama,
            'daysOfWeek'  => [DayOfWeek::from($mk->hari)->toFullCalendar()],
            'startTime'   => $mk->jam_mulai,
            'endTime'     => $mk->jam_selesai,
            'extendedProps' => [
                'type'    => 'jadwal',
                'hari'    => $mk->hari,
                'ruangan' => $mk->ruangan,
                'dosen'   => $mk->dosen,
            ],
        ]);

        // dd($jadwalEvents);

        $deadlineEvents = Tugas::where('user_id', $user->id)
            ->whereIn('status', [Status::BELUM, Status::PROGRESS])
            ->with('mataKuliah')
            ->get()
            ->map(fn($t) => [
                'id'      => 'tugas-' . $t->id,
                'title'   => $t->judul,
                'start'   => $t->deadline,
                'allDay'  => true,
                'extendedProps' => [
                    'type'        => 'deadline',
                    'mata_kuliah' => $t->mataKuliah->nama ?? '-',
                    'status'      => $t->status,
                    'progress'    => $t->progress,
                ],
            ]);

        $customEvents = Event::where('user_id', $user->id)->get()->map(fn($e) => [
            'id'      => 'event-' . $e->id,
            'title'   => $e->title,
            'start'   => $e->start,
            'end'     => $e->end,
            'allDay'  => false,
            'extendedProps' => [
                'type'        => 'custom',
                'description' => $e->description,
                'location'    => $e->location,
                'eventId'     => $e->id,
            ],
        ]);

        $events = $jadwalEvents->merge($deadlineEvents)->merge($customEvents)->values();

        return view('kalender.index', compact('events'));
    }
}
