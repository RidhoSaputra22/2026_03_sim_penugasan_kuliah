<?php

/**
 * Konfigurasi modul Import & Export
 *
 * Setiap modul mendefinisikan:
 * - title          : Judul halaman
 * - model          : Fully-qualified model class
 * - date_column    : Kolom tanggal untuk filter rentang (null = created_at)
 * - back_route     : Route name untuk tombol kembali
 * - with           : Eager-load relasi saat export
 * - columns        : Kolom DB yang di-export
 * - headers        : Label header untuk Excel (export)
 * - required       : Kolom wajib saat import
 * - resolvers      : Mapping kolom → relasi readable (untuk export)
 * - importers      : Mapping kolom → logic lookup (untuk import)
 * - import_columns : Kolom DB yang di-import (jika berbeda dari columns)
 * - import_headers : Label header untuk template import
 * - unique_by      : Kolom untuk cek duplikat saat import
 */

return [

    // ── AKADEMIK: MATA KULIAH ────────────────────────────────

    'mata-kuliah' => [
        'title' => 'Data Mata Kuliah',
        'model' => \App\Models\MataKuliah::class ,
        'date_column' => 'created_at',
        'back_route' => 'mata-kuliah.index',
        'with' => [],
        'columns' => [
            'kode', 'nama', 'sks', 'kelas', 'dosen', 'ruangan', 'hari', 'jam_mulai', 'jam_selesai',
            'lms', 'lms_link', 'semester', 'tahun_ajaran', 'warna', 'catatan', 'is_active'
        ],
        'headers' => [
            'Kode', 'Nama', 'SKS', 'Kelas', 'Dosen', 'Ruangan', 'Hari', 'Jam Mulai', 'Jam Selesai',
            'LMS', 'LMS Link', 'Semester', 'Tahun Ajaran', 'Warna', 'Catatan', 'Aktif'
        ],
        'required' => ['kode', 'nama', 'sks', 'kelas', 'dosen', 'hari', 'jam_mulai', 'jam_selesai', 'semester', 'tahun_ajaran'],
        'resolvers' => [
            'is_active' => [\App\Helpers\ImportExportHelper::class, 'resolveIsActive'],
        ],
        'import_columns' => [
            'kode', 'nama', 'sks', 'kelas', 'dosen', 'ruangan', 'hari', 'jam_mulai', 'jam_selesai',
            'lms', 'lms_link', 'semester', 'tahun_ajaran', 'warna', 'catatan', 'is_active'
        ],
        'import_headers' => [
            'Kode', 'Nama', 'SKS', 'Kelas', 'Dosen', 'Ruangan', 'Hari', 'Jam Mulai', 'Jam Selesai',
            'LMS', 'LMS Link', 'Semester', 'Tahun Ajaran', 'Warna', 'Catatan', 'Aktif'
        ],
        'importers' => [
            'jam_mulai' => [\App\Helpers\ImportExportHelper::class, 'importTime'],
            'jam_selesai' => [\App\Helpers\ImportExportHelper::class, 'importTime'],
            'is_active' => [\App\Helpers\ImportExportHelper::class, 'importIsActive'],
        ],
        'unique_by' => ['kelas', 'tahun_ajaran', 'semester'],
    ],

    // ── AKADEMIK: TUGAS ─────────────────────────────────────

    'tugas' => [
        'title' => 'Data Tugas',
        'model' => \App\Models\Tugas::class ,
        'date_column' => 'deadline',
        'back_route' => 'tugas.index',
        'with' => ['mataKuliah'],
        'columns' => [
            'judul', 'mata_kuliah', 'deskripsi', 'deadline', 'status', 'progress', 'prioritas', 'catatan'
        ],
        'headers' => [
            'Judul', 'Mata Kuliah', 'Deskripsi', 'Deadline', 'Status', 'Progress (%)', 'Prioritas', 'Catatan'
        ],
        'required' => ['judul', 'mata_kuliah_id', 'deadline', 'status'],
        'resolvers' => [
            'mata_kuliah' => 'mataKuliah.nama',
        ],
        'import_columns' => [
            'judul', 'mata_kuliah', 'deskripsi', 'deadline', 'status', 'progress', 'prioritas', 'catatan'
        ],
        'import_headers' => [
            'Judul', 'Mata Kuliah (Kode)', 'Deskripsi', 'Deadline (YYYY-MM-DD)', 'Status', 'Progress (%)', 'Prioritas', 'Catatan'
        ],
        'importers' => [
            'mata_kuliah_id' => [\App\Helpers\ImportExportHelper::class, 'importMataKuliahId'],
        ],
        'import_field_map' => [
            'mata_kuliah' => 'mata_kuliah_id', // kolom input → kolom DB sebenarnya
        ],
        'unique_by' => [],
    ],

];
