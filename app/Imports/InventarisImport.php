<?php

namespace App\Imports;

use App\Models\Inventaris;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class InventarisImport implements ToModel, SkipsOnFailure, WithHeadingRow
{
    use SkipsFailures;

    private $skippedRows = [];
    private $importedCount = 0;

    private function transformDate($date)
    {
        if (is_numeric($date)) {
            // Jika numeric, kemungkinan besar format Excel date (serial)
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning('Numeric date transformation failed', ['date' => $date, 'message' => $e->getMessage()]);
                return '-';
            }
        }

        if (is_string($date) && !empty(trim($date)) && $date !== '#N/A') {
            $formats = [
                'Y-m-d',
                'd-m-Y',
                'd/m/Y',
                'm/d/Y',
                'j F Y',
                'j M Y',
                'd M Y',
                'd F Y',
                'Y/m/d',
                'Y.n.j',
                'd.m.Y',
                'Ymd',
            ];

            foreach ($formats as $format) {
                try {
                    $carbonDate = \Carbon\Carbon::createFromFormat($format, $date);
                    return $carbonDate->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }

            // Jika gagal dengan semua format yang dicoba, coba parse langsung
            try {
                return \Carbon\Carbon::parse($date)->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning('Fallback parsing failed for date', ['date' => $date, 'message' => $e->getMessage()]);
                return '-';
            }
        }

        return '-';
    }

    // Fungsi baru untuk mapping fleksibel berdasarkan daftar alias (keyword)
    private function getFlexibleValue($row, array $aliases, $default = '-')
    {
        // 1. Coba pencocokan persis dulu
        foreach ($aliases as $alias) {
            $cleanAlias = str_replace(['_', ' '], '', strtolower($alias));
            foreach ($row as $key => $value) {
                $cleanKey = str_replace(['_', ' '], '', strtolower($key));
                if ($cleanKey === $cleanAlias && $value !== null && $value !== '') {
                    return $value;
                }
            }
        }
        
        // 2. Jika tidak ada yang cocok persis, coba pencocokan sebagian (contains)
        foreach ($aliases as $alias) {
            $cleanAlias = str_replace(['_', ' '], '', strtolower($alias));
            foreach ($row as $key => $value) {
                $cleanKey = str_replace(['_', ' '], '', strtolower($key));
                if (str_contains($cleanKey, $cleanAlias) && $value !== null && $value !== '') {
                    return $value;
                }
            }
        }

        return $default;
    }


    private function mapKategoriToType($kategori)
    {
        if (empty($kategori) || $kategori === '#N/A' || $kategori === '-') {
            return 'NE';
        }
        return preg_match('/elektronik/i', $kategori) ? 'E' : 'NE';
    }

    private function cleanValue($key, $value)
    {
        if (is_string($value)) {
            $value = trim($value);

            // Hanya bersihkan format untuk kolom harga
            if (in_array($key, ['harga_satuan_rp', 'total_harga_rp'])) {
                $value = preg_replace('/[\,\.Rp\s]+/', '', $value); // Hapus format angka
            }

            return $value === '#N/A' || $value === '#REF!' || $value === '' ? '-' : $value;
        }

        return is_null($value) || $value === '' ? '-' : $value;
    }


    private function normalizeKondisi($kondisi)
    {
        if (empty($kondisi) || $kondisi === '#N/A' || $kondisi === '-') {
            return 'baik';
        }
        $kondisi = strtolower(trim($kondisi));
        return $kondisi === 'bagus' ? 'baik' : $kondisi;
    }

    public function model(array $row)
    {
        if ($this->importedCount === 0 && empty($this->skippedRows)) {
            Log::info('Excel headers: ' . json_encode(array_keys($row)));
        }

        // Clean values
        foreach ($row as $key => $value) {
            $row[$key] = $this->cleanValue($key, $value);
        }

        // Normalize keys (replace spasi dan simbol)
        $normalizedKeys = array_map(function ($key) {
            return str_replace([' ', '(', ')'], ['_', '', ''], strtolower(trim($key)));
        }, array_keys($row));

        $row = array_combine($normalizedKeys, array_values($row));

        Log::debug('Normalized row keys: ' . json_encode(array_keys($row)));
        Log::debug('Processing row: ' . json_encode($row));

        // Daftar keyword/alias untuk setiap target kolom
        $idbarang = $this->getFlexibleValue($row, ['id_barang', 'idbarang', 'nomor_inventaris', 'no_inv', 'kode', 'id']);
        $namaBarang = $this->getFlexibleValue($row, ['nama_barang', 'nama', 'barang', 'item', 'deskripsi_barang']);
        $kategori = $this->getFlexibleValue($row, ['tipe', 'kategori', 'jenis', 'type', 'kelompok']);
        $pic = $this->getFlexibleValue($row, ['pic', 'pengguna', 'user', 'penanggung_jawab', 'pemakai', 'karyawan']);
        $ruangan = $this->getFlexibleValue($row, ['ruangan', 'lokasi', 'tempat', 'posisi']);
        $kondisi = $this->getFlexibleValue($row, ['kondisi', 'status', 'keadaan']);
        $tglBeli = $this->getFlexibleValue($row, ['tanggal_pembelian', 'tgl_beli', 'waktu_pembelian', 'tanggal', 'tahun', 'pembelian'], null);
        
        // Kolom lain yang mungkin ada (opsional)
        $jumlah = $this->getFlexibleValue($row, ['jumlah', 'qty', 'kuantitas'], 1);
        $hargaSatuan = $this->getFlexibleValue($row, ['harga', 'biaya', 'satuan'], 0);
        
        if ($namaBarang === '-') {
            $this->skippedRows[] = ['row' => $row, 'reason' => 'Missing nama_barang'];
            Log::info('Skipped row: missing nama_barang', ['row' => $row]);
            return null;
        }

        if ($idbarang !== '-' && Inventaris::where('idbarang', $idbarang)->exists()) {
            $this->skippedRows[] = ['row' => $row, 'reason' => 'Duplicate idbarang: ' . $idbarang];
            Log::info('Skipped row: duplicate idbarang', ['row' => $row]);
            return null;
        }

        $jumlah = (int)$jumlah;
        $hargaSatuan = (float) preg_replace('/[\,\.Rp\s]+/', '', $hargaSatuan);
        $totalHarga = $jumlah * $hargaSatuan;

        $data = [
            'idbarang' => $idbarang === '-' ? null : $idbarang,
            'name' => $namaBarang,
            'kodebarang' => $idbarang, // Fallback kodebarang = idbarang
            'merk_kode_seri_hardware' => '-',
            'qty' => $jumlah,
            'satuan' => 'unit',
            'type' => $this->mapKategoriToType($kategori),
            'harga_beli' => $hargaSatuan,
            'total_harga' => $totalHarga,
            'waktu_pembelian' => $this->transformDate($tglBeli),
            'pengguna' => $pic,
            'ruangan' => $ruangan,
            'kondisi' => $this->normalizeKondisi($kondisi),
            'deskripsi' => '-',
        ];

        try {
            $model = new Inventaris($data);
            $model->save();
            $this->importedCount++;
            Log::info('Row imported successfully', ['idbarang' => $data['idbarang']]);
            return $model;
        } catch (\Exception $e) {
            $this->skippedRows[] = ['row' => $row, 'reason' => 'DB Error: ' . $e->getMessage()];
            Log::error('DB Error during import: ' . $e->getMessage(), ['row' => $row]);
            return null;
        }
    }

    public function getSkippedRows()
    {
        return $this->skippedRows;
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }
}
