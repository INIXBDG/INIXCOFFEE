<?php

namespace App\Imports;

use App\Models\targetKPI;
use App\Models\DetailTargetKPI;
use App\Models\detailPersonKPI;
use App\Models\karyawan;
use App\Models\DataTarget;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Validators\Failure;
use Throwable;
use Illuminate\Support\Facades\DB;

class KpiTargetImport implements OnEachRow, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsOnError, SkipsOnFailure, SkipsEmptyRows
{
    protected $options;

    protected $summary = [
        'imported' => 0,
        'skipped' => 0,
        'errors' => [],
    ];

    protected $processedKeys = [];

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function onRow(Row $row)
    {
        $rowData = $row->toArray();

        if (!$this->isDataRow($rowData)) {
            return;
        }

        try {
            $this->validateRow($rowData);

            $judul = $this->getValue($rowData, ['judul_kpi', 'judul_kpi_', 'judul kpi', 'judul']);
            $deskripsi = $this->getValue($rowData, ['deskripsi', 'deskripsi_kpi']);
            $jabatanRaw = $this->getValue($rowData, ['jabatan', 'jabatan_']);
            $karyawanRaw = $this->getValue($rowData, ['karyawan', 'karyawan_opsional']);
            $assistantRoute = $this->getValue($rowData, ['assistant_route', 'assistant_route_', 'asistant_route', 'assistant route', 'route']);
            $detailJangka = $this->getValue($rowData, ['detail_jangka', 'detail_jangka_jika_tahunan', 'detail_jangka_jika_tahunan_', 'detail jangka']);

            $jabatanList = array_filter(array_map('trim', explode(',', $jabatanRaw)));

            if (empty($jabatanList)) {
                throw new \Exception('Jabatan tidak boleh kosong');
            }

            $dataTarget = DataTarget::whereRaw('LOWER(asistant_route) = ?', [strtolower($assistantRoute)])->first();

            if (!$dataTarget) {
                throw new \Exception("Assistant Route '{$assistantRoute}' tidak ditemukan dalam konfigurasi");
            }

            $idPembuat = auth()->id() ?? 1;

            if ($this->options['skip_duplicate'] ?? false) {
                $uniqueKey = md5(strtolower($judul) . '|' . $idPembuat . '|' . implode('|', $jabatanList) . '|' . strtolower($assistantRoute));

                if (in_array($uniqueKey, $this->processedKeys)) {
                    $this->summary['skipped']++;
                    return;
                }

                $this->processedKeys[] = $uniqueKey;
            }

            DB::transaction(function () use ($judul, $deskripsi, $jabatanList, $karyawanRaw, $dataTarget, $detailJangka, $idPembuat) {
                $createTarget = targetKPI::create([
                    'id_pembuat' => $idPembuat,
                    'id_data_target' => $dataTarget->id,
                    'judul' => $judul,
                    'deskripsi' => $deskripsi ?: null,
                    'status' => '0',
                ]);

                foreach ($jabatanList as $jabatan) {
                    $jabatanLower = strtolower($jabatan);

                    $dataDivisi = karyawan::whereRaw('LOWER(jabatan) = ?', [$jabatanLower])
                        ->where('divisi', '!=', 'Direksi')
                        ->value('divisi');

                    $detailJangkaValue = null;
                    $isTahunan = strtolower(trim($dataTarget->jangka_target)) === 'tahunan';

                    if ($isTahunan && !empty($detailJangka)) {
                        if (!preg_match('/^\d{4}$/', $detailJangka)) {
                            throw new \Exception('Detail Jangka harus format 4 digit tahun (contoh: 2024)');
                        }
                        $detailJangkaValue = $detailJangka;
                    }

                    $detailStore = DetailTargetKPI::create([
                        'id_targetKPI' => $createTarget->id,
                        'jabatan' => $jabatan,
                        'divisi' => $dataDivisi,
                        'id_data_target' => $dataTarget->id,
                        'jangka_target' => $dataTarget->jangka_target,
                        'detail_jangka' => $detailJangkaValue,
                        'tipe_target' => $dataTarget->tipe_target,
                        'nilai_target' => $dataTarget->nilai_target,
                    ]);

                    $karyawanRawList = array_filter(array_map('trim', explode(',', $karyawanRaw)));

                    if (!empty($karyawanRawList)) {
                        $karyawanIds = karyawan::whereIn('nama_lengkap', $karyawanRawList)
                            ->whereRaw('LOWER(jabatan) = ?', [$jabatanLower])
                            ->where('status_aktif', '1')
                            ->where('jabatan', '!=', 'Outsource')
                            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                            ->where('jabatan', '!=', 'Pilih Jabatan')
                            ->whereNotNull('nip')
                            ->where('divisi', '!=', 'Direksi')
                            ->pluck('id')
                            ->toArray();
                    } else {
                        $karyawanIds = karyawan::whereRaw('LOWER(jabatan) = ?', [$jabatanLower])
                            ->where('status_aktif', '1')
                            ->where('jabatan', '!=', 'Outsource')
                            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
                            ->where('jabatan', '!=', 'Pilih Jabatan')
                            ->whereNotNull('nip')
                            ->where('divisi', '!=', 'Direksi')
                            ->pluck('id')
                            ->toArray();
                    }

                    foreach ($karyawanIds as $karyawanId) {
                        detailPersonKPI::create([
                            'id_target' => $createTarget->id,
                            'detailTargetKey' => $detailStore->id,
                            'id_karyawan' => $karyawanId,
                        ]);
                    }
                }
            });

            $this->summary['imported']++;
        } catch (\Exception $e) {
            $this->summary['errors'][] = 'Baris #' . $row->getIndex() . ': ' . $e->getMessage();
        }
    }

    private function isDataRow(array $row): bool
    {
        $inputColumns = ['judul_kpi', 'judul_kpi_', 'judul kpi', 'judul', 'deskripsi', 'jabatan', 'jabatan_', 'karyawan', 'karyawan_opsional', 'assistant_route', 'assistant_route_', 'asistant_route', 'assistant route', 'route', 'detail_jangka', 'detail_jangka_jika_tahunan', 'detail_jangka_jika_tahunan_', 'detail jangka'];

        foreach ($inputColumns as $col) {
            if (isset($row[$col]) && trim((string) $row[$col]) !== '') {
                return true;
            }
        }

        return false;
    }

    private function getValue(array $row, array $keys): string
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && trim((string) $row[$key]) !== '') {
                return trim((string) $row[$key]);
            }
        }
        return '';
    }

    private function validateRow(array $row)
    {
        $judul = $this->getValue($row, ['judul_kpi', 'judul_kpi_', 'judul kpi', 'judul']);
        $jabatan = $this->getValue($row, ['jabatan', 'jabatan_']);
        $assistantRoute = $this->getValue($row, ['assistant_route', 'assistant_route_', 'asistant_route', 'assistant route', 'route']);

        if (empty($judul)) {
            throw new \Exception("Kolom 'Judul KPI' wajib diisi");
        }

        if (empty($jabatan)) {
            throw new \Exception("Kolom 'Jabatan' wajib diisi");
        }

        if (empty($assistantRoute)) {
            throw new \Exception("Kolom 'Assistant Route' wajib diisi");
        }

        $detailJangka = $this->getValue($row, ['detail_jangka', 'detail_jangka_jika_tahunan', 'detail_jangka_jika_tahunan_', 'detail jangka']);
        if (!empty($detailJangka) && !preg_match('/^\d{4}$/', $detailJangka)) {
            throw new \Exception('Detail Jangka harus format 4 digit tahun (contoh: 2024)');
        }
    }

    public function rules(): array
    {
        return [];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function onError(Throwable $e) {}

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->summary['errors'][] = "Baris #{$failure->row()} [{$failure->attribute()}]: " . implode(', ', $failure->errors());
        }
    }

    public function getSummary(): array
    {
        return $this->summary;
    }
}
