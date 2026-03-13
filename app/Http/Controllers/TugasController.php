<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\MataKuliah;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use \App\Enums\Status;

class TugasController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Tugas::where('user_id', $user->id)->with(['mataKuliah', 'absensi']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('mata_kuliah_id')) {
            $query->where('mata_kuliah_id', $request->mata_kuliah_id);
        }

        if ($request->filled('search')) {
            $query->where('judul', 'like', "%{$request->search}%");
        }

        $tugas = $query->orderBy('deadline', 'asc')->paginate(15);
        $mataKuliah = MataKuliah::orderBy('nama')->get();

        // Stats summary
        $totalTugas = Tugas::where('user_id', $user->id)->count();
        $tugasSelesai = Tugas::where('user_id', $user->id)->where('status', Status::SELESAI)->count();
        $tugasProgress = Tugas::where('user_id', $user->id)->where('status', Status::PROGRESS)->count();
        $tugasBelum = Tugas::where('user_id', $user->id)->where('status', Status::BELUM)->count();
        $tugasTerlambat = Tugas::where('user_id', $user->id)
            ->whereIn('status', [Status::BELUM, Status::PROGRESS])
            ->where('deadline', '<', now())
            ->count();
        $avgProgress = Tugas::where('user_id', $user->id)->avg('progress') ?? 0;

        // Tugas per prioritas
        $tugasPerPrioritas = Tugas::where('user_id', $user->id)
            ->select('prioritas', DB::raw('count(*) as total'))
            ->groupBy('prioritas')
            ->pluck('total', 'prioritas')
            ->toArray();

        // Deadline minggu ini
        $deadlineMingguIni = Tugas::where('user_id', $user->id)
            ->whereIn('status', [Status::BELUM, Status::PROGRESS])
            ->whereBetween('deadline', [now()->startOfWeek(), now()->endOfWeek()])
            ->count();

        return view('tugas.index', compact(
            'tugas',
            'mataKuliah',
            'totalTugas',
            'tugasSelesai',
            'tugasProgress',
            'tugasBelum',
            'tugasTerlambat',
            'avgProgress',
            'tugasPerPrioritas',
            'deadlineMingguIni'
        ));
    }

    public function create()
    {
        $mataKuliah = MataKuliah::orderBy('nama')->get();
        $absensi = Absensi::with('mataKuliah')->orderByDesc('tanggal')->orderByDesc('pertemuan_ke')->get();
        return view('tugas.create', compact('mataKuliah', 'absensi'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
            'absensi_id' => 'nullable|exists:absensis,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'deadline' => 'required|date|after_or_equal:today',
            'status' => 'required|in:' . Status::BELUM->value . ',' . Status::PROGRESS->value . ',' . Status::SELESAI->value,
            'progress' => 'required|integer|min:0|max:100',
            'prioritas' => 'required|in:rendah,sedang,tinggi',
            'file' => 'nullable',
            'catatan' => 'nullable|string',
            'todos' => 'nullable|array',
            'todos.*.judul' => 'required_with:todos|string|max:255',
            'todos.*.deskripsi' => 'nullable|string',
        ]);

        $validated['absensi_id'] = $this->resolveTaskAbsensiId($request);

        $validated['user_id'] = auth()->id();

        if ($request->hasFile('file')) {
            $validated['file'] = $request->file('file')->store('tugas', 'public');
        }

        // Simpan tugas
        $tugas = Tugas::create($validated);

        // Simpan todos jika ada
        if (!empty($validated['todos'])) {
            foreach ($validated['todos'] as $todo) {
                if (!empty($todo['judul'])) {
                    $tugas->todos()->create([
                        'judul' => $todo['judul'],
                        'deskripsi' => $todo['deskripsi'] ?? null,
                        'status' => Status::BELUM,
                        'deadline' => $tugas->deadline,
                    ]);
                }
            }
        }

        return redirect()->route('tugas.index')
            ->with('success', 'Tugas berhasil ditambahkan.');
    }

    public function show(Tugas $tugas)
    {
        $this->authorize($tugas);
        $tugas->load('mataKuliah', 'absensi', 'reminders', 'todos');
        return view('tugas.show', compact('tugas'));
    }

    public function edit(Tugas $tugas)
    {
        $this->authorize($tugas);
        $mataKuliah = MataKuliah::orderBy('nama')->get();
        $absensi = Absensi::with('mataKuliah')->orderByDesc('tanggal')->orderByDesc('pertemuan_ke')->get();
        return view('tugas.edit', compact('tugas', 'mataKuliah', 'absensi'));
    }

    public function update(Request $request, Tugas $tugas)
    {
        $this->authorize($tugas);

        $validated = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliahs,id',
            'absensi_id' => 'nullable|exists:absensis,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'deadline' => 'required|date',
            'status' => 'required|in:' . Status::BELUM->value . ',' . Status::PROGRESS->value . ',' . Status::SELESAI->value,

            'progress' => 'required|integer|min:0|max:100',
            'prioritas' => 'required|in:rendah,sedang,tinggi',
            'file' => 'nullable',
            'catatan' => 'nullable|string',
            'todos' => 'nullable|array',
            'todos.*.judul' => 'required_with:todos|string|max:255',
            'todos.*.deskripsi' => 'nullable|string',
        ]);

        $validated['absensi_id'] = $this->resolveTaskAbsensiId($request);

        if ($request->hasFile('file')) {
            $validated['file'] = $request->file('file')->store('tugas', 'public');
        }

        $tugas->update($validated);

        // Update todos: delete old, add new
        $tugas->todos()->delete();
        if (!empty($validated['todos'])) {
            foreach ($validated['todos'] as $todo) {
                if (!empty($todo['judul'])) {
                    $tugas->todos()->create([
                        'judul' => $todo['judul'],
                        'deskripsi' => $todo['deskripsi'] ?? null,
                        'status' => Status::BELUM,
                        'deadline' => $tugas->deadline,
                    ]);
                }
            }
        }

        return redirect()->route('tugas.show', $tugas)
            ->with('success', 'Tugas berhasil diupdate.');
    }

    public function destroy(Tugas $tugas)
    {
        $this->authorize($tugas);
        $tugas->delete();
        return redirect()->route('tugas.index')
            ->with('success', 'Tugas berhasil dihapus.');
    }


    // Progress tugas otomatis berdasarkan todo
    private function updateTugasProgressFromTodos(Tugas $tugas)
    {
        $total = $tugas->todos()->count();
        $done = $tugas->todos()->where('status', Status::SELESAI)->count();
        $progress = $total > 0 ? intval(round(($done / $total) * 100)) : 0;
        $status = $progress >= 100 ? Status::SELESAI : ($progress > 0 ? Status::PROGRESS : Status::BELUM);
        $tugas->progress = $progress;
        $tugas->status = $status;
        $tugas->save();
    }

    private function authorize(Tugas $tugas)
    {
        if ($tugas->user_id !== auth()->id()) {
            abort(403);
        }
    }

    // Update status todo (checked/unchecked)
    public function updateTodoStatus(Request $request, $todoId)
    {
        $todo = \App\Models\Todo::findOrFail($todoId);
        // Pastikan hanya pemilik tugas yang bisa update
        if ($todo->tugas->user_id !== auth()->id()) {
            abort(403);
        }
        $validated = $request->validate([
            'status' => 'required|in:' . Status::BELUM->value . ',' . Status::SELESAI->value,
        ]);
        $todo->status = $validated['status'];
        $todo->save();
        // Update progress tugas otomatis
        $this->updateTugasProgressFromTodos($todo->tugas);
        return response()->json([
            'success' => true,
            'status' => $todo->status,
            'progress' => $todo->tugas->progress,
            'tugas_status' => $todo->tugas->status
        ]);
    }

    private function resolveTaskAbsensiId(Request $request): ?int
    {
        if (!$request->filled('absensi_id')) {
            return null;
        }

        $absensi = Absensi::findOrFail($request->integer('absensi_id'));

        if ((int) $absensi->mata_kuliah_id !== $request->integer('mata_kuliah_id')) {
            throw ValidationException::withMessages([
                'absensi_id' => 'Absensi yang dipilih tidak sesuai dengan mata kuliah tugas.',
            ]);
        }

        return $absensi->id;
    }
}
