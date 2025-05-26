<?php

namespace App\Exports;

use App\Models\RKM;
use App\Models\Karyawan;
use App\Models\Perusahaan;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RkmExport implements FromView, ShouldAutoSize
{
    protected $tahun, $bulan;

    public function __construct($tahun, $bulan)
    {
        $this->tahun = $tahun;
        $this->bulan = $bulan;
    }

    public function view(): View
    {
        $month = (int) $this->bulan;
        $year = (int) $this->tahun;

        $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        $rows = RKM::with('materi')
            ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
            ->whereBetween('r_k_m_s.tanggal_awal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->select(
                'r_k_m_s.materi_key',
                'r_k_m_s.ruang',
                'r_k_m_s.metode_kelas',
                'r_k_m_s.event',
                \DB::raw('GROUP_CONCAT(r_k_m_s.instruktur_key SEPARATOR ", ") AS instruktur_all'),
                \DB::raw('GROUP_CONCAT(r_k_m_s.perusahaan_key SEPARATOR ", ") AS perusahaan_all'),
                \DB::raw('GROUP_CONCAT(r_k_m_s.sales_key SEPARATOR ", ") AS sales_all'),
                \DB::raw('CASE WHEN SUM(r_k_m_s.status = 0) > 0 THEN 0 ELSE MIN(r_k_m_s.status) END AS status_all'),
                \DB::raw('SUM(r_k_m_s.pax) AS total_pax'),
                \DB::raw('MIN(r_k_m_s.tanggal_awal) AS tanggal_awal'),
                \DB::raw('MAX(r_k_m_s.tanggal_akhir) AS tanggal_akhir')
            )
            ->groupBy(
                'r_k_m_s.materi_key',
                'r_k_m_s.ruang',
                'r_k_m_s.metode_kelas',
                'r_k_m_s.event',
                'r_k_m_s.tanggal_awal'
            )
            ->orderBy('status_all', 'asc')
            ->orderBy('r_k_m_s.tanggal_awal', 'asc')
            ->get();

        foreach ($rows as $row) {
            $sales_ids = explode(', ', $row->sales_all ?? '');
            $perusahaan_ids = explode(', ', $row->perusahaan_all ?? '');
            $instruktur_ids = explode(', ', $row->instruktur_all ?? '');

            $row->sales = Karyawan::whereIn('kode_karyawan', $sales_ids)->get();
            $row->perusahaan = Perusahaan::whereIn('id', $perusahaan_ids)->get();
            $row->instruktur = Karyawan::whereIn('kode_karyawan', $instruktur_ids)->get();
        }

        return view('exports.rkm', [
            'rows' => $rows,
            'bulan' => $month,
            'tahun' => $year
        ]);
    }
}
