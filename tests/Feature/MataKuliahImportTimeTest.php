<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Tests\TestCase;

class MataKuliahImportTimeTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_normalizes_common_time_formats(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('import-export.import.process', 'mata-kuliah'), [
            'file' => $this->makeSpreadsheet([
                [
                    'Kode', 'Nama', 'SKS', 'Kelas', 'Dosen', 'Ruangan', 'Hari', 'Jam Mulai', 'Jam Selesai',
                    'LMS', 'LMS Link', 'Semester', 'Tahun Ajaran', 'Warna', 'Catatan', 'Aktif',
                ],
                [
                    'IF701',
                    'Robotika Lanjut',
                    2,
                    'A',
                    'Dr. Arham',
                    'B112',
                    'Senin',
                    '07.20',
                    '2:40 PM',
                    '',
                    '',
                    6,
                    2026,
                    '',
                    '',
                    'Ya',
                ],
            ]),
        ]);

        $response->assertSessionMissing('error');

        $this->assertDatabaseHas('mata_kuliahs', [
            'kode' => 'IF701',
            'jam_mulai' => '07:20:00',
            'jam_selesai' => '14:40:00',
        ]);
    }

    public function test_import_rejects_end_time_that_is_before_start_time(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('import-export.import', 'mata-kuliah'))
            ->post(route('import-export.import.process', 'mata-kuliah'), [
                'file' => $this->makeSpreadsheet([
                    [
                        'Kode', 'Nama', 'SKS', 'Kelas', 'Dosen', 'Ruangan', 'Hari', 'Jam Mulai', 'Jam Selesai',
                        'LMS', 'LMS Link', 'Semester', 'Tahun Ajaran', 'Warna', 'Catatan', 'Aktif',
                    ],
                    [
                        'IF702',
                        'Gudang Data',
                        2,
                        'A',
                        'Dr. Arham',
                        'B112',
                        'Senin',
                        '07:12',
                        '02:24',
                        '',
                        '',
                        6,
                        2026,
                        '',
                        '',
                        'Ya',
                    ],
                ]),
            ]);

        $response->assertRedirect(route('import-export.import', 'mata-kuliah'));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('mata_kuliahs', ['kode' => 'IF702']);
    }

    private function makeSpreadsheet(array $rows): UploadedFile
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'mata_kuliah_import_');

        if ($tempPath === false) {
            throw new \RuntimeException('Tidak bisa membuat file sementara untuk test import.');
        }

        $xlsxPath = $tempPath . '.xlsx';

        rename($tempPath, $xlsxPath);

        $writer = new XlsxWriter();
        $writer->openToFile($xlsxPath);

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();

        return new UploadedFile(
            $xlsxPath,
            'mata-kuliah.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }
}
