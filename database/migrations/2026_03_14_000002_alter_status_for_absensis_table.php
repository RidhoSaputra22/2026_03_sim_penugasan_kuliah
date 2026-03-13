<?php

use App\Enums\AttendanceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $values = implode(
                ', ',
                array_map(
                    fn(string $value) => "'" . $value . "'",
                    AttendanceStatus::list()
                )
            );

            DB::statement(
                "ALTER TABLE absensis MODIFY status ENUM({$values}) NOT NULL DEFAULT '" . AttendanceStatus::HADIR->value . "'"
            );
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE absensis MODIFY status ENUM('hadir', 'izin', 'sakit', 'alpha') NOT NULL DEFAULT 'hadir'"
            );
        }
    }
};
