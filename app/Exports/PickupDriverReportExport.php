<?php

namespace App\Exports;

use App\Models\pickupDriver;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class PickupDriverReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $filterKendaraan;
    protected $filterStatus;

    public function __construct($startDate = null, $endDate = null, $kendaraan = null, $status = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filterKendaraan = $kendaraan;
        $this->filterStatus = $status;
    }

    public function collection()
    {
        $query = pickupDriver::with([
            'karyawan',
            'pembuat',
            'detailPickupDriver',
            'Tracking',
            'biayaTransportasi' => function ($q) {
                $q->with('karyawan');
            },
        ]);

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }
        if ($this->filterKendaraan) {
            $query->where('kendaraan', $this->filterKendaraan);
        }
        if ($this->filterStatus !== null) {
            $query->where('status_apply', $this->filterStatus);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        $periodeStart = $this->startDate ? Carbon::parse($this->startDate)->format('d M Y') : 'Awal';
        $periodeEnd = $this->endDate ? Carbon::parse($this->endDate)->format('d M Y') : 'Sekarang';
        $exportedAt = Carbon::now()->format('d M Y H:i:s');

        return [['LAPORAN KOORDINASI DRIVER & BIAYA TRANSPORTASI'], ["Periode: {$periodeStart} s/d {$periodeEnd}"], ["Diexport pada: {$exportedAt}"], [], ['No', 'Tanggal Koordinasi', 'Driver', 'Pembuat', 'Kendaraan', 'Tipe Perjalanan', 'Budget', 'Total Biaya', 'Sisa Budget', 'Kilometer Awal', 'Kilometer Akhir', 'Status', 'Detail Rute', 'Waktu Kepulangan', 'Tracking Terakhir']];
    }

    public function map($pickup): array
    {
        $totalBiaya = $pickup->biayaTransportasi->sum('harga');
        $sisaBudget = $pickup->budget ? $pickup->budget - $totalBiaya : null;

        $detailRute = $pickup->detailPickupDriver
            ->map(function ($detail) {
                $tanggal = $detail->tanggal_keberangkatan ?? '';
                $waktu = $detail->waktu_keberangkatan ?? '';
                return "{$detail->tipe}: {$detail->lokasi} ({$tanggal} {$waktu})";
            })
            ->implode("\n");

        $trackingTerakhir = $pickup->Tracking->sortByDesc('created_at')->first();

        $statusText = match ($pickup->status_apply) {
            0 => 'Menunggu',
            1 => 'Diterima',
            2 => 'Selesai',
            default => 'Unknown',
        };

        $createdAt = $pickup->created_at ? Carbon::parse($pickup->created_at)->format('d M Y') : '-';
        $waktuKepulangan = $pickup->waktu_kepulangan ? Carbon::parse($pickup->waktu_kepulangan)->format('d M Y H:i') : '-';

        return ['', $createdAt, $pickup->karyawan?->nama_lengkap ?? '-', $pickup->pembuat?->karyawan?->nama_lengkap ?? ($pickup->pembuat?->username ?? '-'), $pickup->kendaraan ?? '-', $pickup->tipe_perjalanan ?? '-', $pickup->budget ? 'Rp ' . number_format($pickup->budget, 0, ',', '.') : '-', 'Rp ' . number_format($totalBiaya, 0, ',', '.'), $sisaBudget !== null ? 'Rp ' . number_format($sisaBudget, 0, ',', '.') : '-', $pickup->KM_awal ?? '-', $pickup->KM_akhir ?? '-', $statusText, $detailRute, $waktuKepulangan, $trackingTerakhir?->status ?? '-'];
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
        return 'Laporan Pickup Driver';
    }
}
