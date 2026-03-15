<?php
namespace App\Models;

use App\Casts\NormalizedEnumCast;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'tugas_id', 'judul', 'deskripsi', 'status', 'deadline'
    ];

    protected $casts = [
        'status' => NormalizedEnumCast::class . ':' . Status::class,
    ];


    public function tugas()
    {
        return $this->belongsTo(Tugas::class);
    }
}
