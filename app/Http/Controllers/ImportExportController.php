<?php

namespace App\Http\Controllers;

use App\Support\EnumInput;
use App\Support\ScheduleTime;
use Illuminate\Http\Request;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImportExportController extends Controller
{

    /**
     * Ambil konfigurasi modul dari config/import-export.php
     */
    protected function getModuleConfig(string $module): array
    {
        $config = config("import-export.{$module}");

        if (!$config) {
            abort(404, "Modul '{$module}' tidak ditemukan.");
        }

        return $config;
    }

    /**
     * Resolve nilai kolom dari model (termasuk relasi dot-notation & callable resolver)
     */
    protected function resolveValue($model, string $column, array $resolvers): mixed
    {
        if (isset($resolvers[$column])) {
            $resolver = $resolvers[$column];

            // Callable resolver (closure / callback)
            if (is_callable($resolver)) {
                return $resolver($model);
            }

            // Dot-notation resolver (e.g., 'mataKuliah.nama')
            if (is_string($resolver)) {
                return data_get($model, $resolver, '-');
            }
        }

        // Ambil langsung dari atribut model
        return EnumInput::displayValue($model->{$column} ?? '-');
    }


    // ═══════════════════════════════════════════════════════════════
    //  EXPORT
    // ═══════════════════════════════════════════════════════════════

    /**
     * Tampilkan form export (pilih rentang tanggal, format, dll)
     */
    public function exportForm(string $module)
    {
        $config = $this->getModuleConfig($module);

        return view('import-export.export', [
            'module' => $module,
            'config' => $config,
            'title' => $config['title'],
            'backRoute' => $config['back_route'],
            'dateColumn' => $config['date_column'],
        ]);
    }

    /**
     * Proses export dan download file Excel (.xlsx)
     */
    public function export(Request $request, string $module): BinaryFileResponse
    {
        $config = $this->getModuleConfig($module);

        $request->validate([
            'tanggal_dari' => ['nullable', 'date'],
            'tanggal_sampai' => ['nullable', 'date', 'after_or_equal:tanggal_dari'],
        ]);

        $modelClass = $config['model'];
        $dateColumn = $config['date_column'];
        $query = $modelClass::query();

        // Eager load relasi
        if (!empty($config['with'])) {
            $query->with($config['with']);
        }

        // Filter rentang tanggal
        if ($request->filled('tanggal_dari')) {
            $query->whereDate($dateColumn, '>=', $request->tanggal_dari);
        }
        if ($request->filled('tanggal_sampai')) {
            $query->whereDate($dateColumn, '<=', $request->tanggal_sampai);
        }

        $records = $query->get();
        $headers = $config['headers'];
        $columns = $config['columns'];
        $resolvers = $config['resolvers'] ?? [];
        $filename = 'export_' . str_replace('-', '_', $module) . '_' . now()->format('Ymd_His') . '.xlsx';
        $tempPath = storage_path('app/' . $filename);

        // Tulis file XLSX menggunakan OpenSpout
        $writer = new XlsxWriter();
        $writer->openToFile($tempPath);

        // Header row dengan style bold
        $headerStyle = (new Style())->withFontBold(true)->withFontSize(11);
        $writer->addRow(Row::fromValuesWithStyle($headers, $headerStyle));

        // Data rows
        foreach ($records as $record) {
            $row = [];
            foreach ($columns as $col) {
                $value = $this->resolveValue($record, $col, $resolvers);
                $row[] = is_null($value) ? '' : (string)$value;
            }

            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();

        // Bersihkan output buffer agar tidak mencemari file binary
        while (ob_get_level()) {
            ob_end_clean();
        }

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ═══════════════════════════════════════════════════════════════
    //  IMPORT
    // ═══════════════════════════════════════════════════════════════

    /**
     * Tampilkan form import (upload file, download template)
     */
    public function importForm(string $module)
    {
        $config = $this->getModuleConfig($module);

        return view('import-export.import', [
            'module' => $module,
            'config' => $config,
            'title' => $config['title'],
            'backRoute' => $config['back_route'],
            'required' => $config['required'] ?? [],
            'importHeaders' => $config['import_headers'] ?? $config['headers'],
            'importColumns' => $config['import_columns'] ?? $config['columns'],
        ]);
    }

    /**
     * Download template Excel (.xlsx) kosong untuk import
     */
    public function downloadTemplate(string $module): BinaryFileResponse
    {
        $config = $this->getModuleConfig($module);
        $headers = $config['import_headers'] ?? $config['headers'];
        $filename = 'template_import_' . str_replace('-', '_', $module) . '.xlsx';
        $tempPath = storage_path('app/' . $filename);

        $writer = new XlsxWriter();
        $writer->openToFile($tempPath);

        // Header row dengan style bold
        $headerStyle = (new Style())->withFontBold(true)->withFontSize(11);
        $writer->addRow(Row::fromValuesWithStyle($headers, $headerStyle));

        $writer->close();

        // Bersihkan output buffer agar tidak mencemari file binary
        while (ob_get_level()) {
            ob_end_clean();
        }

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Proses import dari file Excel (.xlsx) yang diupload
     */
    public function import(Request $request, string $module)
    {
        $config = $this->getModuleConfig($module);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls', 'max:10240'],
            'skip_errors' => ['nullable', 'boolean'],
        ]);

        $modelClass = $config['model'];
        $importColumns = $config['import_columns'] ?? $config['columns'];
        $required = $config['required'] ?? [];
        $importers = $config['importers'] ?? [];
        $fieldMap = $config['import_field_map'] ?? [];
        $skipErrors = $request->boolean('skip_errors', false);

        $file = $request->file('file');
        $reader = new XlsxReader();

        try {
            $reader->open($file->getRealPath());
        }
        catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membaca file Excel. Pastikan format file benar.');
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];
        $rowNum = 0;
        $isHeader = true;

        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $row) {
                $rowNum++;

                // Lewati baris header (baris pertama)
                if ($isHeader) {
                    $isHeader = false;

                    continue;
                }

                $cells = $row->toArray();

                // Skip baris kosong
                if (collect($cells)->filter(fn($v) => !empty($v))->isEmpty()) {
                    continue;
                }

                // Pastikan jumlah kolom sesuai
                if (count($cells) < count($importColumns)) {
                    $cells = array_pad($cells, count($importColumns), '');
                }

                // Map ke associative array
                $data = [];
                foreach ($importColumns as $i => $col) {
                    $value = $cells[$i] ?? null;
                    if ($value instanceof \DateTimeInterface) {
                        $value = $value->format('Y-m-d H:i:s');
                    }
                    elseif (is_string($value)) {
                        $value = trim($value);
                    }
                    $data[$col] = $value === '' ? null : $value;
                }

                // Jalankan importers (lookup / transform) berdasar config
                foreach ($importers as $targetCol => $importer) {
                    // Tentukan source column: bisa dari field_map atau dari targetCol itu sendiri
                    $sourceCol = array_search($targetCol, $fieldMap) ?: $targetCol;
                    $inputValue = $data[$sourceCol] ?? null;

                    if (is_callable($importer)) {
                        $data[$targetCol] = $importer($inputValue);
                    }

                    // Hapus kolom source jika berbeda dari target (input display → actual DB col)
                    if ($sourceCol !== $targetCol && isset($data[$sourceCol])) {
                        unset($data[$sourceCol]);
                    }
                }

                $data = EnumInput::normalizeAttributes($modelClass, $data);

                // Validasi required fields
                $missingFields = [];
                foreach ($required as $field) {
                    if (empty($data[$field])) {
                        $missingFields[] = $field;
                    }
                }

                if (!empty($missingFields)) {
                    $errors[] = "Baris {$rowNum}: Kolom wajib kosong (" . implode(', ', $missingFields) . ')';
                    if (!$skipErrors) {
                        $reader->close();

                        return redirect()->back()
                            ->with('error', "Import gagal pada baris {$rowNum}: Kolom wajib kosong (" . implode(', ', $missingFields) . ')')
                            ->with('import_errors', $errors);
                    }

                    continue;
                }

                $rowError = $this->validateImportRow($module, $data);

                if ($rowError) {
                    $errors[] = "Baris {$rowNum}: {$rowError}";

                    if (!$skipErrors) {
                        $reader->close();

                        return redirect()->back()
                            ->with('error', "Import gagal pada baris {$rowNum}: {$rowError}")
                            ->with('import_errors', $errors);
                    }

                    continue;
                }

                // Cek duplikat: lewati jika semua kolom unique_by sudah ada di database
                $uniqueBy = $config['unique_by'] ?? [];

                if ($uniqueBy) {
                    $query = $modelClass::query();

                    foreach ($uniqueBy as $col) {
                        $query->where($col, $data[$col] ?? null);
                    }

                    if ($query->exists()) {
                        $skipped++;
                        continue;
                    }
                }

                // Insert ke database
                try {
                    $modelClass::create($data);
                    $imported++;
                }
                catch (\Exception $e) {
                    $errorMsg = "Baris {$rowNum}: " . $this->cleanErrorMessage($e->getMessage());
                    $errors[] = $errorMsg;

                    if (!$skipErrors) {
                        $reader->close();

                        return redirect()->back()
                            ->with('error', "Import gagal pada baris {$rowNum}: " . $this->cleanErrorMessage($e->getMessage()))
                            ->with('import_errors', $errors);
                    }
                }
            }

            // Hanya proses sheet pertama
            break;
        }

        $reader->close();

        $message = "Berhasil mengimport {$imported} data {$config['title']}.";
        if ($skipped > 0) {
            $message .= " ({$skipped} data sudah ada, dilewati)";
        }
        if (!empty($errors)) {
            $message .= ' (' . count($errors) . ' baris gagal)';
        }

        return redirect()->route($config['back_route'])
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    /**
     * Bersihkan pesan error agar lebih user-friendly
     */
    protected function cleanErrorMessage(string $message): string
    {
        // Singkatkan pesan Integrity constraint / Duplicate entry
        if (str_contains($message, 'Duplicate entry')) {
            preg_match("/Duplicate entry '(.+?)' for key/", $message, $m);

            return 'Data duplikat: ' . ($m[1] ?? 'tidak diketahui');
        }

        if (str_contains($message, 'SQLSTATE')) {
            // Ambil pesan setelah ']:
            $parts = explode(']: ', $message);

            return end($parts);
        }

        return \Illuminate\Support\Str::limit($message, 120);
    }

    protected function validateImportRow(string $module, array $data): ?string
    {
        if ($module !== 'mata-kuliah') {
            return null;
        }

        if (empty($data['jam_mulai']) || empty($data['jam_selesai'])) {
            return 'Format jam harus berupa HH:MM, HH.MM, atau AM/PM.';
        }

        if (!ScheduleTime::isValidRange($data['jam_mulai'], $data['jam_selesai'])) {
            return 'Jam selesai harus lebih besar dari jam mulai.';
        }

        return null;
    }
}
