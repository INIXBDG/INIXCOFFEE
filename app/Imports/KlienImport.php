<?php

namespace App\Imports;

use App\Models\dbklien;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Concerns\ToModel;

class KlienImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // dd($row);
        if ($row[0] == 'nama' || $row[0] == 'Nama') {
            return null;
        }

        // Logic Tanggal Lahir (Kolom G = Index 6)
        $tanggalLahir = null;
        if (!empty($row[6])) {
            $tanggalLahir = is_numeric($row[6]) 
                ? Date::excelToDateTimeObject($row[6]) 
                : $row[6];
        }
        $namaPerusahaan = !empty($row[5]) ? $row[5] : 'Personal';
        // Logic Created At (Kolom J = Index 9)
        $customCreatedAt = now();
        if (!empty($row[9])) {
            $customCreatedAt = is_numeric($row[9])
                ? Date::excelToDateTimeObject($row[9])
                : $row[9];
        }

        return new dbklien([
            'nama'            => $row[0], // Kolom A
            'jenis_kelamin'   => $row[1], // Kolom B
            'email'           => $row[2], // Kolom C
            'no_hp'           => $row[3], // Kolom D
            'alamat'          => $row[4], // Kolom E
            'nama_perusahaan' => $row[5], // Kolom F
            'tanggal_lahir'   => $tanggalLahir,
            'nama_materi'     => $row[7], // Kolom H
            'sales_key'       => $row[8], // Kolom I
            'created_at'      => $customCreatedAt,
        ]);
    }
}
