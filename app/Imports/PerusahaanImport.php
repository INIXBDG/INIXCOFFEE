<?php

namespace App\Imports;

use App\Models\Perusahaan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PerusahaanImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Skip kalau tidak ada nama_perusahaan
            if (empty($row['nama_perusahaan'])) {
                continue;
            }

            $formatted = [
                'nama_perusahaan'     => $row['nama_perusahaan'] ?? null,
                'kategori_perusahaan' => $row['kategori_perusahaan'] ?? null,
                'lokasi'              => $row['lokasi'] ?? null,
                'status'              => $row['status'] ?? null,
                'alamat'              => $row['alamat'] ?? null,
                'sales_key'           => $row['sales_key'] ?? null,
            ];

            // Hapus key yang null agar tidak bikin double koma
            $cleanData = array_filter($formatted, fn($v) => !is_null($v));

            Perusahaan::updateOrCreate(
                ['nama_perusahaan' => $row['nama_perusahaan']],
                $cleanData
            );
        }
    }
}
