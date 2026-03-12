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
        Schema::table('tugas', function (Blueprint $table) {
            $table->enum('prioritas', [
                'rendah',
                'sedang',
                'tinggi'
            ])->default('sedang')->after('status');
            $table->string('file')->nullable()->after('deadline');
            $table->text('catatan')->nullable()->after('file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tugas', function (Blueprint $table) {
            $table->dropColumn([
                'prioritas',
                'file',
                'catatan'
            ]);
        });
    }
};
