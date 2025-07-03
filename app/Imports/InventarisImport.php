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

class InventarisImport implements ToModel, WithValidation, SkipsOnFailure, WithHeadingRow
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

        // Validasi key yang penting
        $namaBarang = $row['nama_barang'] ?? '-';
        $kategori = $row['kategori'] ?? '-';

        if ($namaBarang === '-' || $kategori === '-') {
            $this->skippedRows[] = ['row' => $row, 'reason' => 'Missing nama_barang or kategori'];
            Log::info('Skipped row: missing nama_barang or kategori', ['row' => $row]);
            return null;
        }

        $idbarang = $row['nomor_inventaris'] ?? '-';
        if ($idbarang !== '-' && Inventaris::where('idbarang', $idbarang)->exists()) {
            $this->skippedRows[] = ['row' => $row, 'reason' => 'Duplicate idbarang: ' . $idbarang];
            Log::info('Skipped row: duplicate idbarang', ['row' => $row]);
            return null;
        }

        // Pastikan field sesuai key
        $jumlah = $row['jumlah'] !== '-' ? (int)$row['jumlah'] : 1;
        $hargaSatuan = $row['harga_satuan_rp'] !== '-' ? (float)$row['harga_satuan_rp'] : 0;
        $totalHarga = $row['total_harga_rp'] !== '-' ? (float)$row['total_harga_rp'] : ($jumlah * $hargaSatuan);

        $data = [
            'idbarang' => $idbarang === '-' ? null : $idbarang,
            'name' => $namaBarang,
            'kodebarang' => $row['kode_barang'] ?? '-',
            'merk_kode_seri_hardware' => $row['merkkode_serikode_hardware'] ?? '-',
            'qty' => $jumlah,
            'satuan' => $row['satuan'] ?? 'unit',
            'type' => $this->mapKategoriToType($kategori),
            'harga_beli' => $hargaSatuan,
            'total_harga' => $totalHarga,
            'waktu_pembelian' => $this->transformDate($row['tanggal_pembelian'] ?? null),
            'pengguna' => $row['user'] ?? '-',
            'ruangan' => $row['lokasi_barang'] ?? '-',
            'kondisi' => $this->normalizeKondisi($row['kondisi_barang'] ?? null),
            'deskripsi' => $row['keterangan'] ?? '-',
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

    public function rules(): array
    {
        return [
            'nama_barang' => 'required|string|max:255',
            'kode_barang' => 'nullable|string|max:255',
            'merkkode_serikode_hardware' => 'nullable|string|max:255',
            'nomor_inventaris' => 'nullable|string|max:255',
            'jumlah' => 'nullable|integer|min:1',
            'satuan' => 'nullable|string|max:255',
            'kategori' => 'required|string',
            'harga_satuan_rp' => 'nullable|numeric|min:0',
            'total_harga_rp' => 'nullable|numeric|min:0',
            'tanggal_pembelian' => 'nullable',
            'user' => 'nullable|string|max:255',
            'lokasi_barang' => 'nullable|string|max:255',
            'kondisi_barang' => ['nullable', 'string', Rule::in(['baik', 'rusak', 'kurang layak'])],
            'keterangan' => 'nullable|string',
        ];
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
