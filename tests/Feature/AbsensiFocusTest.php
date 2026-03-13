<?php

namespace Tests\Feature;

use App\Enums\AttendanceStatus;
use App\Models\Absensi;
use App\Models\MataKuliah;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsensiFocusTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_store_absensi_and_notes_from_focus_mode(): void
    {
        $user = User::factory()->create();

        $mataKuliah = MataKuliah::create([
            'kode' => 'IF401',
            'nama' => 'Interaksi Manusia Komputer',
            'dosen' => 'Dr. Rahmawati',
            'ruangan' => 'Lab 2',
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:40',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('mata-kuliah.focus-attendance.save', $mataKuliah), [
            'tanggal' => now()->toDateString(),
            'pertemuan_ke' => 4,
            'status' => AttendanceStatus::HADIR->value,
            'topik' => 'Review prototipe antarmuka',
        ]);

        $response->assertRedirect(route('mata-kuliah.show', $mataKuliah));

        $absensi = Absensi::query()->first();

        $this->assertNotNull($absensi);
        $this->assertSame($mataKuliah->id, $absensi->mata_kuliah_id);
        $this->assertSame(AttendanceStatus::HADIR, $absensi->status);
        $this->assertSame(4, $absensi->pertemuan_ke);

        $response = $this->actingAs($user)->put(route('mata-kuliah.focus-attendance-notes.update', $mataKuliah), [
            'absensi_id' => $absensi->id,
            'catatan' => [
                [
                    'judul' => 'Highlight kelas',
                    'isi' => 'Bahas struktur wireframe final.',
                ],
                [
                    'judul' => '',
                    'isi' => 'Siapkan bahan presentasi untuk minggu depan.',
                ],
            ],
        ]);

        $response->assertRedirect(route('mata-kuliah.show', $mataKuliah));

        $absensi->refresh();

        $this->assertIsArray($absensi->catatan);
        $this->assertCount(2, $absensi->catatan);
        $this->assertSame('Highlight kelas', $absensi->catatan[0]['judul']);
        $this->assertSame('Siapkan bahan presentasi untuk minggu depan.', $absensi->catatan[1]['isi']);
    }

    public function test_user_can_create_focus_task_that_is_linked_to_selected_attendance(): void
    {
        $user = User::factory()->create();

        $mataKuliah = MataKuliah::create([
            'kode' => 'IF402',
            'nama' => 'Rekayasa Perangkat Lunak',
            'dosen' => 'Dr. Nanda Pratama',
            'ruangan' => 'B201',
            'hari' => 'Selasa',
            'jam_mulai' => '10:00',
            'jam_selesai' => '11:40',
            'is_active' => true,
        ]);

        $absensi = Absensi::create([
            'mata_kuliah_id' => $mataKuliah->id,
            'tanggal' => now()->toDateString(),
            'pertemuan_ke' => 5,
            'status' => AttendanceStatus::HADIR,
            'topik' => 'Review kebutuhan sistem',
            'catatan' => [],
        ]);

        $response = $this->actingAs($user)->post(route('mata-kuliah.focus-task', $mataKuliah), [
            'task_absensi_id' => $absensi->id,
            'task_judul' => 'Susun use case final',
            'task_deskripsi' => 'Turunkan kebutuhan dari hasil diskusi kelas.',
            'task_deadline' => now()->addDays(3)->toDateString(),
            'task_prioritas' => 'tinggi',
            'task_catatan' => 'Pastikan sinkron dengan catatan absensi.',
        ]);

        $response->assertRedirect(route('mata-kuliah.show', $mataKuliah));

        $tugas = Tugas::query()->first();

        $this->assertNotNull($tugas);
        $this->assertSame($mataKuliah->id, $tugas->mata_kuliah_id);
        $this->assertSame($absensi->id, $tugas->absensi_id);
        $this->assertSame($user->id, $tugas->user_id);
    }

    public function test_regular_task_form_rejects_attendance_from_other_course(): void
    {
        $user = User::factory()->create();

        $mataKuliahA = MataKuliah::create([
            'kode' => 'IF403',
            'nama' => 'Basis Data',
            'dosen' => 'Siti Lestari',
            'ruangan' => 'C103',
            'hari' => 'Rabu',
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:40',
            'is_active' => true,
        ]);

        $mataKuliahB = MataKuliah::create([
            'kode' => 'IF404',
            'nama' => 'Pemrograman Web',
            'dosen' => 'Rizki Saputra',
            'ruangan' => 'Lab Web',
            'hari' => 'Kamis',
            'jam_mulai' => '13:00',
            'jam_selesai' => '14:40',
            'is_active' => true,
        ]);

        $absensi = Absensi::create([
            'mata_kuliah_id' => $mataKuliahB->id,
            'tanggal' => now()->toDateString(),
            'pertemuan_ke' => 2,
            'status' => AttendanceStatus::HADIR,
            'topik' => 'Routing lanjutan',
            'catatan' => [],
        ]);

        $response = $this->actingAs($user)->from(route('tugas.create'))->post(route('tugas.store'), [
            'mata_kuliah_id' => $mataKuliahA->id,
            'absensi_id' => $absensi->id,
            'judul' => 'Tugas invalid',
            'deskripsi' => 'Harus gagal karena absensi beda mata kuliah.',
            'deadline' => now()->addDays(5)->toDateString(),
            'status' => 'BELUM',
            'progress' => 0,
            'prioritas' => 'sedang',
            'catatan' => null,
            'todos' => [],
        ]);

        $response->assertRedirect(route('tugas.create'));
        $response->assertSessionHasErrors('absensi_id');
        $this->assertDatabaseCount('tugas', 0);
    }
}
