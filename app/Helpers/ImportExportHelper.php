<?php

namespace App\Helpers;

use App\Models\MataKuliah;

class ImportExportHelper
{
    /**
     * Resolve boolean is_active ke format teks yang mudah dibaca (Excel).
     */
    public static function resolveIsActive($model)
    {
        return $model->is_active ? 'Ya' : 'Tidak';
    }

    /**
     * Parsing teks is_active dari Excel ke boolean.
     */
    public static function importIsActive($value)
    {
        return in_array(strtolower(trim($value ?? '')), ['ya', 'yes', '1', 'true', 'aktif'], true) ? 1 : 0;
    }

    /**
     * Resolve mata_kuliah_id berdasarkan kode saat import tugas.
     */
    public static function importMataKuliahId($value)
    {
        return MataKuliah::where('kode', $value)->value('id');
    }
}
