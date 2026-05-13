<?php

namespace App\Exports;

use App\Models\StockOpnameLog;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockOpnameLogExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return StockOpnameLog::with(['barang', 'karyawan'])->latest()->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Update',
            'Nama Barang',
            'Stock Sebelumnya',
            'Stock Hari Ini',
            'Selisih',
            'Notes',
            'PIC',
        ];
    }

    public function map($log): array
    {
        static $no = 0;
        $no++;
        
        return [
            $no,
            $log->updated_at ? $log->updated_at->format('d/m/Y H:i') : '',
            $log->barang?->nama_barang ?? '-',
            $log->stock_sebelumnya ?? 0,
            $log->stock_hari_ini ?? 0,
            $log->selisih ?? 0,
            $log->notes ?? '-',
            $log->karyawan?->nama_lengkap ?? '-',
        ];
    }
}