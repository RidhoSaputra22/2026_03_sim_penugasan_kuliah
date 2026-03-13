<?php

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\Absensi;
use App\Models\MataKuliah;
use Illuminate\Database\Eloquent\Factories\Factory;

class AbsensiFactory extends Factory
{
    protected $model = Absensi::class;

    public function definition(): array
    {
        $pertemuanKe = $this->faker->numberBetween(1, 14);

        return [
            'mata_kuliah_id' => MataKuliah::factory(),
            'tanggal' => $this->faker->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'pertemuan_ke' => $pertemuanKe,
            'status' => $this->faker->randomElement(AttendanceStatus::list()),
            'topik' => 'Pertemuan ' . $pertemuanKe . ' - ' . $this->faker->sentence(3),
            'catatan' => [
                [
                    'judul' => 'Highlight kelas',
                    'isi' => $this->faker->sentence(),
                ],
            ],
        ];
    }
}
