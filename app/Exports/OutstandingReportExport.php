<?php

namespace App\Exports;

use App\Models\Outstanding;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    ShouldAutoSize,
    WithEvents,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OutstandingReportExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    WithTitle,
    ShouldAutoSize,
    WithEvents
{
    protected $filters;
    protected $rowNumber = 0;
    protected $potonganTypes = [];
    protected $data;
    protected $isDataLoaded = false;

    public function __construct($reportType = 'tugas', $filters = [])
    {
        $this->filters = collect($filters);
    }

    protected function baseQuery()
    {
        $query = Outstanding::with(['rkm.perusahaan', 'rkm.materi', 'rkm.sales', 'rkm.invoice']);

        if ($this->filters->get('start_date') && $this->filters->get('end_date')) {
            return $query->whereBetween('created_at', [
                $this->filters->get('start_date') . ' 00:00:00',
                $this->filters->get('end_date') . ' 23:59:59'
            ]);
        }

        $preset  = $this->filters->get('filter_preset');
        $year    = $this->filters->get('filter_year');
        $month   = $this->filters->get('filter_month');
        $quarter = $this->filters->get('filter_quarter');

        if ($preset === 'tahun' && $year) {
            $query->whereYear('created_at', $year);
        }

        if ($preset === 'bulan' && $year && $month) {
            $query->whereYear('created_at', $year)
                  ->whereMonth('created_at', $month);
        }

        if ($preset === 'triwulan' && $year && $quarter) {
            $quarters = [
                1 => [1, 3],
                2 => [4, 6],
                3 => [7, 9],
                4 => [10, 12]
            ];

            if (isset($quarters[$quarter])) {
                [$start, $end] = $quarters[$quarter];

                $query->whereYear('created_at', $year)
                      ->whereBetween(DB::raw('MONTH(created_at)'), [$start, $end]);
            }
        }

        if ($this->filters->get('karyawan')) {
            $query->whereHas('rkm.sales', fn ($q) =>
                $q->where('id', $this->filters->get('karyawan'))
            );
        }

        return $query;
    }

    public function collection()
    {
        if ($this->isDataLoaded) {
            return $this->data;
        }

        $this->data = $this->baseQuery()
            ->orderBy('due_date')
            ->orderBy('created_at', 'desc')
            ->get();

        $this->isDataLoaded = true;
        $this->loadPotonganTypes();

        return $this->data;
    }

    protected function decodeJsonField($value)
    {
        if (empty($value)) return null;

        $decoded = is_string($value) ? json_decode($value, true) : $value;

        return is_string($decoded) ? json_decode($decoded, true) : $decoded;
    }

    protected function loadPotonganTypes()
    {
        if (!empty($this->potonganTypes)) return;

        $types = [];

        foreach ($this->collection() as $item) {
            $attr = $item->getAttributes();

            $jenis = $this->decodeJsonField($attr['jenis_potongan'] ?? null);
            if (is_array($jenis)) {
                foreach ($jenis as $j) {
                    if (!empty($j)) $types[] = trim($j);
                }
            }

            $jumlah = $this->decodeJsonField($attr['jumlah_potongan'] ?? null);
            if (is_array($jumlah)) {
                foreach ($jumlah as $p) {
                    if (!empty($p['jenis'])) {
                        $types[] = trim($p['jenis']);
                    }
                }
            }
        }

        $this->potonganTypes = array_values(array_unique($types));
    }

    public function headings(): array
    {
        $this->loadPotonganTypes();

        $meta = [
            ['LAPORAN OUTSTANDING'],
            ['Periode: ' . $this->getPeriodLabel()],
            ['Diexport pada: ' . Carbon::now()->format('d M Y H:i:s')],
            ['Oleh: ' . (Auth::user()->username ?? 'System')],
            []
        ];

        $columns = array_merge(
            [
                'No',
                'Perusahaan',
                'Kelas',
                'Sales',
                'Tanggal',
                'Tagihan',
                'Jatuh Tempo',
                'Tanggal Bayar',
                'Nominal Pembayaran',
            ],
            $this->potonganTypes,
            ['Uang Diterima', 'Status', 'Info']
        );

        return array_merge($meta, [$columns]);
    }

    protected function getPeriodLabel(): string
    {
        if ($this->filters->get('start_date') && $this->filters->get('end_date')) {
            return Carbon::parse($this->filters->get('start_date'))->format('d M Y')
                . ' s/d ' .
                Carbon::parse($this->filters->get('end_date'))->format('d M Y');
        }

        $preset  = $this->filters->get('filter_preset');
        $year    = $this->filters->get('filter_year');
        $month   = $this->filters->get('filter_month');
        $quarter = $this->filters->get('filter_quarter');

        if ($preset === 'tahun' && $year) return "Tahun $year";

        if ($preset === 'bulan' && $year && $month) {
            return Carbon::create()->month($month)->translatedFormat('F') . " $year";
        }

        if ($preset === 'triwulan' && $year && $quarter) {
            return "Triwulan $quarter / $year";
        }

        return 'Semua Data Outstanding';
    }

    public function map($item): array
    {
        $this->rowNumber++;
        $this->loadPotonganTypes();

        $rkm  = $item->rkm;
        $attr = $item->getAttributes();

        $potonganData = [];

        $decoded = $this->decodeJsonField($attr['jumlah_potongan'] ?? null);
        if (is_array($decoded)) {
            foreach ($decoded as $p) {
                if (!empty($p['jenis'])) {
                    $potonganData[trim($p['jenis'])] = $p['jumlah'] ?? 0;
                }
            }
        }

        $mappedPotongan = array_map(function ($type) use ($potonganData) {
            $val = $potonganData[$type] ?? 0;
            return $val > 0 ? number_format($val, 0, ',', '.') : '0';
        }, $this->potonganTypes);

        $tanggalAkhir = optional($rkm)->tanggal_akhir;
        $amount = optional($rkm?->invoice)->amount;

        $jatuhTempo = $tanggalAkhir
            ? Carbon::parse($tanggalAkhir)->addMonths(6)->format('d M Y')
            : '-';

        return array_merge(
            [
                $this->rowNumber,
                optional($rkm?->perusahaan)->nama_perusahaan ?? '-',
                optional($rkm?->materi)->nama_materi ?? '-',
                optional($rkm?->sales)->nama_lengkap ?? '-',
                $tanggalAkhir ?? '-',
                optional($rkm?->invoice)->amount ?? '-',
                $jatuhTempo,
                $item->tanggal_bayar
                    ? Carbon::parse($item->tanggal_bayar)->format('d M Y H:i')
                    : '-',
                optional($rkm?->invoice)->amount ?? '-',
            ],
            $mappedPotongan,
            [
                number_format($item->jumlah_pembayaran ?? 0, 0, ',', '.'),
                $item->tanggal_bayar ? 'LUNAS' : 'BELUM BAYAR',
                $amount ? 'Sesuai' : ' ',
            ]
        );
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            2 => ['font' => ['bold' => true]],
            3 => ['font' => ['bold' => true]],
            4 => ['font' => ['bold' => true]],
            6 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'CCCCCC']
                ]
            ],
        ];
    }

    public function title(): string
    {
        return 'Outstanding';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {
                $sheet = $event->sheet->getDelegate();

                $lastColumn = $sheet->getHighestColumn();
                $sheet->mergeCells("A1:{$lastColumn}1");

                $sheet->getStyle('A1')
                    ->getAlignment()
                    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $lastRow = $sheet->getHighestRow();
                $total   = $this->collection()->sum('jumlah_pembayaran');

                $rowTotal = $lastRow + 2;

                $sheet->setCellValue(
                    "A{$rowTotal}",
                    'Total Uang Diterima: Rp ' . number_format($total, 0, ',', '.')
                );

                $sheet->getStyle("A{$rowTotal}")
                    ->getFont()
                    ->setBold(true);
            }
        ];
    }
}