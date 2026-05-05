<?php

namespace App\Exports;

use App\Models\PerbaikanKendaraan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;           // ← Tambahkan ini
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Str;

class PerbaikanKendaraanExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithStyles,
    WithTitle,
    WithCustomStartCell,
    WithEvents                                          // ← Tambahkan ini
{
    protected ?Carbon $from;
    protected Carbon $to;

    public function __construct(?string $from = null, ?string $to = null)
    {
        $this->from = $from ? Carbon::parse($from)->startOfDay() : null;
        $this->to = $to 
            ? Carbon::parse($to)->endOfDay() 
            : Carbon::now()->endOfDay();
    }

    public function title(): string
    {
        return 'Laporan Perbaikan Kendaraan';
    }

    public function startCell(): string
    {
        return 'A5';        // Tabel mulai dari baris ke-5
    }

    public function collection(): Collection
    {
        $query = PerbaikanKendaraan::with(['user.karyawan', 'vendor']);

        if ($this->from) {
            $query->whereBetween('tanggal_kejadian', [$this->from, $this->to]);
        } else {
            $query->where('tanggal_kejadian', '<=', $this->to);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Kendaraan',
            'Driver',
            'Jenis Kondisi',
            'Kondisi Kendaraan',
            'Jenis Perbaikan',
            'Deskripsi Kondisi',
            'Waktu Kejadian',
            'Lokasi',
            'Estimasi',
            'Status',
            'Bukti Foto',
            'Tanggal Perbaikan',
            'Tanggal Selesai',
            'Detail Perbaikan',
            'Dokumen',
            'Invoice',
            'Deskripsi Perbaikan',
            'Vendor',
        ];
    }

    public function map($d): array
    {
        static $no = 1;

        $waktuKejadian = $d->tanggal_kejadian && $d->waktu_kejadian
            ? Carbon::parse($d->tanggal_kejadian)->format('d-m-Y') . ' ' .
              Carbon::parse($d->waktu_kejadian)->format('H:i')
            : '-';

        return [
            $no++,
            $d->kendaraan ?? '-',
            $d->user->karyawan->nama_lengkap ?? '-',
            $d->type_condition ?? '-',
            $d->type_vehicle_condition ?? '-',
            $d->type_repair ?? '-',
            Str::limit($d->deskripsi_kondisi ?? '', 60, '...'),
            $waktuKejadian,
            $d->lokasi ?? '-',
            $d->estimasi ? 'Rp ' . number_format($d->estimasi, 0, ',', '.') : '-',
            $d->status ?? '-',
            $d->bukti ? 'Ada' : 'Tidak Ada',
            $d->tanggal_perbaikan ? Carbon::parse($d->tanggal_perbaikan)->format('d-m-Y') : '-',
            $d->selesai_perbaikan ? Carbon::parse($d->selesai_perbaikan)->format('d-m-Y') : '-',
            Str::limit($d->detail_perbaikan ?? '', 50, '...'),
            $d->document ?? '-',
            $d->invoice ? 'Ada' : 'Tidak Ada',
            Str::limit($d->deskripsi_perbaikan ?? '', 60, '...'),
            $d->vendor->nama ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Judul Utama
            'A1' => [
                'font' => ['bold' => true, 'size' => 16],
            ],
            // Periode & Tanggal Generate
            'A2:A3' => [
                'font' => ['bold' => true],
            ],
            // Header tabel (baris 5)
            5 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ],
            ],
        ];
    }

    /**
     * Menambahkan informasi di atas tabel (Periode & Tanggal Generate)
     */
    public function registerEvents(): array
    {
        return [
            \Maatwebsite\Excel\Events\BeforeSheet::class => function (\Maatwebsite\Excel\Events\BeforeSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Judul Laporan
                $sheet->setCellValue('A1', 'LAPORAN PERBAIKAN KENDARAAN');

                // Periode
                $periode = $this->from
                    ? $this->from->format('d-m-Y') . ' s/d ' . $this->to->format('d-m-Y')
                    : 's/d ' . $this->to->format('d-m-Y');

                $sheet->setCellValue('A2', 'Periode : ' . $periode);

                // Tanggal Generate
                $sheet->setCellValue('A3', 'Tanggal Generate : ' . 
                    Carbon::now()->format('d-m-Y H:i:s') . ' WIB');
            },
        ];
    }
}