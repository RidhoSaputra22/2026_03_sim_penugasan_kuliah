<?php

namespace Tests\Feature;

use App\Enums\DayOfWeek;
use App\Enums\Status;
use App\Models\MataKuliah;
use App\Models\Todo;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnumConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_mata_kuliah_hari_is_cast_to_day_of_week_enum(): void
    {
        $mataKuliah = MataKuliah::create([
            'kode' => 'IF501',
            'nama' => 'Sistem Operasi',
            'dosen' => 'Nur Hidayah',
            'ruangan' => 'D201',
            'hari' => DayOfWeek::MONDAY->value,
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:40',
            'is_active' => true,
        ]);

        $this->assertSame(DayOfWeek::MONDAY, $mataKuliah->fresh()->hari);
    }

    public function test_todo_store_defaults_to_status_enum_value(): void
    {
        $user = User::factory()->create();

        $mataKuliah = MataKuliah::create([
            'kode' => 'IF502',
            'nama' => 'Jaringan Komputer',
            'dosen' => 'Ahmad Nur',
            'ruangan' => 'Lab Jaringan',
            'hari' => DayOfWeek::TUESDAY->value,
            'jam_mulai' => '10:00',
            'jam_selesai' => '11:40',
            'is_active' => true,
        ]);

        $tugas = Tugas::create([
            'user_id' => $user->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'judul' => 'Konfigurasi router',
            'deskripsi' => 'Siapkan topologi dan konfigurasi dasar.',
            'deadline' => now()->addDays(3),
            'status' => Status::BELUM->value,
            'progress' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('todo.store'), [
            'tugas_id' => $tugas->id,
            'judul' => 'Atur alamat IP',
            'deskripsi' => 'Gunakan skema alamat private.',
            'deadline' => now()->addDay()->toDateTimeString(),
        ]);

        $response->assertRedirect(route('tugas.show', $tugas));

        $todo = Todo::query()->first();

        $this->assertNotNull($todo);
        $this->assertSame(Status::BELUM, $todo->status);
    }

    public function test_mata_kuliah_store_normalizes_uppercase_day_input(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('mata-kuliah.store'), [
            'kode' => 'IF503',
            'nama' => 'Keamanan Informasi',
            'sks' => 3,
            'kelas' => 'A',
            'dosen' => 'Muhammad Fajar',
            'ruangan' => 'E101',
            'hari' => 'SENIN',
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:40',
            'semester' => 5,
            'tahun_ajaran' => 2026,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('mata-kuliah.index'));

        $this->assertDatabaseHas('mata_kuliahs', [
            'kode' => 'IF503',
            'hari' => DayOfWeek::MONDAY->value,
        ]);
    }

    public function test_focus_attendance_store_normalizes_uppercase_status_input(): void
    {
        $user = User::factory()->create();

        $mataKuliah = MataKuliah::create([
            'kode' => 'IF504',
            'nama' => 'Analisis Algoritma',
            'dosen' => 'Dewi Sartika',
            'ruangan' => 'F202',
            'hari' => DayOfWeek::WEDNESDAY->value,
            'jam_mulai' => '13:00',
            'jam_selesai' => '14:40',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('mata-kuliah.focus-attendance.save', $mataKuliah), [
            'tanggal' => now()->toDateString(),
            'pertemuan_ke' => 1,
            'status' => 'HADIR',
            'topik' => 'Kompleksitas waktu',
        ]);

        $response->assertRedirect(route('mata-kuliah.show', $mataKuliah));

        $this->assertSame('hadir', $mataKuliah->absensis()->firstOrFail()->getRawOriginal('status'));
    }

    public function test_todo_store_normalizes_lowercase_status_input(): void
    {
        $user = User::factory()->create();

        $mataKuliah = MataKuliah::create([
            'kode' => 'IF505',
            'nama' => 'Pemrosesan Citra',
            'dosen' => 'Ira Kusuma',
            'ruangan' => 'Lab AI',
            'hari' => DayOfWeek::THURSDAY->value,
            'jam_mulai' => '10:00',
            'jam_selesai' => '11:40',
            'is_active' => true,
        ]);

        $tugas = Tugas::create([
            'user_id' => $user->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'judul' => 'Implementasi edge detection',
            'deadline' => now()->addDays(4),
            'status' => Status::BELUM->value,
            'progress' => 0,
        ]);

        $response = $this->actingAs($user)->post(route('todo.store'), [
            'tugas_id' => $tugas->id,
            'judul' => 'Siapkan dataset',
            'status' => 'selesai',
        ]);

        $response->assertRedirect(route('tugas.show', $tugas));

        $this->assertSame(Status::SELESAI, Todo::query()->firstOrFail()->status);
    }
}
