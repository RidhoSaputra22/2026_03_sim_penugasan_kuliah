<?php

use App\Enums\AttendanceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_kuliah_id')->constrained('mata_kuliahs')->cascadeOnDelete();
            $table->date('tanggal');
            $table->unsignedSmallInteger('pertemuan_ke')->nullable();
            $table->enum('status', AttendanceStatus::list())->default(AttendanceStatus::HADIR->value);
            $table->string('topik')->nullable();
            $table->json('catatan')->nullable();
            $table->timestamps();

            $table->index(['mata_kuliah_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensis');
    }
};
