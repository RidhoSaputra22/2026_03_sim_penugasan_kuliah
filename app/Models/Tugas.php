<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tugas extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'mata_kuliah_id', 'judul', 'deskripsi', 'deadline', 'status', 'progress',
        'prioritas', 'file', 'catatan'
    ];

    protected $casts = [
        'status' => \App\Enums\Status::class,
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    public function todos()
    {
        return $this->hasMany(Todo::class);
    }
}
