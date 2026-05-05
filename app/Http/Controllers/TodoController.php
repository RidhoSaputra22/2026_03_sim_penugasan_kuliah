<?php

namespace App\Http\Controllers;

use App\Enums\Status;
use App\Models\Todo;
use App\Models\Tugas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Jika ada tugas_id, filter todo berdasarkan tugas
        $tugasId = $request->query('tugas_id');
        if ($tugasId) {
            $todos = Todo::where('tugas_id', $tugasId)->get();
        } else {
            $todos = Todo::all();
        }
        return view('todo.index', compact('todos', 'tugasId'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Bisa menerima tugas_id untuk preselect
        $tugasId = $request->query('tugas_id');
        $tugasList = Tugas::all();
        return view('todo.create', compact('tugasId', 'tugasList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->normalizeRequestEnums($request, [
            'status' => Status::class,
        ]);

        $validated = $request->validate([
            'tugas_id' => 'required|exists:tugas,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'status' => ['nullable', Rule::enum(Status::class)->only(Status::taskCases())],
            'deadline' => 'nullable|date',
            'file' => $this->todoAttachmentRules(),
        ]);
        $validated['status'] = $validated['status'] ?? Status::BELUM->value;

        if ($request->hasFile('file')) {
            $validated['file'] = $this->storeTodoAttachment($request, 'file');
        }

        $todo = Todo::create($validated);
        return redirect()->route('tugas.show', $todo->tugas_id)->with('success', 'Todo berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $todo = Todo::findOrFail($id);
        return view('todo.show', compact('todo'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $todo = Todo::findOrFail($id);
        $tugasList = Tugas::all();
        return view('todo.edit', compact('todo', 'tugasList'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $todo = Todo::findOrFail($id);
        $this->normalizeRequestEnums($request, [
            'status' => Status::class,
        ]);
        $validated = $request->validate([
            'tugas_id' => 'required|exists:tugas,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'status' => ['nullable', Rule::enum(Status::class)->only(Status::taskCases())],
            'deadline' => 'nullable|date',
            'file' => $this->todoAttachmentRules(),
        ]);
        $validated['status'] = $validated['status'] ?? Status::BELUM->value;

        if ($request->hasFile('file')) {
            $validated['file'] = $this->replaceTodoAttachment($request, 'file', $todo->file);
        }

        $todo->update($validated);
        return redirect()->route('tugas.show', $todo->tugas_id)->with('success', 'Todo berhasil diupdate!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $todo = Todo::findOrFail($id);
        $tugasId = $todo->tugas_id;
        $todo->deleteAttachment();
        $todo->delete();
        return redirect()->route('tugas.show', $tugasId)->with('success', 'Todo berhasil dihapus!');
    }

    private function todoAttachmentRules(): array
    {
        return ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif,bmp,avif', 'max:10240'];
    }

    private function storeTodoAttachment(Request $request, string $fieldName): string
    {
        return $request->file($fieldName)->store('todos', 'public');
    }

    private function replaceTodoAttachment(Request $request, string $fieldName, ?string $currentPath): string
    {
        $path = $this->storeTodoAttachment($request, $fieldName);

        if ($currentPath) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($currentPath);
        }

        return $path;
    }
}
