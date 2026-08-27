<?php

namespace App\Exports;

use App\Models\Inventaris;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RekapInventarisExport implements FromCollection, WithHeadings, WithMapping
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Inventaris::query();

        $tahun = $this->request->tahun ?? date('Y');
        $query->whereYear('waktu_pembelian', $tahun);

        if ($this->request->kategori) {
            $query->where('kategori', $this->request->kategori);
        }

        if ($this->request->lokasi) {
            $query->where('ruangan', $this->request->lokasi);
        }

        if ($this->request->jenis) {
            $query->where('name', $this->request->jenis);
        }

        if ($this->request->mode_periode === 'bulan' && $this->request->bulan) {
            $query->whereMonth('waktu_pembelian', $this->request->bulan);
        } elseif ($this->request->mode_periode === 'quartal' && $this->request->quartal) {
            $quartal = $this->request->quartal;
            if ($quartal == 1) {
                $query->whereBetween(DB::raw('MONTH(waktu_pembelian)'), [1, 3]);
            } elseif ($quartal == 2) {
                $query->whereBetween(DB::raw('MONTH(waktu_pembelian)'), [4, 6]);
            } elseif ($quartal == 3) {
                $query->whereBetween(DB::raw('MONTH(waktu_pembelian)'), [7, 9]);
            } elseif ($quartal == 4) {
                $query->whereBetween(DB::raw('MONTH(waktu_pembelian)'), [10, 12]);
            }
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID Barang',
            'ID Inventaris',
            'Tanggal Beli',
            'No. KK',
            'Nama Barang',
            'Kategori',
            'Lokasi/Ruangan',
            'Harga',
        ];
    }

    public function map($item): array
    {
        return [
            $item->idbarang,
            $item->idinventaris,
            $item->waktu_pembelian ? Carbon::parse($item->waktu_pembelian)->format('d-m-Y') : '',
            $item->no_kk,
            $item->name,
            $item->kategori,
            $item->ruangan,
            $item->total_harga,
        ];
    }
}
