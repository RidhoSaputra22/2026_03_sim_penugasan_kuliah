<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mata_kuliahs', function (Blueprint $table) {
            $table->integer('sks')->nullable()->after('nama');
            $table->string('kelas')->nullable()->after('sks');
            $table->string('lms')->nullable()->after('jam_selesai');
            $table->string('lms_link')->nullable()->after('lms');
            $table->integer('semester')->nullable()->after('lms_link');
            $table->year('tahun_ajaran')->nullable()->after('semester');
            $table->string('warna')->nullable()->after('tahun_ajaran');
            $table->text('catatan')->nullable()->after('warna');
            $table->boolean('is_active')->default(true)->after('catatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mata_kuliahs', function (Blueprint $table) {
            $table->dropColumn([
                'sks',
                'kelas',
                'lms',
                'lms_link',
                'semester',
                'tahun_ajaran',
                'warna',
                'catatan',
                'is_active'
            ]);
        });
    }
};
