<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Enums\DayOfWeek;
use App\Enums\Status;
use App\Models\Absensi;
use App\Models\MataKuliah;
use App\Models\Todo;
use App\Models\Tugas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MataKuliahController extends Controller
{
    public function index(Request $request)
    {
        $query = MataKuliah::query();

        // Filter hari
        if ($request->filled('hari')) {
            $query->where('hari', $request->hari);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%")
                    ->orWhere('dosen', 'like', "%{$search}%")
                    ->orWhere('hari', 'like', "%{$search}%")
                    ->orWhere('ruangan', 'like', "%{$search}%");
            });
        }

        // Sorting untuk data-table component
        $allowedSorts = [
            'kode',
            'nama',
            'dosen',
            'hari',
            'jam_mulai',
            'jam_selesai',
            'ruangan',
            'sks',
        ];

        $sort = $request->get('sort');
        $direction = $request->get('direction', 'asc') === 'desc' ? 'desc' : 'asc';

        if ($sort && in_array($sort, $allowedSorts, true)) {
            if ($sort === 'hari') {
                $query->orderByRaw("
                FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu') {$direction}
            ");
            } else {
                $query->orderBy($sort, $direction);
            }

            // Supaya kalau sort selain hari, jam_mulai tetap jadi urutan kedua
            if ($sort !== 'jam_mulai') {
                $query->orderBy('jam_mulai');
            }
        } else {
            // Default sorting lama Anda
            $query->orderByRaw("
            FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')
        ")->orderBy('jam_mulai');
        }

       $mataKuliah = $query->paginate(5)->withQueryString()->fragment('tabel-mata-kuliah');

        // Stats tetap global, tidak terpengaruh filter tabel
        $totalMataKuliah = MataKuliah::count();
        $totalSks = MataKuliah::sum('sks') ?? 0;
        $totalDosen = MataKuliah::distinct('dosen')->count('dosen');
        $totalRuangan = MataKuliah::distinct('ruangan')->count('ruangan');

        // Jadwal per hari
        $jadwalPerHari = MataKuliah::select('hari', DB::raw('count(*) as total'))
            ->groupBy('hari')
            ->pluck('total', 'hari')
            ->toArray();

        // Total jam kuliah per minggu
        $allMk = MataKuliah::all();
        $totalJamPerMinggu = $allMk->sum(function ($mk) {
            return $mk->durasi_menit ?? 0;
        });

        $totalJamPerMinggu = round($totalJamPerMinggu / 60, 1);

        return view('mata-kuliah.index', compact(
            'mataKuliah',
            'totalMataKuliah',
            'totalSks',
            'totalDosen',
            'totalRuangan',
            'jadwalPerHari',
            'totalJamPerMinggu'
        ));
    }

    public function show(MataKuliah $mataKuliah)
    {
        $userId = auth()->id();

        $absensis = $mataKuliah->absensis()
            ->orderByDesc('tanggal')
            ->orderByDesc('pertemuan_ke')
            ->get();

        $tugas = Tugas::query()
            ->where('user_id', $userId)
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->with([
                'absensi',
                'todos' => fn($query) => $query
                    ->orderByRaw("
                        CASE
                            WHEN UPPER(status) = 'SELESAI' THEN 3
                            WHEN UPPER(status) = 'PROGRESS' THEN 2
                            ELSE 1
                        END
                    ")
                    ->orderBy('deadline'),
            ])
            ->orderByRaw("
                CASE
                    WHEN status = 'BELUM' THEN 1
                    WHEN status = 'PROGRESS' THEN 2
                    WHEN status = 'SELESAI' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('deadline')
            ->get();

        $durasiKuliah = $mataKuliah->durasi_kuliah_label;

        $absensiPayload = $absensis->map(function (Absensi $item) use ($mataKuliah) {
            $notes = $this->normalizeAttendanceNotes($item->catatan);
            $status = $item->status instanceof AttendanceStatus
                ? $item->status
                : AttendanceStatus::from((string) $item->status);

            return [
                'id' => $item->id,
                'meeting_number' => $item->pertemuan_ke,
                'date' => optional($item->tanggal)->format('Y-m-d'),
                'date_label' => optional($item->tanggal)->translatedFormat('d M Y'),
                'status' => $status->value,
                'status_label' => $status->label(),
                'topic' => $item->topik,
                'notes' => $notes,
                'notes_count' => count($notes),
                'delete_url' => route('mata-kuliah.focus-attendance.destroy', [$mataKuliah, $item]),
            ];
        })->values();

        $tugasPayload = $tugas->map(function (Tugas $item) {
            $status = $this->normalizeStatusValue($item->status);
            $deadline = Carbon::parse($item->deadline);
            $deadlineOffset = now()->startOfDay()->diffInDays($deadline->copy()->startOfDay(), false);
            $attendanceStatus = $item->absensi?->status instanceof AttendanceStatus
                ? $item->absensi->status
                : ($item->absensi?->status ? AttendanceStatus::from((string) $item->absensi->status) : null);

            $todos = $item->todos->map(function (Todo $todo) {
                $status = $this->normalizeStatusValue($todo->status);
                $deadline = $todo->deadline ? Carbon::parse($todo->deadline) : null;

                return [
                    'id' => $todo->id,
                    'title' => $todo->judul,
                    'description' => $todo->deskripsi,
                    'status' => $status,
                    'status_label' => $this->statusLabel($status),
                    'deadline_label' => $deadline?->format('d M Y'),
                    'update_url' => route('todo.updateStatus', ['todo' => $todo->id]),
                ];
            })->values();

            $todoCompletedCount = $todos->where('status', Status::SELESAI->value)->count();

            return [
                'id' => $item->id,
                'title' => $item->judul,
                'description' => $item->deskripsi,
                'status' => $status,
                'status_label' => $this->statusLabel($status),
                'priority' => $item->prioritas ?? 'sedang',
                'progress' => (int) $item->progress,
                'deadline_sort' => $deadline->toDateString(),
                'deadline_label' => $deadline->format('d M Y'),
                'deadline_relative' => $this->deadlineRelativeLabel($deadlineOffset, $status),
                'is_overdue' => $deadlineOffset < 0 && $status !== Status::SELESAI->value,
                'is_due_soon' => $deadlineOffset >= 0 && $deadlineOffset <= 3 && $status !== Status::SELESAI->value,
                'note' => $item->catatan,
                'absensi_id' => $item->absensi_id,
                'attendance_id' => $item->absensi_id,
                'attendance_label' => $item->absensi
                    ? $this->attendanceSummaryLabel($item->absensi)
                    : null,
                'attendance_status' => $attendanceStatus?->value,
                'attendance_status_label' => $attendanceStatus?->label(),
                'attendance_date_label' => $item->absensi?->tanggal?->translatedFormat('d M Y'),
                'attendance_topic' => $item->absensi?->topik,
                'todo_count' => $todos->count(),
                'todo_completed_count' => $todoCompletedCount,
                'show_url' => route('tugas.show', $item),
                'edit_url' => route('tugas.edit', $item),
                'todos' => $todos,
            ];
        })->values();

        $absensiPayload = $absensiPayload->map(function (array $attendance) use ($tugasPayload) {
            $attendance['linked_task_count'] = $tugasPayload
                ->where('absensi_id', $attendance['id'])
                ->count();

            return $attendance;
        })->values();

        $totalTugas = $tugasPayload->count();
        $tugasAktif = $tugasPayload->whereIn('status', [Status::BELUM->value, Status::PROGRESS->value])->count();
        $tugasSelesai = $tugasPayload->where('status', Status::SELESAI->value)->count();
        $tugasMendekat = $tugasPayload->where('is_due_soon', true)->count();
        $rataRataProgress = (int) round($tugas->avg('progress') ?? 0);
        $totalTodo = $tugasPayload->sum('todo_count');
        $todoSelesai = $tugasPayload->sum('todo_completed_count');

        $nextDeadlineTask = $tugasPayload
            ->whereIn('status', [Status::BELUM->value, Status::PROGRESS->value])
            ->sortBy('deadline_sort')
            ->first();

        $totalAbsensi = $absensiPayload->count();
        $hadirCount = $absensiPayload->where('status', AttendanceStatus::HADIR->value)->count();
        $izinSakitCount = $absensiPayload->whereIn('status', [AttendanceStatus::IZIN->value, AttendanceStatus::SAKIT->value])->count();
        $alphaCount = $absensiPayload->where('status', AttendanceStatus::ALPHA->value)->count();
        $persentaseKehadiran = $totalAbsensi > 0
            ? (int) round(($hadirCount / $totalAbsensi) * 100)
            : 0;
        $totalCatatanAbsensi = $absensiPayload->sum('notes_count');

        $initialTaskId = old('tugas_id')
            ?: session('focus_task_id')
            ?: data_get($tugasPayload->first(), 'id');
        $initialAbsensiId = old('task_absensi_id')
            ?: old('absensi_id')
            ?: session('focus_absensi_id')
            ?: data_get($absensiPayload->first(), 'id');

        return view('mata-kuliah.show', compact(
            'mataKuliah',
            'durasiKuliah',
            'absensiPayload',
            'tugasPayload',
            'totalAbsensi',
            'hadirCount',
            'izinSakitCount',
            'alphaCount',
            'persentaseKehadiran',
            'totalCatatanAbsensi',
            'totalTugas',
            'tugasAktif',
            'tugasSelesai',
            'tugasMendekat',
            'rataRataProgress',
            'totalTodo',
            'todoSelesai',
            'nextDeadlineTask',
            'initialTaskId',
            'initialAbsensiId'
        ));
    }

    public function create()
    {
        return view('mata-kuliah.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:20|unique:mata_kuliahs,kode',
            'nama' => 'required|string|max:100',
            'sks' => 'nullable|integer|min:1|max:4',
            'kelas' => 'nullable|string|max:10',
            'dosen' => 'required|string|max:100',
            'ruangan' => 'required|string|max:50',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'lms' => 'nullable|string|max:50',
            'lms_link' => 'nullable|string|max:255',
            'semester' => 'nullable|integer|min:1|max:8',
            'tahun_ajaran' => 'nullable|integer|min:2020|max:2100',
            'warna' => 'nullable|string|max:20',
            'catatan' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        MataKuliah::create($validated);

        return redirect()->route('mata-kuliah.index')
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function edit(MataKuliah $mataKuliah)
    {
        return view('mata-kuliah.edit', compact('mataKuliah'));
    }

    public function update(Request $request, MataKuliah $mataKuliah)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:20|unique:mata_kuliahs,kode,' . $mataKuliah->id,
            'nama' => 'required|string|max:100',
            'sks' => 'nullable|integer|min:1|max:4',
            'kelas' => 'nullable|string|max:10',
            'dosen' => 'required|string|max:100',
            'ruangan' => 'required|string|max:50',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'lms' => 'nullable|string|max:50',
            'lms_link' => 'nullable|string|max:255',
            'semester' => 'nullable|integer|min:1|max:8',
            'tahun_ajaran' => 'nullable|integer|min:2020|max:2100',
            'warna' => 'nullable|string|max:20',
            'catatan' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        $mataKuliah->update($validated);

        return redirect()->route('mata-kuliah.index')
            ->with('success', 'Mata kuliah berhasil diupdate.');
    }

    public function destroy(MataKuliah $mataKuliah)
    {
        $mataKuliah->delete();
        return redirect()->route('mata-kuliah.index')
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'bulk_action' => ['required', 'string'],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:mata_kuliahs,id'],
            ]);


        $action = $request->bulk_action;
        $ids = $request->ids;

        if ($action === 'delete') {
            MataKuliah::whereIn('id', $ids)->delete();

            return back()->with('success', 'Data terpilih berhasil dihapus.');
        }

        if ($action === 'set_senin') {
            MataKuliah::whereIn('id', $ids)->update(['hari' => DayOfWeek::MONDAY->value]);

            return back()->with('success', 'Hari berhasil diubah ke Senin.');
        }

        return back()->with('error', 'Bulk action tidak dikenali.');
    }

    public function saveFocusAttendance(Request $request, MataKuliah $mataKuliah)
    {
        $validated = $request->validateWithBag('attendanceManager', [
            'absensi_id' => ['nullable', 'integer', Rule::exists('absensis', 'id')],
            'tanggal' => ['required', 'date'],
            'pertemuan_ke' => ['nullable', 'integer', 'min:1', 'max:32'],
            'status' => ['required', Rule::in(AttendanceStatus::list())],
            'topik' => ['nullable', 'string', 'max:255'],
        ]);

        $absensi = !empty($validated['absensi_id'])
            ? $this->resolveAbsensiForCourse($mataKuliah, (int) $validated['absensi_id'])
            : new Absensi([
                'mata_kuliah_id' => $mataKuliah->id,
            ]);

        $absensi->fill([
            'tanggal' => $validated['tanggal'],
            'pertemuan_ke' => $validated['pertemuan_ke'] ?? null,
            'status' => $validated['status'],
            'topik' => $validated['topik'] ?? null,
        ]);
        $absensi->mata_kuliah_id = $mataKuliah->id;
        $absensi->save();

        $message = !empty($validated['absensi_id'])
            ? 'Data absensi berhasil diperbarui.'
            : 'Data absensi berhasil ditambahkan.';

        return redirect()->route('mata-kuliah.show', $mataKuliah)
            ->with('focus_absensi_id', $absensi->id)
            ->with('success', $message);
    }

    public function updateFocusAttendanceNotes(Request $request, MataKuliah $mataKuliah)
    {
        $validated = $request->validateWithBag('attendanceNotes', [
            'absensi_id' => ['required', 'integer', Rule::exists('absensis', 'id')],
            'catatan' => ['nullable', 'array'],
            'catatan.*.judul' => ['nullable', 'string', 'max:120'],
            'catatan.*.isi' => ['nullable', 'string'],
        ]);

        $absensi = $this->resolveAbsensiForCourse($mataKuliah, (int) $validated['absensi_id']);

        $absensi->update([
            'catatan' => $this->sanitizeAttendanceNotes($validated['catatan'] ?? []),
        ]);

        return redirect()->route('mata-kuliah.show', $mataKuliah)
            ->with('focus_absensi_id', $absensi->id)
            ->with('success', 'Catatan absensi berhasil disimpan.');
    }

    public function destroyFocusAttendance(MataKuliah $mataKuliah, Absensi $absensi)
    {
        $this->ensureAbsensiBelongsToCourse($mataKuliah, $absensi);

        $nextAbsensiId = $mataKuliah->absensis()
            ->whereKeyNot($absensi->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('pertemuan_ke')
            ->value('id');

        $absensi->delete();

        return redirect()->route('mata-kuliah.show', $mataKuliah)
            ->with('focus_absensi_id', $nextAbsensiId)
            ->with('success', 'Data absensi berhasil dihapus.');
    }

    public function storeFocusTask(Request $request, MataKuliah $mataKuliah)
    {
        $validated = $request->validateWithBag('quickTask', [
            'task_absensi_id' => ['nullable', 'integer', Rule::exists('absensis', 'id')],
            'task_judul' => ['required', 'string', 'max:255'],
            'task_deskripsi' => ['nullable', 'string'],
            'task_deadline' => ['required', 'date', 'after_or_equal:today'],
            'task_prioritas' => ['required', Rule::in(['rendah', 'sedang', 'tinggi'])],
            'task_catatan' => ['nullable', 'string'],
        ]);

        $absensiId = null;

        if (!empty($validated['task_absensi_id'])) {
            $absensiId = $this->resolveAbsensiForCourse($mataKuliah, (int) $validated['task_absensi_id'])->id;
        }

        $tugas = Tugas::create([
            'user_id' => auth()->id(),
            'mata_kuliah_id' => $mataKuliah->id,
            'absensi_id' => $absensiId,
            'judul' => $validated['task_judul'],
            'deskripsi' => $validated['task_deskripsi'] ?? null,
            'deadline' => Carbon::parse($validated['task_deadline'])->endOfDay(),
            'status' => Status::BELUM->value,
            'progress' => 0,
            'prioritas' => $validated['task_prioritas'],
            'catatan' => $validated['task_catatan'] ?? null,
        ]);

        return redirect()->route('mata-kuliah.show', $mataKuliah)
            ->with('focus_task_id', $tugas->id)
            ->with('focus_absensi_id', $absensiId)
            ->with('success', 'Tugas baru berhasil ditambahkan dari mode fokus.');
    }

    public function storeFocusTodo(Request $request, MataKuliah $mataKuliah)
    {
        $validated = $request->validateWithBag('quickTodo', [
            'tugas_id' => ['required', 'integer', Rule::exists('tugas', 'id')],
            'todo_judul' => ['required', 'string', 'max:255'],
            'todo_deskripsi' => ['nullable', 'string'],
            'todo_deadline' => ['nullable', 'date'],
        ]);

        $tugas = Tugas::query()
            ->where('user_id', auth()->id())
            ->where('mata_kuliah_id', $mataKuliah->id)
            ->findOrFail($validated['tugas_id']);

        $tugas->todos()->create([
            'judul' => $validated['todo_judul'],
            'deskripsi' => $validated['todo_deskripsi'] ?? null,
            'status' => Status::BELUM->value,
            'deadline' => !empty($validated['todo_deadline'])
                ? Carbon::parse($validated['todo_deadline'])->endOfDay()
                : $tugas->deadline,
        ]);

        $this->syncTugasProgressFromTodos($tugas);

        return redirect()->route('mata-kuliah.show', $mataKuliah)
            ->with('focus_task_id', $tugas->id)
            ->with('focus_absensi_id', $tugas->absensi_id)
            ->with('success', 'Checklist tugas berhasil ditambahkan.');
    }

    private function syncTugasProgressFromTodos(Tugas $tugas): void
    {
        $total = $tugas->todos()->count();
        $done = $tugas->todos()->whereIn('status', [Status::SELESAI->value, 'SELESAI'])->count();

        $progress = $total > 0 ? (int) round(($done / $total) * 100) : 0;
        $status = $progress >= 100
            ? Status::SELESAI->value
            : ($progress > 0 ? Status::PROGRESS->value : Status::BELUM->value);

        $tugas->update([
            'progress' => $progress,
            'status' => $status,
        ]);
    }

    private function normalizeStatusValue(mixed $status): string
    {
        if ($status instanceof Status) {
            return $status->value;
        }

        $value = strtoupper((string) $status);

        return match ($value) {
            'PENDING' => Status::BELUM->value,
            'DONE', 'COMPLETED', 'COMPLETE' => Status::SELESAI->value,
            'IN_PROGRESS' => Status::PROGRESS->value,
            default => $value,
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            Status::SELESAI->value => 'Selesai',
            Status::PROGRESS->value => 'Progress',
            Status::BELUM->value => 'Belum',
            default => ucfirst(strtolower($status)),
        };
    }

    private function deadlineRelativeLabel(int $deadlineOffset, string $status): string
    {
        if ($status === Status::SELESAI->value) {
            return 'Sudah selesai';
        }

        if ($deadlineOffset < 0) {
            return 'Terlambat ' . abs($deadlineOffset) . ' hari';
        }

        if ($deadlineOffset === 0) {
            return 'Deadline hari ini';
        }

        return $deadlineOffset . ' hari lagi';
    }

    private function resolveAbsensiForCourse(MataKuliah $mataKuliah, int $absensiId): Absensi
    {
        return $mataKuliah->absensis()->findOrFail($absensiId);
    }

    private function ensureAbsensiBelongsToCourse(MataKuliah $mataKuliah, Absensi $absensi): void
    {
        if ((int) $absensi->mata_kuliah_id !== (int) $mataKuliah->id) {
            abort(404);
        }
    }

    private function normalizeAttendanceNotes(mixed $notes): array
    {
        return $this->sanitizeAttendanceNotes(is_array($notes) ? $notes : []);
    }

    private function attendanceSummaryLabel(Absensi $absensi): string
    {
        $date = optional($absensi->tanggal)->translatedFormat('d M');

        if ($absensi->pertemuan_ke) {
            return 'Pertemuan ' . $absensi->pertemuan_ke . ($date ? ' • ' . $date : '');
        }

        return $date ?: 'Pertemuan terkait';
    }

    private function sanitizeAttendanceNotes(array $notes): array
    {
        return collect($notes)
            ->map(function ($note) {
                $title = trim((string) data_get($note, 'judul', ''));
                $content = trim((string) data_get($note, 'isi', ''));

                return [
                    'judul' => $title,
                    'isi' => $content,
                ];
            })
            ->filter(fn(array $note) => $note['judul'] !== '' || $note['isi'] !== '')
            ->values()
            ->all();
    }
}
