<?php
namespace App\Models;

use App\Casts\NormalizedEnumCast;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Tugas extends Model
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
        'user_id', 'mata_kuliah_id', 'absensi_id', 'judul', 'deskripsi', 'deadline', 'status', 'progress',
        'prioritas', 'file', 'catatan'
    ];

    protected $casts = [
        'status' => NormalizedEnumCast::class . ':' . Status::class,
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'mata_kuliah_id');
    }

    public function absensi()
    {
        return $this->belongsTo(Absensi::class, 'absensi_id');
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    public function todos()
    {
        return $this->hasMany(Todo::class);
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

    public function deleteTodoAttachments(): void
    {
        $todos = $this->relationLoaded('todos')
            ? $this->todos
            : $this->todos()->get(['id', 'tugas_id', 'file']);

        $todos->each(function (Todo $todo) {
            $todo->deleteAttachment();
        });
    }
}
