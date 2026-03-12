<?php
namespace Database\Factories;

use App\Models\Reminder;
use App\Models\Tugas;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReminderFactory extends Factory
{
    protected $model = Reminder::class;

    public function definition(): array
    {
        return [
            'tugas_id' => Tugas::factory(),
            'tanggal_notifikasi' => $this->faker->dateTimeBetween('now', '+1 month'),
            'status' => $this->faker->randomElement([\App\Enums\Status::BELUM, \App\Enums\Status::SELESAI]),
            'terkirim' => $this->faker->boolean(20),
        ];
    }
}
