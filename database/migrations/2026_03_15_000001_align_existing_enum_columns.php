<?php

use App\Enums\DayOfWeek;
use App\Enums\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            UPDATE mata_kuliahs
            SET hari = CASE UPPER(hari)
                WHEN 'SENIN' THEN 'Senin'
                WHEN 'SELASA' THEN 'Selasa'
                WHEN 'RABU' THEN 'Rabu'
                WHEN 'KAMIS' THEN 'Kamis'
                WHEN 'JUMAT' THEN 'Jumat'
                WHEN 'SABTU' THEN 'Sabtu'
                WHEN 'MINGGU' THEN 'Minggu'
                ELSE hari
            END
        ");

        DB::statement("
            UPDATE todos
            SET status = CASE UPPER(status)
                WHEN 'PENDING' THEN 'BELUM'
                WHEN 'BELUM' THEN 'BELUM'
                WHEN 'IN_PROGRESS' THEN 'PROGRESS'
                WHEN 'PROGRESS' THEN 'PROGRESS'
                WHEN 'DONE' THEN 'SELESAI'
                WHEN 'SELESAI' THEN 'SELESAI'
                WHEN 'COMPLETE' THEN 'SELESAI'
                WHEN 'COMPLETED' THEN 'COMPLETED'
                WHEN 'CANCELLED' THEN 'CANCELLED'
                ELSE 'BELUM'
            END
        ");

        $hariValues = implode(
            ', ',
            array_map(
                static fn (string $value) => "'" . $value . "'",
                DayOfWeek::list()
            )
        );

        $statusValues = implode(
            ', ',
            array_map(
                static fn (string $value) => "'" . $value . "'",
                Status::list()
            )
        );

        DB::statement("ALTER TABLE mata_kuliahs MODIFY hari ENUM({$hariValues}) NOT NULL");
        DB::statement("ALTER TABLE todos MODIFY status ENUM({$statusValues}) NOT NULL DEFAULT '" . Status::BELUM->value . "'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE mata_kuliahs MODIFY hari VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE todos MODIFY status VARCHAR(255) NOT NULL DEFAULT 'pending'");
    }
};
