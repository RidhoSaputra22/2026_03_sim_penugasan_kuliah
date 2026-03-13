<?php
namespace App\Models;

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
            'is_active' => 'boolean',
        ];
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
