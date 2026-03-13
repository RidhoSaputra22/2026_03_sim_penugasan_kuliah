<?php

namespace App\Models;

use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'mata_kuliah_id',
        'tanggal',
        'pertemuan_ke',
        'status',
        'topik',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'status' => AttendanceStatus::class,
            'catatan' => 'array',
        ];
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function tugas()
    {
        return $this->hasMany(Tugas::class, 'absensi_id');
    }
}
