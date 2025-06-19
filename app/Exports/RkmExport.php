<?php

namespace App\Exports;

use App\Models\RKM;
use App\Models\Karyawan;
use App\Models\Perusahaan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class RkmExport implements FromView, ShouldAutoSize, WithStyles
{
    protected $rowsWithStatus = [];
    protected $tahun, $bulan;

    public function __construct($tahun, $bulan)
    {
        $this->tahun = $tahun;
        $this->bulan = $bulan;
    }

    protected function insertWeekSeparators($rows)
    {
        $groupedByWeek = [];

        foreach ($rows as $row) {
            $date = Carbon::parse($row->tanggal_awal);

            // Gunakan kombinasi tahun, bulan, dan minggu ke-n
            $yearMonth = $date->format('Y-m');
            $weekNumberInMonth = intdiv($date->day - 1, 7) + 1;
            $weekKey = $yearMonth . '-W' . $weekNumberInMonth;

            if (!isset($groupedByWeek[$weekKey])) {
                $groupedByWeek[$weekKey] = [];
            }

            $groupedByWeek[$weekKey][] = $row;
        }

        ksort($groupedByWeek); // Urutkan berdasarkan minggu

        $sortedRows = collect();

        foreach ($groupedByWeek as $week => $weekRows) {
            // Tambahkan baris pemisah antar minggu
            if (!$sortedRows->isEmpty()) {
                $separator = new \stdClass();
                $separator->is_separator = true;
                $separator->week_label = $week; // Bisa digunakan di Excel untuk label
                $sortedRows->push($separator);
            }

            // Urutkan status: merah(0), biru(1), lainnya
            usort($weekRows, function ($a, $b) {
                $priority = fn($status) => $status == 0 ? 0 : ($status == 1 ? 1 : 2);
                return $priority($a->status_all) <=> $priority($b->status_all);
            });

            foreach ($weekRows as $row) {
                $sortedRows->push($row);
            }
        }

        return $sortedRows;
    }

    public function map($row): array
    {
        if (property_exists($row, 'is_separator') && $row->is_separator) {
            return ["=== Minggu: {$row->week_label} ==="];
        }

        return [
            $row->materi,
            $row->ruang,
            $row->metode_kelas,
            $row->event,
            $row->instruktur,
            $row->sales,
            $row->perusahaan,
            $row->jumlah_peserta,
            $row->tanggal_awal,
            $row->tanggal_akhir,
        ];
    }

    public function view(): View
    {
        $month = (int) $this->bulan;
        $year = (int) $this->tahun;

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $rows = RKM::with('materi')
            ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
            ->whereBetween('r_k_m_s.tanggal_awal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->select(
                'r_k_m_s.materi_key',
                'r_k_m_s.ruang',
                'r_k_m_s.metode_kelas',
                'r_k_m_s.event',
                \DB::raw('GROUP_CONCAT(r_k_m_s.instruktur_key SEPARATOR ",") AS instruktur_all'),
                \DB::raw('GROUP_CONCAT(r_k_m_s.perusahaan_key SEPARATOR ",") AS perusahaan_all'),
                \DB::raw('GROUP_CONCAT(r_k_m_s.sales_key SEPARATOR ",") AS sales_all'),
                \DB::raw('CASE WHEN SUM(r_k_m_s.status = 0) > 0 THEN 0 ELSE MIN(r_k_m_s.status) END AS status_all'),
                \DB::raw('SUM(r_k_m_s.pax) AS total_pax'),
                \DB::raw('MIN(r_k_m_s.tanggal_awal) AS tanggal_awal'),
                \DB::raw('MAX(r_k_m_s.tanggal_akhir) AS tanggal_akhir')
            )
            ->groupBy(
                'r_k_m_s.materi_key',
                'r_k_m_s.ruang',
                'r_k_m_s.metode_kelas',
                'r_k_m_s.event'
            )
            ->orderBy('tanggal_awal', 'asc')
            ->get();

        // Set relasi many-to-many untuk instruktur, perusahaan, sales
        foreach ($rows as $row) {
            $sales_ids = preg_split('/\s*,\s*/', $row->sales_all ?? '', -1, PREG_SPLIT_NO_EMPTY);
            $perusahaan_ids = preg_split('/\s*,\s*/', $row->perusahaan_all ?? '', -1, PREG_SPLIT_NO_EMPTY);
            $instruktur_ids = preg_split('/\s*,\s*/', $row->instruktur_all ?? '', -1, PREG_SPLIT_NO_EMPTY);

            $row->sales = !empty($sales_ids) ? Karyawan::whereIn('kode_karyawan', $sales_ids)->get() : collect();
            $row->perusahaan = !empty($perusahaan_ids) ? Perusahaan::whereIn('id', $perusahaan_ids)->get() : collect();
            $row->instruktur = !empty($instruktur_ids) ? Karyawan::whereIn('kode_karyawan', $instruktur_ids)->get() : collect();
        }

        // Urutkan berdasarkan status: merah (0), biru (1), lalu hitam
        $rows = $rows->sortBy(function ($item) {
            return match ($item->status_all) {
                0 => 0,   // merah
                1 => 1,   // biru
                default => 2,
            };
        })->values();

        // Sisipkan baris kosong pemisah per minggu
        $rows = $this->insertWeekSeparators($rows);

        // Simpan status untuk pewarnaan Excel
        $this->rowsWithStatus = [];
        $rowIndexExcel = 2; // baris pertama adalah header

        foreach ($rows as $row) {
            if (isset($row->is_separator) && $row->is_separator) {
                $rowIndexExcel++;
                continue;
            }

            $this->rowsWithStatus[$rowIndexExcel] = $row->status_all;
            $rowIndexExcel++;
        }

        return view('exports.rkm', [
            'rows' => $rows,
            'bulan' => $month,
            'tahun' => $year
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        $lastColumn = 'J'; // Ubah jika jumlah kolom berubah

        foreach ($this->rowsWithStatus as $rowIndex => $status) {
            if ($status == 0) {
                // Merah muda
                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C5172E');

                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFont()->getColor()->setARGB('FFFFFFFF');
            } elseif ($status == 1) {
                // Biru muda
                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('578FCA');

                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFont()->getColor()->setARGB('FFFFFFFF');
            } else {
                // Abu gelap
                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FF666666');

                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFont()->getColor()->setARGB('FFFFFFFF');
            }
        }

        return [];
    }
}
