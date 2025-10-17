<?php

namespace App\Exports;

use App\Models\Inventaris;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventarisExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Inventaris::all();
    }

    public function headings(): array
    {
        return [
            'ID Barang',
            'Nama',
            'Tipe',
            'Merk/Kode/Seri',
            'Qty',
            'Harga Beli',
            'Total Harga',
            'Ruangan',
            'Kondisi',
            'Tanggal Pembelian',
            'Pengguna',
            'Deskripsi',
        ];
    }

    public function map($item): array
    {
        return [
            $item->idbarang,
            $item->name,
            $item->type === 'E' ? 'Elektronik' : 'Non-Elektronik',
            $item->merk_kode_seri_hardware,
            $item->qty . ' ' . ($item->satuan ?? ''),
            $this->formatRupiah($item->harga_beli),
            $this->formatRupiah($item->total_harga),
            $item->ruangan,
            $item->kondisi,
            $item->waktu_pembelian ? \Carbon\Carbon::parse($item->waktu_pembelian)->format('d-m-Y') : '',
            $item->pengguna,
            $item->deskripsi,
        ];
    }

    private function formatRupiah($value)
    {
        if (!$value) {
            return 'Rp 0';
        }

        return 'Rp ' . number_format($value, 0, ',', '.');
    }
}
