<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class JurnalArrayImport implements ToArray, WithCalculatedFormulas
{
    public function array(array $array) {}
}
