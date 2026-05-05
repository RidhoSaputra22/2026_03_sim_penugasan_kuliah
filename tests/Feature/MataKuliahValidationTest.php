<?php

namespace Tests\Feature;

use App\Enums\DayOfWeek;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class MataKuliahValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mata_kuliah_store_allows_long_lms_values(): void
    {
        $user = User::factory()->create();
        $longLms = 'https://divlearn.undipa.ac.id/course/view.php?id=955962&token=' . str_repeat('abc123xyz', 40);
        $longLmsLink = 'https://divlearn.undipa.ac.id/mod/resource/view.php?id=123456&redirect=' . str_repeat('materi-14-', 35);

        $response = $this->actingAs($user)->post(route('mata-kuliah.store'), [
            'kode' => 'IF901',
            'nama' => 'Pemodelan Simulasi',
            'sks' => 3,
            'kelas' => 'A',
            'dosen' => 'Dr. Rahmawati',
            'ruangan' => 'D301',
            'hari' => DayOfWeek::MONDAY->value,
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:40',
            'lms' => $longLms,
            'lms_link' => $longLmsLink,
            'semester' => 7,
            'tahun_ajaran' => 2026,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('mata-kuliah.index'));

        $this->assertDatabaseHas('mata_kuliahs', [
            'kode' => 'IF901',
            'lms' => $longLms,
            'lms_link' => $longLmsLink,
        ]);
    }

    public function test_mata_kuliah_validation_messages_are_translated_to_indonesian(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('mata-kuliah.create'))
            ->post(route('mata-kuliah.store'), [
                'kode' => '',
                'nama' => '',
                'dosen' => '',
                'ruangan' => '',
                'hari' => '',
                'jam_mulai' => '',
                'jam_selesai' => '',
            ]);

        $response->assertRedirect(route('mata-kuliah.create'));
        $response->assertSessionHasErrors(['kode', 'nama', 'dosen', 'ruangan', 'hari', 'jam_mulai', 'jam_selesai']);
        $response->assertSessionHas('errors', function (ViewErrorBag $errors): bool {
            return $errors->first('kode') === 'Kolom kode mata kuliah wajib diisi.'
                && $errors->first('jam_mulai') === 'Kolom jam mulai wajib diisi.'
                && $errors->first('hari') === 'Kolom hari wajib diisi.';
        });
    }
}
