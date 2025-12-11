<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\Perusahaan;
use Illuminate\Http\Request;
use Carbon\CarbonImmutable;
use App\Http\Resources\PostResource;
use App\Models\Materi;
use App\Models\RekomendasiLanjutan;
use App\Models\RKM;

class RekomendasiLanjutanController extends Controller
{
    public function index()
    {
        $dataMateri = Materi::get();
        return view('rekomendasilanjutan.index', compact('dataMateri'));
    }

    public function showMonth($year, $month)
    {
        if (!is_numeric($year) || !is_numeric($month)) {
            return response()->json(['error' => 'Year and month must be numeric'], 400);
        }

        $year = (int)$year;
        $month = (int)$month;

        if ($month < 1 || $month > 12) {
            return response()->json(['error' => 'Month must be between 1 and 12'], 400);
        }

        try {
            $startDate = CarbonImmutable::create($year, $month, 1);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date'], 400);
        }

        $endDate = $startDate->endOfMonth();

        $monthRanges = [];
        $date = $startDate;

        while ($date->month <= $endDate->month && $date->year <= $endDate->year) {
            $startOfMonth = $date->startOfMonth();
            $endOfMonth = $startOfMonth->endOfMonth();

            $weekRanges = [];
            $startOfWeek = $startOfMonth->startOfWeek();

            while ($startOfWeek->lte($endOfMonth)) {
                $endOfWeek = $startOfWeek->copy()->endOfWeek();
                $start = $startOfWeek->format('Y-m-d');
                $end = $endOfWeek->format('Y-m-d');

                $rows = RKM::with(['materi', 'peluang', 'rekomendasilanjutan.materi'])
                    ->whereBetween('tanggal_awal', [$start, $end])
                    ->whereDoesntHave('peluang', function ($query) {
                        $query->where('tentatif', 1);
                    })
                    ->orderBy('status', 'asc')
                    ->orderBy('tanggal_awal', 'asc')
                    ->get();

                foreach ($rows as $row) {
                    $row->status_all = $row->status;
                    $row->total_pax = $row->pax ?? 0;

                    $perusahaanIds = $row->perusahaan_key ? explode(',', str_replace(' ', '', $row->perusahaan_key)) : [];
                    $salesIds = $row->sales_key ? explode(',', str_replace(' ', '', $row->sales_key)) : [];
                    $instrukturIds = $row->instruktur_key ? explode(',', str_replace(' ', '', $row->instruktur_key)) : [];

                    $row->perusahaan = Perusahaan::whereIn('id', $perusahaanIds)->get();
                    $row->sales = karyawan::whereIn('kode_karyawan', $salesIds)->get();
                    $row->instruktur = karyawan::whereIn('kode_karyawan', $instrukturIds)->get();
                }

                $weekRanges[] = ['start' => $start, 'end' => $end, 'data' => $rows];
                $startOfWeek = $startOfWeek->addWeek();
            }

            $monthRanges[] = ['month' => $startOfMonth->translatedFormat('F-Y'), 'weeksData' => $weekRanges];
            $date = $date->addMonth();
        }

        return new PostResource(true, 'List Detail Bulan RKM', $monthRanges);
    }


    public function store(Request $request)
    {
        $request->validate([
            'id_rkm' => 'required',
            'id_materi' => 'required',
        ]);

        if ($request->input('id_rekomendasi')) {
            $rekomendasi = RekomendasiLanjutan::where('id' ,$request->input('id_rekomendasi'))->first();
            $rekomendasi->id_materi = $request->id_materi;
            $saved = $rekomendasi->save();
            $message = 'Rekomendasi berhasil diperbarui.';
        } else {
            $rekomendasi = new RekomendasiLanjutan();
            $rekomendasi->id_rkm = $request->id_rkm;
            $rekomendasi->id_materi = $request->id_materi;
            $saved = $rekomendasi->save();
            $message = 'Rekomendasi berhasil diajukan.';
        }

        if ($saved) {
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal menyimpan rekomendasi.'
        ], 500);
    }
}
