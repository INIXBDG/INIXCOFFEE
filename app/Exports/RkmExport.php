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

    /**
     * Sisipkan baris kosong sebagai pemisah tiap pergantian minggu.
     */
    protected function insertWeekSeparators($rows)
    {
        $newRows = collect();
        $previousWeek = null;

        foreach ($rows as $row) {
            $week = Carbon::parse($row->tanggal_awal)->format('W');

            if ($previousWeek !== null && $week !== $previousWeek) {
                // Baris pemisah
                $separator = new \stdClass();
                $separator->is_separator = true;
                $newRows->push($separator);
            }

            $newRows->push($row);
            $previousWeek = $week;
        }

        return $newRows;
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
            ->orderBy('tanggal_awal', 'asc') // Pastikan urut tanggal
            ->get();

        // Set relasi many-to-many untuk instruktur, perusahaan, sales
        foreach ($rows as $index => $row) {
            $sales_ids = preg_split('/\s*,\s*/', $row->sales_all ?? '', -1, PREG_SPLIT_NO_EMPTY);
            $perusahaan_ids = preg_split('/\s*,\s*/', $row->perusahaan_all ?? '', -1, PREG_SPLIT_NO_EMPTY);
            $instruktur_ids = preg_split('/\s*,\s*/', $row->instruktur_all ?? '', -1, PREG_SPLIT_NO_EMPTY);

            $row->sales = !empty($sales_ids) ? Karyawan::whereIn('kode_karyawan', $sales_ids)->get() : collect();
            $row->perusahaan = !empty($perusahaan_ids) ? Perusahaan::whereIn('id', $perusahaan_ids)->get() : collect();
            $row->instruktur = !empty($instruktur_ids) ? Karyawan::whereIn('kode_karyawan', $instruktur_ids)->get() : collect();
        }

        // Sisipkan baris kosong pemisah per minggu
        $rows = $this->insertWeekSeparators($rows);

        // Reset $rowsWithStatus dengan memperhatikan baris pemisah
        $this->rowsWithStatus = [];
        $rowIndexExcel = 2; // header di baris 1

        foreach ($rows as $row) {
            if (isset($row->is_separator) && $row->is_separator) {
                $rowIndexExcel++; // baris pemisah, tidak ada status
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
        $lastColumn = 'J'; // sampai kolom J

        foreach ($this->rowsWithStatus as $rowIndex => $status) {
            if ($status == 0) {
                // merah muda
                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('C5172E'); // lebih terang

                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFont()->getColor()->setARGB('FFFFFFFF');
            } elseif ($status == 1) {
                // hijau muda
                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('578FCA');

                $sheet->getStyle("A{$rowIndex}:{$lastColumn}{$rowIndex}")
                    ->getFont()->getColor()->setARGB('FFFFFFFF');
            } else {
                // abu gelap + teks putih
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
