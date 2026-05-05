<?php

namespace Tests\Feature;

use App\Enums\Status;
use App\Models\MataKuliah;
use App\Models\Todo;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TodoAttachmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_store_todo_with_photo(): void
    {
        Storage::fake('public');

        [$user, $tugas] = $this->makeUserAndTask();

        $response = $this->actingAs($user)->post(route('todo.store'), [
            'tugas_id' => $tugas->id,
            'judul' => 'Foto hasil eksperimen',
            'deskripsi' => 'Simpan dokumentasi visual untuk checklist.',
            'status' => Status::BELUM->value,
            'deadline' => now()->addDay()->toDateTimeString(),
            'file' => UploadedFile::fake()->image('bukti-checklist.jpg'),
        ]);

        $response->assertRedirect(route('tugas.show', $tugas));

        $todo = Todo::query()->firstOrFail();

        $this->assertNotNull($todo->file);
        Storage::disk('public')->assertExists($todo->file);
        $this->assertStringStartsWith('/storage/todos/', $todo->attachmentUrl());
    }

    public function test_user_can_upload_and_replace_focus_todo_attachment(): void
    {
        Storage::fake('public');

        [$user, $tugas, $mataKuliah] = $this->makeUserAndTask(includeCourse: true);

        $createResponse = $this->actingAs($user)->post(route('mata-kuliah.focus-todo', $mataKuliah), [
            'tugas_id' => $tugas->id,
            'todo_judul' => 'Upload foto draft',
            'todo_deskripsi' => 'Checklist visual awal.',
            'todo_deadline' => now()->addDays(2)->toDateString(),
            'todo_file' => UploadedFile::fake()->image('draft-awal.jpg'),
        ]);

        $createResponse->assertRedirect(route('mata-kuliah.show', $mataKuliah));

        $todo = Todo::query()->firstOrFail();
        $firstPath = $todo->file;

        $this->assertNotNull($firstPath);
        Storage::disk('public')->assertExists($firstPath);

        $updateResponse = $this->actingAs($user)->post(
            route('mata-kuliah.focus-todo.update', [$mataKuliah, $todo]),
            [
                '_method' => 'PUT',
                'todo_judul' => 'Upload foto draft revisi',
                'todo_deskripsi' => 'Checklist visual setelah revisi.',
                'todo_deadline' => now()->addDays(3)->toDateString(),
                'todo_file' => UploadedFile::fake()->image('draft-revisi.jpg'),
            ]
        );

        $updateResponse->assertRedirect(route('mata-kuliah.show', $mataKuliah));

        $todo->refresh();

        $this->assertNotNull($todo->file);
        $this->assertNotSame($firstPath, $todo->file);
        Storage::disk('public')->assertMissing($firstPath);
        Storage::disk('public')->assertExists($todo->file);
        $this->assertStringStartsWith('/storage/todos/', $todo->attachmentUrl());
    }

    private function makeUserAndTask(bool $includeCourse = false): array
    {
        $user = User::factory()->create();

        $mataKuliah = MataKuliah::create([
            'kode' => $includeCourse ? 'IF601' : 'IF602',
            'nama' => $includeCourse ? 'Machine Learning' : 'Grafika Komputer',
            'dosen' => 'Rahmat Hidayat',
            'ruangan' => 'Lab 1',
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:40',
            'is_active' => true,
        ]);

        $tugas = Tugas::create([
            'user_id' => $user->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'judul' => 'Tugas dengan checklist foto',
            'deskripsi' => 'Cek dukungan lampiran pada checklist.',
            'deadline' => now()->addDays(5),
            'status' => Status::BELUM->value,
            'progress' => 0,
            'prioritas' => 'sedang',
        ]);

        return $includeCourse
            ? [$user, $tugas, $mataKuliah]
            : [$user, $tugas];
    }
}
