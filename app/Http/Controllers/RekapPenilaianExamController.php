<?php

namespace App\Http\Controllers;

use App\Models\RKM;
use App\Models\PenilaianExam;
use Illuminate\Http\Request;

class RekapPenilaianExamController extends Controller
{
    public function indexRekap()
    {
        return view('rekappenilaianexam.index');
    }

    public function getRekapPenilaian(Request $request)
        {
            $tahun = $request->get('tahun', now()->year);
            $bulan = str_pad($request->get('bulan', now()->month), 2, '0', STR_PAD_LEFT);
            $formatPencarian = $tahun . '-' . $bulan;

            $data = RKM::with(['materi', 'perusahaan', 'penilaianExam', 'dataExam'])
                ->where('exam', '1')
                ->whereHas('dataExam', function($query) use ($formatPencarian) {
                    $query->where('tanggal_mulai', 'like', $formatPencarian . '%')
                        ->orWhere('tanggal_selesai', 'like', $formatPencarian . '%');
                })
                ->whereHas('penilaianExam')
                ->get()
                ->map(function ($item) {
                    $totalNilai = $item->penilaianExam->sum('nilai_emote');
                    $totalResponden = $item->penilaianExam->count();
                    $pax = $item->dataExam->pax;
                    $rataRata = $totalResponden > 0 ? round($totalNilai / $totalResponden, 2) : 0;

                    $detailPenilaian = [
                        'sangat_baik' => $item->penilaianExam->where('nilai_emote', 4)->count(),
                        'baik' => $item->penilaianExam->where('nilai_emote', 3)->count(),
                        'cukup' => $item->penilaianExam->where('nilai_emote', 2)->count(),
                        'buruk' => $item->penilaianExam->where('nilai_emote', 1)->count(),
                        'total_responden' => $totalResponden
                    ];

                    return [
                        'id' => $item->id,
                        'kode_exam' => $item->dataExam->kode_exam ?? '-',
                        'nama_materi' => $item->materi ? $item->materi->nama_materi : '-',
                        // Mengambil data tanggal dari tabel eksams (relasi dataExam)
                        'tanggal_pengajuan' => $item->dataExam->tanggal_pengajuan ?? null,
                        'tanggal_mulai' => $item->dataExam->tanggal_mulai ?? null,
                        'tanggal_selesai' => $item->dataExam->tanggal_selesai ?? null,
                        'nama_perusahaan' => $item->perusahaan ? $item->perusahaan->nama_perusahaan : '-',
                        'pax' => $pax,
                        'rata_rata' => $rataRata,
                        'total_nilai' => $totalNilai,
                        'detail' => $detailPenilaian,
                    ];
                });

            return response()->json([
                'data' => $data
            ]);
        }
}
