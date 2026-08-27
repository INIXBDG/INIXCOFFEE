<?php

namespace App\Imports;

use App\Models\ObTools;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KondisiToolsImport implements  ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new ObTools([
            'nama_alat' => $row['nama_alat'],
            'kategori' => $row['kategori'],
            'qty' => $row['quantity'],
        ]);
    }
}
