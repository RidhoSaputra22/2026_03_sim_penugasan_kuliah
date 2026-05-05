<?php
namespace App\Models;

use App\Casts\NormalizedEnumCast;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Todo extends Model
{
    use HasFactory;

    private const IMAGE_EXTENSIONS = [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
        'bmp',
        'avif',
    ];

    protected $fillable = [
        'tugas_id', 'judul', 'deskripsi', 'status', 'deadline', 'file'
    ];

    protected $casts = [
        'status' => NormalizedEnumCast::class . ':' . Status::class,
    ];


    public function tugas()
    {
        return $this->belongsTo(Tugas::class);
    }

    public function attachmentUrl(): ?string
    {
        return $this->file ? Storage::disk('public')->url($this->file) : null;
    }

    public function attachmentName(): ?string
    {
        return $this->file ? basename($this->file) : null;
    }

    public function attachmentIsImage(): bool
    {
        if (!$this->file) {
            return false;
        }

        return in_array(strtolower(pathinfo($this->file, PATHINFO_EXTENSION)), self::IMAGE_EXTENSIONS, true);
    }

    public function deleteAttachment(): void
    {
        if ($this->file) {
            Storage::disk('public')->delete($this->file);
        }
    }
}
