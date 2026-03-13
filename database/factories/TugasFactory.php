<?php
namespace Database\Factories;

use App\Models\Tugas;
use App\Models\User;
use App\Models\MataKuliah;
use Illuminate\Database\Eloquent\Factories\Factory;

class TugasFactory extends Factory
{
    protected $model = Tugas::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'mata_kuliah_id' => MataKuliah::factory(),
            'absensi_id' => null,
            'judul' => $this->faker->sentence(3),
            'deskripsi' => $this->faker->paragraph(),
            'deadline' => $this->faker->dateTimeBetween('+1 days', '+1 month'),
            'status' => $this->faker->randomElement([\App\Enums\Status::BELUM, \App\Enums\Status::PROGRESS, \App\Enums\Status::SELESAI]),
            'progress' => $this->faker->numberBetween(0, 100),
            'prioritas' => $this->faker->randomElement(['rendah', 'sedang', 'tinggi']),
            'file' => $this->faker->optional()->url(),
            'catatan' => $this->faker->optional()->sentence(),
        ];
    }
}
