<?php

namespace App\Imports;

use App\Models\Contact;
use App\Models\Perusahaan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ContactImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
        {
            foreach ($rows as $row) {
                // cari perusahaan berdasarkan nama_perusahaan + sales_key
                $perusahaan = Perusahaan::where('nama_perusahaan', $row['nama_perusahaan'])
                    ->where('sales_key', $row['sales_key'])
                    ->first();

                // kalau tidak ketemu, skip
                if (!$perusahaan) {
                    continue;
                }

                $formatted = [
                    'id_perusahaan' => $perusahaan->id,
                    'sales_key'     => $row['sales_key'] ?? null,
                    'nama'          => $row['nama'] ?? null,
                    'status'        => '0',
                    'email'         => $row['email'] ?? null,
                    'cp'            => $row['cp'] ?? null,
                    'divisi'        => $row['divisi'] ?? null,
                ];

                // update kalau nama + perusahaan sudah ada, kalau belum insert
                Contact::updateOrCreate(
                    [
                        'id_perusahaan' => $perusahaan->id,
                        'nama' => $formatted['nama']
                    ],
                    $formatted
                );
            }
        }
}
