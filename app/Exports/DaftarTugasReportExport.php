<?php

namespace App\Exports;

use App\Models\KontrolTugas;
use App\Models\KategoriDaftarTugas;
use Maatwebsite\Excel\Concerns\FromCollection;
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

class DaftarTugasReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle, ShouldAutoSize, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $filterTipe;
    protected $filterStatus;
    protected $filterKaryawan;
    protected $reportType;

    public function __construct($reportType = 'tugas', $startDate = null, $endDate = null, $tipe = null, $status = null, $karyawan = null)
    {
        $this->reportType = $reportType;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->filterTipe = $tipe;
        $this->filterStatus = $status;
        $this->filterKaryawan = $karyawan;
    }

    public function collection()
    {
        if ($this->reportType === 'kategori') {
            $query = KategoriDaftarTugas::with('karyawan');
            
            if ($this->filterKaryawan) {
                $query->where('id_user', $this->filterKaryawan);
            }
            if ($this->filterTipe && $this->filterTipe !== 'all') {
                $query->where('Tipe', $this->filterTipe);
            }
            
            if (Auth::user()->jabatan !== 'HRD') {
                $query->where('id_user', Auth::id());
            }
            
            return $query->orderBy('Tipe')->orderBy('judul_kategori')->get();
        }

        $query = KontrolTugas::with(['kategoriDaftarTugas', 'karyawan']);
        
        if ($this->startDate) {
            $query->whereDate('created_at', '>=', $this->startDate);
        }
        if ($this->endDate) {
            $query->whereDate('created_at', '<=', $this->endDate);
        }
        if ($this->filterTipe && $this->filterTipe !== 'all') {
            $query->whereHas('kategoriDaftarTugas', fn($q) => $q->where('Tipe', $this->filterTipe));
        }
        if ($this->filterStatus !== null) {
            $query->where('status', $this->filterStatus);
        }
        if ($this->filterKaryawan) {
            $query->where('id_karyawan', $this->filterKaryawan);
        }
        if (Auth::user()->jabatan !== 'HRD') {
            $query->where('id_karyawan', Auth::id());
        }

        return $query->orderBy('Deadline_Date')->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        if ($this->reportType === 'kategori') {
            return [
                ['LAPORAN KATEGORI TUGAS OFFICE BOY'],
                ['Diexport pada: ' . Carbon::now()->format('d M Y H:i:s')],
                ['Oleh: ' . (Auth::user()->karyawan->nama_lengkap ?? Auth::user()->username)],
                [],
                [
                    'No', 'Nama Tugas', 'Tipe Frekuensi', 'Penanggung Jawab', 
                    'Jabatan Pembuat', 'Tanggal Dibuat', 'Total Instance'
                ]
            ];
        }

        return [
            ['LAPORAN PELAKSANAAN TUGAS OFFICE BOY'],
            ['Periode: ' . ($this->startDate ? Carbon::parse($this->startDate)->format('d M Y') : 'Awal') . ' s/d ' . ($this->endDate ? Carbon::parse($endDate)->format('d M Y') : 'Sekarang')],
            ['Diexport pada: ' . Carbon::now()->format('d M Y H:i:s')],
            ['Oleh: ' . (Auth::user()->karyawan->nama_lengkap ?? Auth::user()->username)],
            [],
            [
                'No', 'Tanggal Assign', 'Nama Tugas', 'Tipe', 'Office Boy', 
                'Deadline', 'Status', 'Bukti', 'Tanggal Selesai'
            ]
        ];
    }

    public function map($item): array
    {
        if ($this->reportType === 'kategori') {
            $totalInstance = KontrolTugas::where('id_DaftarTugas', $item->id)->count();
            
            return [
                '',
                $item->judul_kategori,
                $item->Tipe,
                $item->karyawan?->nama_lengkap ?? '-',
                $item->jabatan_pembuat ?? '-',
                Carbon::parse($item->created_at)->format('d M Y'),
                $totalInstance
            ];
        }

        $statusText = $item->status == 1 ? 'Selesai' : 'Belum';
        $buktiText = $item->bukti ? 'Ada (' . basename($item->bukti) . ')' : '-';
        $tanggalSelesai = $item->updated_at && $item->status == 1 
            ? Carbon::parse($item->updated_at)->format('d M Y H:i') 
            : '-';

        return [
            '',
            Carbon::parse($item->created_at)->format('d M Y'),
            $item->kategoriDaftarTugas?->judul_kategori ?? '-',
            $item->kategoriDaftarTugas?->Tipe ?? '-',
            $item->karyawan?->nama_lengkap ?? '-',
            Carbon::parse($item->Deadline_Date)->format('d M Y'),
            $statusText,
            $buktiText,
            $tanggalSelesai
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $styles = [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['italic' => true], 'alignment' => ['horizontal' => 'center']],
            3 => ['font' => ['italic' => true], 'alignment' => ['horizontal' => 'center']],
            4 => ['font' => ['italic' => true], 'alignment' => ['horizontal' => 'center']],
        ];
        
        $headerRow = $this->reportType === 'kategori' ? 6 : 7;
        $styles[$headerRow] = ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['argb' => 'FFE0E0E0']]];
        
        return $styles;
    }

    public function title(): string
    {
        return $this->reportType === 'kategori' ? 'Kategori Tugas' : 'Pelaksanaan Tugas';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                
                if ($this->reportType !== 'kategori') {
                    $totalData = KontrolTugas::where('status', 1)->count();
                    $totalPending = KontrolTugas::where('status', 0)->count();
                    
                    $sheet->setCellValue('A' . ($lastRow + 2), 'RINGKASAN:');
                    $sheet->setCellValue('A' . ($lastRow + 3), 'Total Tugas Selesai: ' . $totalData);
                    $sheet->setCellValue('A' . ($lastRow + 4), 'Total Tugas Pending: ' . $totalPending);
                    $sheet->setCellValue('A' . ($lastRow + 5), 'Persentase Completion: ' . ($totalData + $totalPending > 0 ? round(($totalData / ($totalData + $totalPending)) * 100, 1) . '%' : '0%'));
                    
                    $sheet->getStyle('A' . ($lastRow + 2) . ':A' . ($lastRow + 5))->getFont()->setBold(true);
                }
            },
        ];
    }
}