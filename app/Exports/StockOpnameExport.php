<?php

namespace App\Exports;

use App\Models\StockOpname;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockOpnameExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return StockOpname::select('kode_barang', 'nama_barang', 'stock_awal', 'stock_sekarang', 'kategori', 'satuan', 'notes', 'updated_at')->get();
    }

    public function headings(): array
    {
        return ['Kode Barang', 'Nama Barang', 'Stock Awal', 'Stock Sekarang', 'Kategori', 'Satuan', 'Notes', 'Updated'];
    }
}
