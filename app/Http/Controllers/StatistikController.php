<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use App\Models\Todo;
use App\Models\Tugas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Enums\Status;

class StatistikController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = now()->startOfDay();

        // Tugas per status
        $tugasPerStatus = Tugas::where('user_id', $user->id)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Tugas per mata kuliah
        $tugasPerMataKuliah = Tugas::where('user_id', $user->id)
            ->join('mata_kuliahs', 'tugas.mata_kuliah_id', '=', 'mata_kuliahs.id')
            ->select('mata_kuliahs.nama', DB::raw('count(*) as total'))
            ->groupBy('mata_kuliahs.nama')
            ->pluck('total', 'nama')
            ->toArray();

        // Rata-rata progress
        $avgProgress = Tugas::where('user_id', $user->id)->avg('progress') ?? 0;

        // Total stats
        $totalTugas = Tugas::where('user_id', $user->id)->count();
        $tugasSelesai = Tugas::where('user_id', $user->id)->where('status', Status::SELESAI)->count();
        $tugasTerlambat = Tugas::where('user_id', $user->id)
            ->whereIn('status', [Status::BELUM, Status::PROGRESS])
            ->where('deadline', '<', now())
            ->count();

        // Tugas per prioritas
        $tugasPerPrioritas = Tugas::where('user_id', $user->id)
            ->select('prioritas', DB::raw('count(*) as total'))
            ->groupBy('prioritas')
            ->pluck('total', 'prioritas')
            ->toArray();

        // Progress per mata kuliah
        $progressPerMataKuliah = Tugas::where('tugas.user_id', $user->id)
            ->join('mata_kuliahs', 'tugas.mata_kuliah_id', '=', 'mata_kuliahs.id')
            ->select('mata_kuliahs.nama', DB::raw('AVG(tugas.progress) as avg_progress'))
            ->groupBy('mata_kuliahs.nama')
            ->pluck('avg_progress', 'nama')
            ->map(fn($v) => round($v))
            ->toArray();

        // Weekly activity (4 minggu terakhir)
        $weeklyActivity = collect(range(3, 0))->map(function ($weeksAgo) use ($user) {
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

        // Deadline timeline (next 14 days)
        $deadlineTimeline = Tugas::where('user_id', $user->id)
            ->whereIn('status', [Status::BELUM, Status::PROGRESS])
            ->whereBetween('deadline', [now(), now()->addDays(14)])
            ->orderBy('deadline')
            ->with('mataKuliah')
            ->get()
            ->map(function ($t) use ($today) {
                $deadline = Carbon::parse($t->deadline);

                return [
                    'judul' => $t->judul,
                    'mata_kuliah' => $t->mataKuliah->nama ?? '-',
                    'deadline' => $deadline->format('d M'),
                    'days_left' => $today->diffInDays($deadline->copy()->startOfDay(), false),
                    'progress' => $t->progress,
                    'status' => $t->status instanceof Status ? $t->status->value : (string) $t->status,
                ];
            });

        // Todo completion stats
        $totalTodos = Todo::whereHas('tugas', fn($q) => $q->where('user_id', $user->id))->count();
        $todosSelesai = Todo::whereHas('tugas', fn($q) => $q->where('user_id', $user->id))
            ->where('status', Status::SELESAI)->count();

        // Produktivitas score (0-100)
        $produktivitas = $totalTugas > 0
            ? round((($tugasSelesai / $totalTugas) * 40) + (($avgProgress / 100) * 40) + (($tugasTerlambat === 0 ? 1 : max(0, 1 - ($tugasTerlambat / $totalTugas))) * 20))
            : 0;

        return view('statistik.index', compact(
            'tugasPerStatus',
            'tugasPerMataKuliah',
            'avgProgress',
            'totalTugas',
            'tugasSelesai',
            'tugasTerlambat',
            'tugasPerPrioritas',
            'progressPerMataKuliah',
            'weeklyActivity',
            'deadlineTimeline',
            'totalTodos',
            'todosSelesai',
            'produktivitas'
        ));
    }
}
