<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->foreignId('absensi_id')
                ->nullable()
                ->after('mata_kuliah_id')
                ->constrained('absensis')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('absensi_id');
        });
    }
};
