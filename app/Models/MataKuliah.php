<?php
namespace App\Models;

use App\Casts\NormalizedEnumCast;
use App\Enums\DayOfWeek;
use App\Support\ScheduleTime;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use HasFactory;
    protected $fillable = [
        'kode', 'nama', 'sks', 'kelas', 'dosen', 'ruangan', 'hari', 'jam_mulai', 'jam_selesai',
        'lms', 'lms_link', 'semester', 'tahun_ajaran', 'warna', 'catatan', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'hari' => NormalizedEnumCast::class . ':' . DayOfWeek::class,
            'is_active' => 'boolean',
        ];
    }

    protected function jamMulai(): Attribute
    {
        return Attribute::make(
            get: fn($value) => ScheduleTime::format($value) ?? (is_string($value) ? trim($value) : null),
            set: fn($value) => ScheduleTime::normalize($value) ?? $value,
        );
    }

    protected function jamSelesai(): Attribute
    {
        return Attribute::make(
            get: fn($value) => ScheduleTime::format($value) ?? (is_string($value) ? trim($value) : null),
            set: fn($value) => ScheduleTime::normalize($value) ?? $value,
        );
    }

    protected function durasiMenit(): Attribute
    {
        return Attribute::make(
            get: fn($_, array $attributes) => ScheduleTime::diffInMinutes(
                $attributes['jam_mulai'] ?? null,
                $attributes['jam_selesai'] ?? null
            ),
        );
    }

    protected function durasiKuliahLabel(): Attribute
    {
        return Attribute::make(
            get: fn($_, array $attributes) => ScheduleTime::humanizeDuration(
                $attributes['jam_mulai'] ?? null,
                $attributes['jam_selesai'] ?? null
            ),
        );
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class, 'mata_kuliah_id');
    }

    public function absensis()
    {
        return $this->hasMany(Absensi::class, 'mata_kuliah_id');
    }
}
