<?php

namespace App\Http\Controllers;

use App\Enums\DayOfWeek;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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
            $mulai = Carbon::parse($mk->jam_mulai);
            $selesai = Carbon::parse($mk->jam_selesai);
            return $mulai->diffInMinutes($selesai);
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
        // dd($request->all());


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
}
