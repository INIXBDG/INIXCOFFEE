<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SuratPerjalanan;
use App\Models\karyawan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RekapSJPController extends Controller
{
    public function index()
    {
        $divisis = karyawan::whereNotNull('divisi')->distinct()->pluck('divisi');
        return view('HR.RekapSPJ.index', compact('divisis'));
    }

    public function getJabatan($divisi)
    {
        $data = karyawan::where('divisi', $divisi)->select('jabatan')->distinct()->pluck('jabatan');
        return response()->json($data);
    }

    public function getKaryawan($jabatan)
    {
        $data = karyawan::where('jabatan', $jabatan)->pluck('nama_lengkap', 'id');
        return response()->json($data);
    }

    private function calculateTotal($items)
    {
        return $items->sum(function ($item) {
            $val = (string) ($item->total ?? 0);
            if (substr_count($val, '.') > 1) {
                $val = str_replace('.', '', $val);
            }
            $val = str_replace(',', '.', $val);
            return (float) $val;
        });
    }

    public function getRekapData(Request $request)
    {
        $baseQuery = SuratPerjalanan::with('karyawan');

        if ($request->filled('divisi')) {
            $baseQuery->whereHas('karyawan', fn($q) => $q->where('divisi', $request->divisi));
        }
        if ($request->filled('jabatan')) {
            $baseQuery->whereHas('karyawan', fn($q) => $q->where('jabatan', $request->jabatan));
        }
        if ($request->filled('karyawan')) {
            $baseQuery->where('id_karyawan', $request->karyawan);
        }

        $tahun = $request->input('tahun', date('Y'));
        $baseQuery->whereYear('tanggal_berangkat', $tahun);

        $mode = $request->input('mode_periode', 'semua');
        if ($mode == 'bulan' && $request->filled('bulan')) {
            $baseQuery->whereMonth('tanggal_berangkat', $request->bulan);
        } elseif ($mode == 'quartal' && $request->filled('quartal')) {
            $startMonth = ($request->quartal - 1) * 3 + 1;
            $endMonth = $startMonth + 2;
            $baseQuery->whereBetween(DB::raw('MONTH(tanggal_berangkat)'), [$startMonth, $endMonth]);
        }

        $data = $baseQuery->get();

        $prevQuery = SuratPerjalanan::with('karyawan');

        if ($request->filled('divisi')) {
            $prevQuery->whereHas('karyawan', fn($q) => $q->where('divisi', $request->divisi));
        }
        if ($request->filled('jabatan')) {
            $prevQuery->whereHas('karyawan', fn($q) => $q->where('jabatan', $request->jabatan));
        }
        if ($request->filled('karyawan')) {
            $prevQuery->where('id_karyawan', $request->karyawan);
        }

        if ($mode == 'bulan' && $request->filled('bulan')) {
            $prevMonth = $request->bulan == 1 ? 12 : $request->bulan - 1;
            $prevYear = $request->bulan == 1 ? $tahun - 1 : $tahun;
            $prevQuery->whereYear('tanggal_berangkat', $prevYear)->whereMonth('tanggal_berangkat', $prevMonth);
        } elseif ($mode == 'quartal' && $request->filled('quartal')) {
            $prevQ = $request->quartal == 1 ? 4 : $request->quartal - 1;
            $prevYear = $request->quartal == 1 ? $tahun - 1 : $tahun;
            $startMonth = ($prevQ - 1) * 3 + 1;
            $endMonth = $startMonth + 2;
            $prevQuery->whereYear('tanggal_berangkat', $prevYear)->whereBetween(DB::raw('MONTH(tanggal_berangkat)'), [$startMonth, $endMonth]);
        } else {
            $prevQuery->whereYear('tanggal_berangkat', $tahun - 1);
        }

        $prevData = $prevQuery->get();
        $prevGroupedByTipe = $prevData->groupBy('tipe')->map(function ($items) {
            return $this->calculateTotal($items);
        });
        $prevGroupedByDivisi = $prevData->groupBy(fn($item) => $item->karyawan->divisi ?? 'Tidak Ada Divisi')->map(function ($items) {
            return $this->calculateTotal($items);
        });

        $tab1 = $data
            ->groupBy(fn($item) => $item->karyawan->divisi ?? 'Tidak Ada Divisi')
            ->map(
                fn($items, $divisi) => [
                    'divisi' => $divisi,
                    'total' => $this->calculateTotal($items),
                    'jumlah_spj' => $items->count(),
                ],
            )
            ->sortByDesc('total')
            ->values()
            ->all();

        $tab2 = [];
        if ($mode == 'semua' || $mode == 'tahun') {
            $tab2 = $data
                ->groupBy(fn($item) => ceil(Carbon::parse($item->tanggal_berangkat)->month / 3))
                ->map(fn($items, $q) => [
                    'periode' => "Quartal {$q} - {$tahun}",
                    'total' => $this->calculateTotal($items),
                    'jumlah_spj' => $items->count(),
                    'filter_mode' => 'quartal',
                    'filter_value' => $q,
                ])
                ->values()
                ->all();
        } elseif ($mode == 'quartal') {
            $tab2 = $data
                ->groupBy(fn($item) => Carbon::parse($item->tanggal_berangkat)->translatedFormat('F'))
                ->map(fn($items, $month) => [
                    'periode' => $month,
                    'total' => $this->calculateTotal($items),
                    'jumlah_spj' => $items->count(),
                    'filter_mode' => 'bulan',
                    'filter_value' => Carbon::parse($items->first()->tanggal_berangkat)->month,
                ])
                ->values()
                ->all();
        } elseif ($mode == 'bulan') {
            $tab2 = $data
                ->groupBy(fn($item) => Carbon::parse($item->tanggal_berangkat)->translatedFormat('d F Y'))
                ->map(fn($items, $date) => [
                    'periode' => $date,
                    'total' => $this->calculateTotal($items),
                    'jumlah_spj' => $items->count(),
                    'filter_mode' => 'tanggal',
                    'filter_value' => Carbon::parse($items->first()->tanggal_berangkat)->format('Y-m-d'),
                ])
                ->values()
                ->all();
        }

        $tabJenis = $data
            ->groupBy(function ($item) {
                return $item->jenis_dinas ?? 'Lain Lain (tidak terdata)';
            })
            ->map(function ($items, $jenis) {
                return [
                    'jenis_dinas' => $jenis,
                    'total'       => $this->calculateTotal($items),
                    'jumlah_spj'  => $items->count(),
                ];
            })
            ->sortByDesc('total')
            ->values()
            ->all();

        $prevGroupedByJenis = $prevData->groupBy('jenis_dinas')->map(function ($items) {
            return $this->calculateTotal($items);
        });

        $totalAll = $this->calculateTotal($data);
        $tab3Collection = $data
            ->groupBy('tipe')
            ->map(function ($items, $tipe) use ($totalAll, $prevGroupedByTipe) {
                $totalTipe = $this->calculateTotal($items);
                $percentage = $totalAll > 0 ? ($totalTipe / $totalAll) * 100 : 0;
                $prevTipeTotal = $prevGroupedByTipe[$tipe] ?? 0;
                $gap = 0;
                if ($prevTipeTotal > 0) {
                    $gap = round((($totalTipe - $prevTipeTotal) / $prevTipeTotal) * 100, 2);
                } elseif ($totalTipe > 0) {
                    $gap = 100;
                }
                return [
                    'kategori' => $tipe ?? 'Lainnya',
                    'total' => $totalTipe,
                    'prev_total' => $prevTipeTotal,
                    'persentase' => round($percentage, 2),
                    'gap' => $gap,
                ];
            })
            ->sortByDesc('total');

        $tab3 = $tab3Collection->values()->all();

        $divisiCollection = $data
            ->groupBy(fn($item) => $item->karyawan->divisi ?? 'Tidak Ada Divisi')
            ->map(function ($items, $divisi) use ($prevGroupedByDivisi) {
                return [
                    'divisi' => $divisi,
                    'current' => $this->calculateTotal($items),
                    'previous' => $prevGroupedByDivisi[$divisi] ?? 0,
                ];
            })
            ->sortByDesc('current');

        $chartDivisi = [
            'labels' => $divisiCollection->pluck('divisi')->toArray(),
            'current' => $divisiCollection->pluck('current')->map(fn($v) => (float)$v)->toArray(),
            'previous' => $divisiCollection->pluck('previous')->map(fn($v) => (float)$v)->toArray(),
        ];

        $chartData = [
            'labels' => $tab3Collection->pluck('kategori')->toArray(),
            'current' => $tab3Collection->pluck('total')->map(fn($v) => (float) $v)->toArray(),
            'previous' => $tab3Collection->pluck('prev_total')->map(fn($v) => (float) $v)->toArray(),
            'percentages' => $tab3Collection->pluck('persentase')->map(fn($v) => (float) $v)->toArray(),
        ];

        $tabJenisStat = $data
            ->groupBy('jenis_dinas')
            ->map(function ($items, $jenis) use ($totalAll, $prevGroupedByJenis) {
                $totalJenis = $this->calculateTotal($items);
                $percentage = $totalAll > 0 ? ($totalJenis / $totalAll) * 100 : 0;
                $prevTotal = $prevGroupedByJenis[$jenis] ?? 0;
                $gap = 0;
                if ($prevTotal > 0) {
                    $gap = round((($totalJenis - $prevTotal) / $prevTotal) * 100, 2);
                } elseif ($totalJenis > 0) {
                    $gap = 100;
                }
                return [
                    'kategori'    => $jenis ?? 'Tidak Ada Jenis',
                    'total'       => $totalJenis,
                    'prev_total'  => $prevTotal,
                    'persentase'  => round($percentage, 2),
                    'gap'         => $gap,
                ];
            })
            ->sortByDesc('total');

        $chartJenis = [
            'labels'    => $tabJenisStat->pluck('kategori')->toArray(),
            'current'   => $tabJenisStat->pluck('total')->map(fn($v) => (float)$v)->toArray(),
            'previous'  => $tabJenisStat->pluck('prev_total')->map(fn($v) => (float)$v)->toArray(),
        ];

        return response()->json([
            'success' => true,
            'tab1' => $tab1,
            'tab2' => $tab2,
            'tab3' => $tab3,
            'tabJenis'     => $tabJenis,
            'chart' => $chartData,
            'chart_divisi' => $chartDivisi,
            'grand_total' => $totalAll,
            'chart_jenis'  => $chartJenis,    
        ]);
    }

    public function getDetailData(Request $request)
    {
        $query = SuratPerjalanan::with('karyawan');

        if ($request->filled('divisi')) $query->whereHas('karyawan', fn($q) => $q->where('divisi', $request->divisi));
        if ($request->filled('jabatan')) $query->whereHas('karyawan', fn($q) => $q->where('jabatan', $request->jabatan));
        if ($request->filled('karyawan')) $query->where('id_karyawan', $request->karyawan);
        $tipe = $request->input('tipe');

        $tahun = $request->input('tahun', date('Y'));
        $query->whereYear('tanggal_berangkat', $tahun);

        $tipe = $request->input('tipe');
        
        if ($tipe == 'divisi') {
            $query->whereHas('karyawan', fn($q) => $q->where('divisi', $request->nilai));
            
            $mode = $request->input('mode_periode', 'semua');
            if ($mode == 'bulan' && $request->filled('bulan')) {
                $query->whereMonth('tanggal_berangkat', $request->bulan);
            } elseif ($mode == 'quartal' && $request->filled('quartal')) {
                $startMonth = ($request->quartal - 1) * 3 + 1;
                $endMonth = $startMonth + 2;
                $query->whereBetween(DB::raw('MONTH(tanggal_berangkat)'), [$startMonth, $endMonth]);
            }
        } elseif ($tipe == 'periode') {
            $filterMode = $request->input('filter_mode');
            $filterValue = $request->input('filter_value');
            
            if ($filterMode == 'quartal') {
                $startMonth = ($filterValue - 1) * 3 + 1;
                $endMonth = $startMonth + 2;
                $query->whereBetween(DB::raw('MONTH(tanggal_berangkat)'), [$startMonth, $endMonth]);
            } elseif ($filterMode == 'bulan') {
                $query->whereMonth('tanggal_berangkat', $filterValue);
            } elseif ($filterMode == 'tanggal') {
                $query->whereDate('tanggal_berangkat', $filterValue);
            }
        } elseif ($tipe == 'jenis_dinas') {
            if ($request->filled('nilai')) {
                $query->where('jenis_dinas', $request->nilai);
            }
        }

        $data = $query->orderBy('tanggal_berangkat', 'desc')->get();

        $result = $data->map(function($item) {
            $namaLengkap = $item->karyawan->nama_lengkap ?? 'Unknown';
            return [
                'tanggal' => Carbon::parse($item->tanggal_berangkat)->translatedFormat('d F Y'),
                'nama' => explode(' ', $namaLengkap)[0],
                'divisi' => $item->karyawan->divisi ?? '-',
                'jabatan' => $item->karyawan->jabatan ?? '-',
                'tipe' => $item->tipe ?? '-',
                'tujuan' => $item->tujuan ?? '-',
                'alasan' => $item->alasan ?? '-',
                'total' => $item->total,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    public function export(Request $request)
    {
        $response = $this->getRekapData($request);
        $data = json_decode($response->getContent(), true);

        $tab1 = $data['tab1'];
        $tab2 = $data['tab2'];
        $tab3 = $data['tab3'];
        $tabJenis = $data['tabJenis'] ?? [];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap SPJ');

        $currentRow = 1;

        // Judul
        $sheet->mergeCells('A' . $currentRow . ':D' . $currentRow);
        $sheet->setCellValue('A' . $currentRow, 'Rekapitulasi Surat Perjalanan Dinas (SPJ)');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $currentRow++;

        $sheet->mergeCells('A' . $currentRow . ':D' . $currentRow);
        $sheet->setCellValue('A' . $currentRow, 'Tanggal Cetak: ' . now()->translatedFormat('l, d F Y'));
        $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $currentRow += 2;

        // Tab 1: Rekap Per Divisi
        $sheet->mergeCells('A' . $currentRow . ':D' . $currentRow);
        $sheet->setCellValue('A' . $currentRow, '1. Rekap Per Divisi');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        $sheet->setCellValue('A' . $currentRow, 'No');
        $sheet->setCellValue('B' . $currentRow, 'Nama Divisi');
        $sheet->setCellValue('C' . $currentRow, 'Jumlah SPJ');
        $sheet->setCellValue('D' . $currentRow, 'Total Pengeluaran');
        $sheet->getStyle('A' . $currentRow . ':D' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        $total1 = 0;
        foreach ($tab1 as $index => $item) {
            $total1 += $item['total'];
            $sheet->setCellValue('A' . $currentRow, $index + 1);
            $sheet->setCellValue('B' . $currentRow, $item['divisi']);
            $sheet->setCellValue('C' . $currentRow, $item['jumlah_spj']);
            $sheet->setCellValue('D' . $currentRow, $item['total']);
            $currentRow++;
        }
        $sheet->setCellValue('C' . $currentRow, 'TOTAL:');
        $sheet->setCellValue('D' . $currentRow, $total1);
        $sheet->getStyle('C' . $currentRow . ':D' . $currentRow)->getFont()->setBold(true);
        $currentRow += 2;

        // Tab 2: Rekap Per Periode
        $sheet->mergeCells('A' . $currentRow . ':D' . $currentRow);
        $sheet->setCellValue('A' . $currentRow, '2. Rekap Per Periode');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        $sheet->setCellValue('A' . $currentRow, 'No');
        $sheet->setCellValue('B' . $currentRow, 'Periode');
        $sheet->setCellValue('C' . $currentRow, 'Jumlah SPJ');
        $sheet->setCellValue('D' . $currentRow, 'Total Pengeluaran');
        $sheet->getStyle('A' . $currentRow . ':D' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        $total2 = 0;
        foreach ($tab2 as $index => $item) {
            $total2 += $item['total'];
            $sheet->setCellValue('A' . $currentRow, $index + 1);
            $sheet->setCellValue('B' . $currentRow, $item['periode']);
            $sheet->setCellValue('C' . $currentRow, $item['jumlah_spj']);
            $sheet->setCellValue('D' . $currentRow, $item['total']);
            $currentRow++;
        }
        $sheet->setCellValue('C' . $currentRow, 'TOTAL:');
        $sheet->setCellValue('D' . $currentRow, $total2);
        $sheet->getStyle('C' . $currentRow . ':D' . $currentRow)->getFont()->setBold(true);
        $currentRow += 2;

        // Tab 3: Statistik
        $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
        $sheet->setCellValue('A' . $currentRow, '3. Statistik Pengeluaran');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        $sheet->setCellValue('A' . $currentRow, 'No');
        $sheet->setCellValue('B' . $currentRow, 'Kategori');
        $sheet->setCellValue('C' . $currentRow, 'Total Pengeluaran');
        $sheet->setCellValue('D' . $currentRow, 'Persentase');
        $sheet->setCellValue('E' . $currentRow, 'Gap');
        $sheet->getStyle('A' . $currentRow . ':E' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        // Tab 4: Rekap Per Jenis Dinas
        $sheet->mergeCells('A' . $currentRow . ':D' . $currentRow);
        $sheet->setCellValue('A' . $currentRow, '4. Rekap Per Jenis Dinas');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        $sheet->setCellValue('A' . $currentRow, 'No');
        $sheet->setCellValue('B' . $currentRow, 'Jenis Dinas');
        $sheet->setCellValue('C' . $currentRow, 'Jumlah SPJ');
        $sheet->setCellValue('D' . $currentRow, 'Total Pengeluaran');
        $sheet->getStyle('A' . $currentRow . ':D' . $currentRow)->getFont()->setBold(true);
        $currentRow++;

        $totalJenis = 0;
        foreach ($tabJenis as $index => $item) {
            $totalJenis += $item['total'];
            $sheet->setCellValue('A' . $currentRow, $index + 1);
            $sheet->setCellValue('B' . $currentRow, $item['jenis_dinas']);
            $sheet->setCellValue('C' . $currentRow, $item['jumlah_spj']);
            $sheet->setCellValue('D' . $currentRow, $item['total']);
            $currentRow++;
        }
        $sheet->setCellValue('C' . $currentRow, 'TOTAL:');
        $sheet->setCellValue('D' . $currentRow, $totalJenis);
        $sheet->getStyle('C' . $currentRow . ':D' . $currentRow)->getFont()->setBold(true);
        $currentRow += 2;

        $total3 = 0;
        foreach ($tab3 as $index => $item) {
            $total3 += $item['total'];
            $sheet->setCellValue('A' . $currentRow, $index + 1);
            $sheet->setCellValue('B' . $currentRow, $item['kategori']);
            $sheet->setCellValue('C' . $currentRow, $item['total']);
            $sheet->setCellValue('D' . $currentRow, $item['persentase'] . '%');
            $sheet->setCellValue('E' . $currentRow, $item['gap'] . '%');
            $currentRow++;
        }
        $sheet->setCellValue('B' . $currentRow, 'TOTAL:');
        $sheet->setCellValue('C' . $currentRow, $total3);
        $sheet->setCellValue('D' . $currentRow, '100%');
        $sheet->getStyle('B' . $currentRow . ':D' . $currentRow)->getFont()->setBold(true);

        // Auto-size columns
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Rekap_SPJ_' . now()->format('Y-m-d') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function exportPdf(Request $request)
    {
        $response = $this->getRekapData($request);
        $data = json_decode($response->getContent(), true);

        $tab1 = $data['tab1'];
        $tab2 = $data['tab2'];
        $tab3 = $data['tab3'];
        $grand_total = $data['grand_total'];

        $pdf = Pdf::loadView('HR.RekapSPJ.export_pdf', compact('tab1', 'tab2', 'tab3', 'grand_total'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Rekap_SPJ_' . now()->format('Y-m-d') . '.pdf');
    }
}
