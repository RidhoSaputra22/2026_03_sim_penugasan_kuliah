<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\MataKuliah;
use App\Models\Todo;
use App\Models\Tugas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use \App\Enums\Status;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $totalTugas = Tugas::where('user_id', $user->id)->count();
        $tugasSelesai = Tugas::where('user_id', $user->id)->where('status', Status::SELESAI)->count();
        $tugasBelum = Tugas::where('user_id', $user->id)->where('status', Status::BELUM)->count();
        $tugasProgress = Tugas::where('user_id', $user->id)->where('status', Status::PROGRESS)->count();

        // Tugas terlambat
        $tugasTerlambat = Tugas::where('user_id', $user->id)
            ->whereIn('status', [Status::BELUM, Status::PROGRESS])
            ->where('deadline', '<', now())
            ->count();

        // Rata-rata progress
        $avgProgress = Tugas::where('user_id', $user->id)->avg('progress') ?? 0;

        // Deadline terdekat (5 tugas belum selesai paling dekat deadlinenya)
        $deadlineTerdekat = Tugas::where('user_id', $user->id)
            ->whereIn('status', [Status::BELUM, Status::PROGRESS])
            ->where('deadline', '>=', now())
            ->orderBy('deadline', 'asc')
            ->with('mataKuliah')
            ->take(5)
            ->get();

        // Jadwal hari ini
        $hariIni = Carbon::now()->locale('id')->isoFormat('dddd');
        $jadwalHariIni = MataKuliah::where('hari', $hariIni)
            ->orderBy('jam_mulai', 'asc')
            ->get();

        // Reminders (tugas deadline <= 3 hari)
        $reminders = Tugas::where('user_id', $user->id)
            ->whereIn('status', [Status::BELUM, Status::PROGRESS])
            ->whereBetween('deadline', [now(), now()->addDays(3)])
            ->with('mataKuliah')
            ->orderBy('deadline', 'asc')
            ->get();

        // Total mata kuliah & SKS
        $totalMataKuliah = MataKuliah::count();
        $totalSks = MataKuliah::sum('sks') ?? 0;

        // Weekly progress data (tugas selesai per minggu - 4 minggu terakhir)
        $weeklyProgress = collect(range(3, 0))->map(function ($weeksAgo) use ($user) {
            $start = now()->subWeeks($weeksAgo)->startOfWeek();
            $end = now()->subWeeks($weeksAgo)->endOfWeek();
            return [
                'week' => $start->format('d M'),
                'selesai' => Tugas::where('user_id', $user->id)
                    ->where('status', Status::SELESAI)
                    ->whereBetween('updated_at', [$start, $end])
                    ->count(),
                'dibuat' => Tugas::where('user_id', $user->id)
                    ->whereBetween('created_at', [$start, $end])
                    ->count(),
            ];
        })->values();

        // Tugas per prioritas
        $tugasPerPrioritas = Tugas::where('user_id', $user->id)
            ->select('prioritas', DB::raw('count(*) as total'))
            ->groupBy('prioritas')
            ->pluck('total', 'prioritas')
            ->toArray();

        // Todo stats
        $totalTodos = Todo::whereHas('tugas', fn($q) => $q->where('user_id', $user->id))->count();
        $todosSelesai = Todo::whereHas('tugas', fn($q) => $q->where('user_id', $user->id))
            ->where('status', Status::SELESAI)->count();

        // Upcoming events (next 7 days)
        $upcomingEvents = Event::where('user_id', $user->id)
            ->where('start', '>=', now())
            ->where('start', '<=', now()->addDays(7))
            ->orderBy('start')
            ->take(3)
            ->get();

        // Jadwal besok
        $hariBesok = Carbon::tomorrow()->locale('id')->isoFormat('dddd');
        $jadwalBesok = MataKuliah::where('hari', $hariBesok)
            ->orderBy('jam_mulai', 'asc')
            ->get();

        return view('dashboard.index', compact(
            'totalTugas',
            'tugasSelesai',
            'tugasBelum',
            'tugasProgress',
            'tugasTerlambat',
            'avgProgress',
            'deadlineTerdekat',
            'jadwalHariIni',
            'reminders',
            'totalMataKuliah',
            'totalSks',
            'weeklyProgress',
            'tugasPerPrioritas',
            'totalTodos',
            'todosSelesai',
            'upcomingEvents',
            'jadwalBesok'
        ));
    }
}
