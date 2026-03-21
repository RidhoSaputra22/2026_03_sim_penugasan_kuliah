<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\MataKuliah;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_search_returns_matching_navigation_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson(route('global-search', [
            'q' => 'Stat',
        ]));

        $response->assertOk()
            ->assertJsonFragment([
                'title' => 'Statistik',
                'url' => route('statistik.index'),
            ]);
    }

    public function test_global_search_returns_matching_course_results(): void
    {
        $user = User::factory()->create();

        $mataKuliah = MataKuliah::create([
            'kode' => 'IF501',
            'nama' => 'Statistik Komputasi',
            'dosen' => 'Dr. Miftahul Jannah',
            'ruangan' => 'B305',
            'hari' => 'Senin',
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:40',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->getJson(route('global-search', [
            'q' => 'IF501',
        ]));

        $response->assertOk()
            ->assertJsonFragment([
                'title' => $mataKuliah->nama,
                'url' => route('mata-kuliah.show', $mataKuliah),
            ]);
    }

    public function test_global_search_only_returns_the_authenticated_users_tasks_and_events(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $mataKuliah = MataKuliah::create([
            'kode' => 'IF502',
            'nama' => 'Rekayasa Pengujian',
            'dosen' => 'Rizky Mahendra',
            'ruangan' => 'Lab QA',
            'hari' => 'Selasa',
            'jam_mulai' => '10:00',
            'jam_selesai' => '11:40',
            'is_active' => true,
        ]);

        $ownTask = Tugas::create([
            'user_id' => $user->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'judul' => 'Strategi testing integrasi',
            'deskripsi' => 'Susun skenario pengujian untuk modul tugas.',
            'deadline' => now()->addDays(3),
            'status' => 'PROGRESS',
            'progress' => 55,
            'prioritas' => 'tinggi',
            'catatan' => 'Bahas di kelas berikutnya.',
        ]);

        Tugas::create([
            'user_id' => $otherUser->id,
            'mata_kuliah_id' => $mataKuliah->id,
            'judul' => 'Strategi testing rahasia',
            'deskripsi' => 'Tidak boleh muncul di hasil user lain.',
            'deadline' => now()->addDays(4),
            'status' => 'BELUM',
            'progress' => 0,
            'prioritas' => 'sedang',
            'catatan' => null,
        ]);

        $event = Event::create([
            'user_id' => $user->id,
            'title' => 'Strategi presentasi hasil testing',
            'description' => 'Latihan presentasi sebelum demo akhir.',
            'start' => now()->addDay(),
            'end' => now()->addDay()->addHour(),
            'location' => 'Ruang Sidang',
            'color' => 'info',
        ]);

        Event::create([
            'user_id' => $otherUser->id,
            'title' => 'Strategi milik user lain',
            'description' => 'Tidak boleh ikut muncul.',
            'start' => now()->addDays(2),
            'end' => now()->addDays(2)->addHour(),
            'location' => 'Private Room',
            'color' => 'warning',
        ]);

        $response = $this->actingAs($user)->getJson(route('global-search', [
            'q' => 'Strategi',
        ]));

        $response->assertOk()
            ->assertJsonFragment([
                'title' => $ownTask->judul,
                'url' => route('tugas.show', $ownTask),
            ])
            ->assertJsonFragment([
                'title' => $event->title,
                'url' => route('kalender.index'),
            ])
            ->assertJsonMissing([
                'title' => 'Strategi testing rahasia',
            ])
            ->assertJsonMissing([
                'title' => 'Strategi milik user lain',
            ]);
    }
}
