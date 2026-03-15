<?php
namespace App\Models;

use App\Casts\NormalizedEnumCast;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;
    protected $fillable = [
        'tugas_id', 'tanggal_notifikasi', 'status', 'terkirim'
    ];

    protected $casts = [
        'status'=> NormalizedEnumCast::class . ':' . Status::class,
    ];

    public function tugas()
    {
        return $this->belongsTo(Tugas::class);
    }
}
