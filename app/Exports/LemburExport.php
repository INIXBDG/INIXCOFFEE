<?php
namespace App\Exports;

use App\Models\Lembur;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\Log;

class LemburExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $month;
    protected $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        // Fetch data for the specified month and year
        $data = Lembur::with('hitunglembur')
            ->whereMonth('tanggal_lembur', $this->month)
            ->whereYear('tanggal_lembur', $this->year)
            ->get();

        // Group data by id_karyawan
        $groupedData = $data->groupBy('id_karyawan');
        // Prepare the data for export
        $exportData = [];
        foreach ($groupedData as $idKaryawan => $items) {
            $totalJamLembur = 0;
            $totalNilaiLembur = 0;
            $exportData[] = [
                '----',
                '----',
                '----',
                '----',
                '----',
                '----',
                '----',
                '----',
                '----',
                '----',
            ];
            foreach ($items as $item) {
                $jamLembur = $this->calculateJamLembur($item->jam_mulai, $item->jam_selesai);
                $nilaiLembur = $item->hitunglembur->nilai_lembur ?? 0;
                $totalNilai = $this->calculateTotalNilai($nilaiLembur, $jamLembur);
                if($item->hitunglembur == null || $item->hitunglembur->approval_gm == null){
                    $approve = 'Belum';
                }else if($item->hitunglembur->approval_gm == '2'){
                    $approve = 'Tidak';
                }else if($item->hitunglembur->approval_gm == '1'){
                    $approve = 'Ya';
                }
                $exportData[] = [
                    'Karyawan' => $item->karyawan->nama_lengkap, // Assuming you have a name field
                    'Tanggal' => $item->tanggal_lembur,
                    'Hari Biasa dan Libur' => 'Hari '. $item->waktu_lembur,
                    'Keperluan' => $item->uraian_tugas,
                    'Jam Mulai' => $item->jam_mulai,
                    'Jam Selesai' => $item->jam_selesai,
                    'Jumlah Jam Lembur' => $jamLembur,
                    'Nilai Lembur per Jam' => $nilaiLembur,
                    'Total Nilai Lembur' => $totalNilai,
                    'Approval GM' => $approve
                ];

                // Accumulate totals
                $totalJamLembur += (int) filter_var($jamLembur, FILTER_SANITIZE_NUMBER_INT);
                $totalNilaiLembur += $totalNilai;
            }

            // Add totals for each karyawan
            $exportData[] = [
                'Karyawan' => $item->karyawan->nama_lengkap,
                'Tanggal' => '',
                'Hari Biasa dan Libur' => '',
                'Keperluan' => 'Total',
                'Jam Mulai' => '',
                'Jam Selesai' => '',
                'Jumlah Jam Lembur' => $totalJamLembur . ' Jam',
                'Nilai Lembur per Jam' => '',
                'Total Nilai Lembur' => $totalNilaiLembur,
                'Approval GM' => ''
            ];
        }

        return collect($exportData);
    }

    public function headings(): array
    {
        return [
            'Nama Karyawan',
            'Tanggal',
            'Hari Biasa dan Libur',
            'Keperluan',
            'Jam Mulai',
            'Jam Selesai',
            'Jumlah Jam Lembur',
            'Nilai Lembur per Jam',
            'Total Nilai Lembur',
            'Approval GM',
        ];
    }

    private function calculateJamLembur($jamMulai, $jamSelesai)
    {
        try {
            // Ensure the input is in the correct format
            $start = \Carbon\Carbon::createFromFormat('H:i', $jamMulai);
            $end = \Carbon\Carbon::createFromFormat('H:i', $jamSelesai);

            // Check if the start and end times are valid
            if (!$start || !$end) {
                throw new \Exception("Invalid time format for jam_mulai or jam_selesai.");
            }

            // Calculate the difference in hours
            return $end->diffInHours($start) . ' Jam';
        } catch (\Exception $e) {
            // Log the error and return a default value
            Log::error('Error calculating jam lembur: ' . $e->getMessage());
            return '0 Jam'; // Return a default value in case of error
        }
    }


    private function calculateTotalNilai($nilaiLembur, $jumlahJam)
    {
        $jam = (int) filter_var($jumlahJam, FILTER_SANITIZE_NUMBER_INT);
        return $nilaiLembur * $jam;
    }
}
