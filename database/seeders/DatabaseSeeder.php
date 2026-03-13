<?php

namespace Database\Seeders;

use App\Models\Absensi;
use App\Models\User;
use App\Models\MataKuliah;
use App\Models\Tugas;
use App\Models\Reminder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();


        User::factory()->create([
            'name' => 'Ridho Saputra',
            'email' => 'ridho@gmail.com',
            'nim' => '222140'
        ])->each(function ($user) {
            $mataKuliahs = MataKuliah::factory(3)->create();
            foreach ($mataKuliahs as $mk) {
                Absensi::factory(4)->create([
                    'mata_kuliah_id' => $mk->id,
                ]);

                $tugasList = Tugas::factory(2)->create([
                    'user_id' => $user->id,
                    'mata_kuliah_id' => $mk->id,
                ]);
                foreach ($tugasList as $tugas) {
                    Reminder::factory(1)->create([
                        'tugas_id' => $tugas->id,
                    ]);
                }
            }
        });

    }
}
