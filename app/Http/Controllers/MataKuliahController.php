<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use Illuminate\Http\Request;

class MataKuliahController extends Controller
{
    public function index(Request $request)
    {
        $query = MataKuliah::query();

        if ($request->filled('hari')) {
            $query->where('hari', $request->hari);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', "%{$request->search}%")
                  ->orWhere('kode', 'like', "%{$request->search}%")
                  ->orWhere('dosen', 'like', "%{$request->search}%");
            });
        }

        $mataKuliah = $query->orderByRaw("FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')")
            ->orderBy('jam_mulai')
            ->paginate(15);

        return view('mata-kuliah.index', compact('mataKuliah'));
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
}
