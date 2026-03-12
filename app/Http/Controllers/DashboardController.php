<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use App\Models\Tugas;
use Carbon\Carbon;
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

        return view('dashboard.index', compact(
            'totalTugas',
            'tugasSelesai',
            'tugasBelum',
            'tugasProgress',
            'deadlineTerdekat',
            'jadwalHariIni',
            'reminders'
        ));
    }
}
