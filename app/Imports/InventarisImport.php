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

    /**
     * Transform Excel date to PHP date format.
     *
     * @param mixed $date
     * @return string|null
     */
    private function transformDate($date)
    {
        if (is_numeric($date)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($date)->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning('Date transformation failed: ' . $e->getMessage(), ['date' => $date]);
                return '-';
            }
        } elseif (is_string($date) && !empty($date) && $date !== '#N/A') {
            try {
                return \Carbon\Carbon::parse($date)->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning('String date parsing failed: ' . $e->getMessage(), ['date' => $date]);
                return '-';
            }
        }
        return '-';
    }

    /**
     * Map Kategori to type (E or NE).
     *
     * @param string|null $kategori
     * @return string
     */
    private function mapKategoriToType($kategori)
    {
        if (empty($kategori) || $kategori === '#N/A' || $kategori === '-') {
            return 'NE';
        }
        return preg_match('/elektronik/i', $kategori) ? 'E' : 'NE';
    }

    /**
     * Clean and normalize input value.
     *
     * @param mixed $value
     * @return mixed
     */
    private function cleanValue($value)
    {
        if (is_string($value)) {
            $value = trim($value);
            // Remove commas, periods, and currency symbols for price fields
            $value = preg_replace('/[\,\.Rp\s]+/', '', $value);
            return $value === '#N/A' || $value === '#REF!' || empty($value) ? '-' : $value;
        }
        return is_null($value) || $value === '' ? '-' : $value;
    }

    /**
     * Normalize kondisi_barang value.
     *
     * @param string|null $kondisi
     * @return string
     */
    private function normalizeKondisi($kondisi)
    {
        if (empty($kondisi) || $kondisi === '#N/A' || $kondisi === '-') {
            return 'baik';
        }
        $kondisi = strtolower(trim($kondisi));
        return $kondisi === 'bagus' ? 'baik' : $kondisi;
    }

    /**
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Log headers for the first row to verify mapping
        if ($this->importedCount === 0 && empty($this->skippedRows)) {
            Log::info('Excel headers: ' . json_encode(array_keys($row)));
        }

        // Clean all row values
        $row = array_map([$this, 'cleanValue'], $row);

        // Normalize header keys to handle spaces and special characters
        $row = array_combine(
            array_map(function ($key) {
                return str_replace([' ', '(', ')'], ['_', '', ''], strtolower($key));
            }, array_keys($row)),
            array_values($row)
        );

        // Log cleaned row for debugging
        Log::debug('Processing row: ' . json_encode($row));

        // Skip if required fields are missing or invalid
        if ($row['nama_barang'] === '-' || $row['kategori'] === '-') {
            $this->skippedRows[] = ['row' => $row, 'reason' => 'Missing or invalid Nama Barang or Kategori'];
            Log::info('Skipped row due to missing nama_barang or kategori', ['row' => $row]);
            return null;
        }

        // Check for duplicate idbarang
        if ($row['nomor_inventaris'] !== '-' && Inventaris::where('idbarang', $row['nomor_inventaris'])->exists()) {
            $this->skippedRows[] = ['row' => $row, 'reason' => 'Duplicate idbarang: ' . $row['nomor_inventaris']];
            Log::info('Skipped row due to duplicate idbarang', ['idbarang' => $row['nomor_inventaris']]);
            return null;
        }

        // Map Excel data to model
        $data = [
            'idbarang' => $row['nomor_inventaris'] === '-' ? null : $row['nomor_inventaris'],
            'name' => $row['nama_barang'],
            'kodebarang' => $row['kode_barang'],
            'merk_kode_seri_hardware' => $row['merkkode_serikode_hardware'],
            'qty' => (int) ($row['jumlah'] === '-' ? 1 : $row['jumlah']),
            'satuan' => $row['satuan'],
            'type' => $this->mapKategoriToType($row['kategori']),
            'harga_beli' => (float) ($row['harga_satuan_rp'] === '-' ? 0 : $row['harga_satuan_rp']),
            'total_harga' => (float) ($row['total_harga_rp'] === '-' ? (($row['jumlah'] === '-' ? 1 : $row['jumlah']) * ($row['harga_satuan_rp'] === '-' ? 0 : $row['harga_satuan_rp'])) : $row['total_harga_rp']),
            'waktu_pembelian' => $this->transformDate($row['tanggal_pembelian']),
            'pengguna' => $row['user'],
            'ruangan' => $row['lokasi_barang'],
            'kondisi' => $this->normalizeKondisi($row['kondisi_barang']),
            'deskripsi' => $row['keterangan'],
        ];

        try {
            $model = new Inventaris($data);
            $model->save(); // Explicitly save to ensure persistence
            $this->importedCount++;
            Log::info('Imported row successfully', ['idbarang' => $data['idbarang']]);
            return $model;
        } catch (\Exception $e) {
            $this->skippedRows[] = ['row' => $row, 'reason' => 'Database error: ' . $e->getMessage()];
            Log::error('Database error during import: ' . $e->getMessage(), ['row' => $row]);
            return null;
        }
    }

    /**
     * Validation rules for each row.
     *
     * @return array
     */
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
            'kondisi_barang' => ['nullable', 'string', Rule::in(['baik', 'rusak/bermasalah', 'sedang diperbaiki', 'bagus', '-'])],
            'keterangan' => 'nullable|string',
        ];
    }

    /**
     * Get skipped rows with reasons.
     *
     * @return array
     */
    public function getSkippedRows()
    {
        return $this->skippedRows;
    }

    /**
     * Get number of imported rows.
     *
     * @return int
     */
    public function getImportedCount()
    {
        return $this->importedCount;
    }
}
