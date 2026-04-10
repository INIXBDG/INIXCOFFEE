<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Outstanding;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class OutstandingReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $filterTipe;
    protected $filterStatus;
    protected $filterKaryawan;
    protected $reportType;
    protected $rowNumber = 0; // ✅ Counter persisten untuk nomor urut

    public function __construct($reportType = 'tugas', $startDate = null, $endDate = null, $tipe = null, $status = null, $karyawan = null)
    {
        $this->reportType = $reportType;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filterTipe = $tipe;
        $this->filterStatus = $status;
        $this->filterKaryawan = $karyawan;
    }

    protected function formatJsonPotongan($value, $type = 'jumlah')
    {
        if (empty($value)) {
            return '-';
        }

        $data = is_string($value) ? json_decode($value, true) : $value;

        if (!is_array($data) || empty($data)) {
            return '-';
        }

        // Cek jika array numerik sederhana
        if (count($data) === count(array_filter(array_keys($data), 'is_numeric')) && !isset($data[0]['jenis']) && !isset($data[0]['nilai'])) {
            return implode(', ', array_map(fn($v) => number_format((float)$v, 0, ',', '.'), $data));
        }

        $result = [];
        foreach ($data as $item) {
            if (is_array($item)) {
                if ($type === 'jenis') {
                    $result[] = $item['jenis'] ?? ($item['type'] ?? ($item['name'] ?? '-'));
                } else {
                    $val = $item['nilai'] ?? ($item['amount'] ?? ($item['jumlah'] ?? 0));
                    $result[] = number_format((float)$val, 0, ',', '.');
                }
            }
        }
        return empty($result) ? '-' : implode('; ', $result);
    }

    public function collection()
    {
        $query = Outstanding::with(['rkm.perusahaan', 'rkm.materi', 'rkm.sales', 'tracking_outstanding']);

        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }

        if ($this->filterTipe === 'Outstanding PA') {
            $query->where(function ($q) {
                $q->whereNotNull('net_sales')->where('net_sales', '!=', 0)->where('net_sales', '!=', '0.00');
            });
        } elseif ($this->filterTipe === 'Lunas') {
            $query->where('status_pembayaran', 1);
        }

        return $query->orderBy('due_date')->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        $start = $this->startDate ? Carbon::parse($this->startDate)->format('d M Y') : 'Awal';
        $end = $this->endDate ? Carbon::parse($this->endDate)->format('d M Y') : 'Sekarang';
        $user = Auth::check() ? (Auth::user()->karyawan->nama_lengkap ?? Auth::user()->username) : 'System';

        $title = match($this->filterTipe) {
            'Outstanding PA' => 'LAPORAN OUTSTANDING PA',
            'Lunas' => 'LAPORAN OUTSTANDING LUNAS',
            default => 'LAPORAN OUTSTANDING'
        };

        $commonHeaders = [
            [$title],
            ['Periode: ' . $start . ' s/d ' . $end],
            ['Diexport pada: ' . Carbon::now()->format('d M Y H:i:s')],
            ['Oleh: ' . $user],
            []
        ];

        if ($this->filterTipe === 'Outstanding PA') {
            $dataHeaders = ['No', 'Perusahaan', 'Materi', 'Periode Latihan', 'Net Sales', 'PIC', 'Sales'];
        } elseif ($this->filterTipe === 'Lunas') {
            $dataHeaders = [
                'No', 'Perusahaan', 'Materi', 'Periode Latihan', 'Net Sales', 'PIC', 'Sales',
                'Tenggat Waktu', 'Status Pembayaran', 'Jenis Potongan', 'Jumlah Potongan',
                'Jumlah Pembayaran', 'Tanggal Bayar',
            ];
        } else {
            $dataHeaders = [
                'No', 'Perusahaan', 'Materi', 'Periode Latihan', 'Net Sales', 'PIC', 'Sales',
                'Tenggat Waktu', 'Status Pembayaran', 'Invoice', 'Faktur Pajak', 'Dokumen Tambahan',
                'Konfirmasi Pengiriman RPX', 'Konfirmasi No. Resi', 'Status Pengiriman',
                'Konfirmasi PIC', 'Pembayaran', 'No. Resi', 'Keterangan PIC'
            ];
        }

        $commonHeaders[] = $dataHeaders;
        return $commonHeaders;
    }

    public function map($item): array
    {
        $this->rowNumber++; // ✅ Increment counter persisten
        $rkm = $item->rkm;
        $perusahaan = optional($rkm?->perusahaan)->nama_perusahaan ?? '-';
        $materi = optional($rkm?->materi)->nama_materi ?? '-';
        $sales = optional($rkm?->sales)->nama_lengkap ?? '-';
        $periode = ($rkm?->tanggal_awal && $rkm?->tanggal_akhir) 
            ? Carbon::parse($rkm->tanggal_awal)->format('d M Y') . ' s/d ' . Carbon::parse($rkm->tanggal_akhir)->format('d M Y') 
            : '-';

        if ($this->filterTipe === 'Outstanding PA') {
            return [
                $this->rowNumber,
                $perusahaan,
                $materi,
                $periode,
                number_format((float)($item->net_sales ?? 0), 0, ',', '.'),
                $item->pic ?? '-',
                $sales
            ];
        } elseif ($this->filterTipe === 'Lunas') {
            return [
                $this->rowNumber,
                $perusahaan,
                $materi,
                $periode,
                number_format((float)($item->net_sales ?? 0), 0, ',', '.'),
                $item->pic ?? '-',
                $sales,
                $item->due_date ? Carbon::parse($item->due_date)->format('d M Y') : '-',
                $item->status_pembayaran === '1' ? 'Lunas' : 'Belum',
                $this->formatJsonPotongan($item->jenis_potongan, 'jenis'),
                $this->formatJsonPotongan($item->jumlah_potongan, 'jumlah'),
                number_format((float)($item->jumlah_pembayaran ?? 0), 0, ',', '.'),
                $item->tanggal_bayar ? Carbon::parse($item->tanggal_bayar)->format('d M Y H:i') : '-',
            ];
        } else {
            $tracking = $item->tracking_outstanding;
            return [
                $this->rowNumber,
                $perusahaan,
                $materi,
                $periode,
                number_format((float)($item->net_sales ?? 0), 0, ',', '.'),
                $item->pic ?? '-',
                $sales,
                $item->due_date ? Carbon::parse($item->due_date)->format('d M Y') : '-',
                $item->status_pembayaran === '1' ? 'Lunas' : 'Belum',
                optional($tracking)->invoice ? '(Ada)' : '-',
                optional($tracking)->faktur_pajak ? 'Ada' : '-',
                optional($tracking)->dokumen_tambahan ? 'Ada' : '-',
                optional($tracking)->konfir_cs ? 'Ada' : '-',
                optional($tracking)->no_resi ? 'Ada' : '-',
                optional($tracking)->status_resi ? 'Ada' : '-',
                optional($tracking)->konfir_pic ? 'Ada' : '-',
                optional($tracking)->pembayaran ? 'Ada' : '-',
                optional($tracking)->status_resi ?? '-',
                optional($tracking)->keterangan_pic ?? '-',
            ];
        }
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['italic' => true], 'alignment' => ['horizontal' => 'center']],
            3 => ['font' => ['italic' => true], 'alignment' => ['horizontal' => 'center']],
            4 => ['font' => ['italic' => true], 'alignment' => ['horizontal' => 'center']],
            6 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFE0E0E0']]],
        ];
    }

    public function title(): string
    {
        return $this->reportType === 'kategori' ? 'Kategori Tugas' : 'Pelaksanaan Tugas';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();

                $summaryRow = $lastRow + 2;
                $sheet->setCellValue('A' . $summaryRow, 'RINGKASAN:');
                $sheet->setCellValue('A' . ($summaryRow + 1), 'Tipe Filter: ' . ($this->filterTipe ?? 'Semua'));
                
                $sheet->getStyle('A' . $summaryRow . ':A' . ($summaryRow + 1))
                    ->getFont()
                    ->setBold(true);
            },
        ];
    }
}