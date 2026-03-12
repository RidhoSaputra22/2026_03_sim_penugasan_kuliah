<?php
namespace Database\Factories;

use App\Models\MataKuliah;
use Illuminate\Database\Eloquent\Factories\Factory;

class MataKuliahFactory extends Factory
{
    protected $model = MataKuliah::class;

    public function definition(): array
    {
        return [
            'kode' => $this->faker->unique()->bothify('MK###'),
            'nama' => $this->faker->words(2, true),
            'sks' => $this->faker->numberBetween(1, 4),
            'kelas' => $this->faker->randomElement(['A', 'B', 'C']),
            'dosen' => $this->faker->name(),
            'ruangan' => $this->faker->bothify('Ruang-##'),
            'hari' => $this->faker->randomElement(\App\Enums\DayOfWeek::list()),
            'jam_mulai' => $this->faker->time('H:i'),
            'jam_selesai' => $this->faker->time('H:i'),
            'lms' => $this->faker->randomElement(['Moodle', 'Google Classroom', 'Edmodo']),
            'lms_link' => $this->faker->url(),
            'semester' => $this->faker->numberBetween(1, 8),
            'tahun_ajaran' => $this->faker->year(),
            'warna' => $this->faker->safeColorName(),
            'catatan' => $this->faker->sentence(),
            'is_active' => $this->faker->boolean(80),
        ];
    }
}
