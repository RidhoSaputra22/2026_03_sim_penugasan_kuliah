<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    use HasFactory;

    protected $fillable = [
        'tugas_id', 'judul', 'deskripsi', 'status', 'deadline'
    ];

    protected $casts = [
        'status' => \App\Enums\Status::class,
    ];


    public function tugas()
    {
        return $this->belongsTo(Tugas::class);
    }
}
