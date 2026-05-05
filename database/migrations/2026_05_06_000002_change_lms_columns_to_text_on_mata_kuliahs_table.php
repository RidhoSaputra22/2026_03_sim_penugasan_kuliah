<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mata_kuliahs', function (Blueprint $table) {
            $table->text('lms')->nullable()->change();
            $table->text('lms_link')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('mata_kuliahs', function (Blueprint $table) {
            $table->string('lms')->nullable()->change();
            $table->string('lms_link')->nullable()->change();
        });
    }
};
