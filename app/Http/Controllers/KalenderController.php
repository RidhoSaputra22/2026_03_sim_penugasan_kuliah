<?php

namespace App\Http\Controllers;

use App\Enums\DayOfWeek;
use App\Enums\Status;
use App\Models\Event;
use App\Models\MataKuliah;
use App\Models\Tugas;

class KalenderController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $jadwalEvents = MataKuliah::all()->map(function ($mk) {
            $hari = $mk->hari instanceof DayOfWeek
                ? $mk->hari
                : DayOfWeek::from((string) $mk->hari);

            return [
            'id'          => 'jadwal-' . $mk->id,
            'title'       => $mk->nama,
            'daysOfWeek'  => [$hari->toFullCalendar()],
            'startTime'   => $mk->jam_mulai,
            'endTime'     => $mk->jam_selesai,
            'extendedProps' => [
                'type'    => 'jadwal',
                'hari'    => $hari->value,
                'ruangan' => $mk->ruangan,
                'dosen'   => $mk->dosen,
            ],
        ];
        });

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
                    'status'      => $t->status instanceof Status ? $t->status->value : (string) $t->status,
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
            'color' => $e->color ?? '#2196f3',
        ]);


        // dd($jadwalEvents);

        $events = $jadwalEvents
            ->toBase()
            ->merge($deadlineEvents)
            ->merge($customEvents)
            ->values();


        // Sidebar stats
        $totalMataKuliah = MataKuliah::count();
        $totalTugasAktif = Tugas::where('user_id', $user->id)
            ->whereIn('status', [Status::BELUM, Status::PROGRESS])
            ->count();
        $totalEvents = Event::where('user_id', $user->id)->count();
        $avgProgress = Tugas::where('user_id', $user->id)->avg('progress') ?? 0;

        return view('kalender.index', compact(
            'events',
            'totalMataKuliah',
            'totalTugasAktif',
            'totalEvents',
            'avgProgress'
        ));
    }
}
