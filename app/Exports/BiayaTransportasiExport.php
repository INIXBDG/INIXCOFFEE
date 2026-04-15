<?php

namespace App\Exports;

use App\Models\BiayaTransportasiDriver;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class BiayaTransportasiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $filterTipe;
    protected $filterStatus;

    public function __construct($startDate = null, $endDate = null, $tipe = null, $status = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filterTipe = $tipe;
        $this->filterStatus = $status;
    }

    public function collection()
    {
        $query = BiayaTransportasiDriver::with(['pickupDriver.karyawan', 'pickupDriver.detailPickupDriver', 'PengajuanBarang.tracking']);

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }
        if ($this->filterTipe) {
            $query->where('tipe', $this->filterTipe);
        }
        if ($this->filterStatus) {
            $query->whereHas('PengajuanBarang.tracking', function ($q) {
                $q->where('tracking', 'like', "%{$this->filterStatus}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [['LAPORAN BIAYA TRANSPORTASI DRIVER'], ['Periode: ' . ($this->startDate ? Carbon::parse($this->startDate)->format('d M Y') : 'Awal') . ' s/d ' . ($this->endDate ? Carbon::parse($this->endDate)->format('d M Y') : 'Sekarang')], ['Diexport pada: ' . Carbon::now()->format('d M Y H:i:s')], [], ['No', 'Bulan','Minggu','Tanggal', 'Driver', 'Koordinasi', 'Tipe Biaya', 'Harga', 'Keterangan', 'Status']];
    }

    public function map($biaya): array
    {
        $status = $biaya->PengajuanBarang?->tracking?->tracking ?? 'Menunggu';
        $koordinasi = '-';

        if ($biaya->id_pickup_driver == 999999999) {
            $koordinasi = 'Diluar Koordinasi Driver';
        } elseif ($biaya->pickupDriver) {
            $driver = $biaya->pickupDriver->karyawan?->nama_lengkap ?? '-';
            $lokasi = $biaya->pickupDriver->detailPickupDriver->first()->lokasi ?? '-';
            $koordinasi = "{$driver} | {$lokasi}";
        }

        return ['', Carbon::parse($biaya->created_at)->format('M'), ceil(Carbon::parse($biaya->created_at)->day / 7), Carbon::parse($biaya->created_at)->format('d M Y'), $biaya->pickupDriver?->karyawan?->nama_lengkap ?? '-', $koordinasi, $biaya->tipe, 'Rp ' . number_format($biaya->harga, 0, ',', '.'), $biaya->keterangan ?? '-', $status];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['italic' => true], 'alignment' => ['horizontal' => 'center']],
            3 => ['font' => ['italic' => true], 'alignment' => ['horizontal' => 'center']],
            5 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFE0E0E0']]],
        ];
    }

    public function title(): string
    {
        return 'Biaya Transportasi';
    }
}
