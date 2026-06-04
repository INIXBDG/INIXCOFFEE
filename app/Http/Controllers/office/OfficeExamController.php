<?php

namespace App\Http\Controllers\Office;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use App\Models\RKM;
use App\Models\Karyawan;
use App\Models\Perusahaan;
use App\Models\Eksam;
use App\Models\BundlingExam;
use App\Http\Resources\PostResource;

class OfficeExamController extends Controller
{
    public function indexOffice()
    {
        return view('office.exam.index');
    }

    public function showExamMonth($year, $month)
    {
        $startDate = CarbonImmutable::create($year, $month, 1);
        $endDate = CarbonImmutable::create($year, $month, 1)->endOfMonth();

        $monthRanges = [];
        $date = $startDate;

        while ($date->month <= $endDate->month && $date->year <= $endDate->year) {
            $startOfMonth = $date->startOfMonth();
            $endOfMonth = $date->addMonth()->endOfMonth();

            $weekRanges = [];
            $startOfWeek = $startOfMonth->startOfWeek();

            while ($startOfWeek->lte($endOfMonth)) {
                $endOfWeek = $startOfWeek->copy()->endOfWeek();
                $start = $startOfWeek->format('Y-m-d');
                $end = $endOfWeek->format('Y-m-d');
                $startOfWeek = $startOfWeek->addWeek();

                $rows = RKM::with(['materi', 'peluang', 'rekomendasilanjutan', 'eksam'])
                    ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
                    ->whereBetween('r_k_m_s.tanggal_awal', [$start, $end])
                    ->where('r_k_m_s.exam', '1')
                    ->whereDoesntHave('peluang', function ($query) {
                        $query->where('tentatif', 1);
                    })
                    ->where(function ($query) {
                        $query
                            ->whereHas('exam.approvalexam', function ($q) {
                                $q->where('technical_support', 1);
                            })
                            ->orWhereDoesntHave('exam.approvalexam');
                    })
                    ->select(DB::raw('GROUP_CONCAT(r_k_m_s.id SEPARATOR ", ") AS id'), DB::raw('GROUP_CONCAT(r_k_m_s.id SEPARATOR ", ") AS id_all'), DB::raw('GROUP_CONCAT(r_k_m_s.registrasi_form SEPARATOR ", ") AS registrasi_form'), 'r_k_m_s.materi_key', 'r_k_m_s.ruang', 'r_k_m_s.metode_kelas', 'r_k_m_s.event', DB::raw('GROUP_CONCAT(r_k_m_s.exam SEPARATOR ", ") AS exam'), DB::raw('GROUP_CONCAT(r_k_m_s.makanan SEPARATOR ", ") AS makanan'), DB::raw('GROUP_CONCAT(r_k_m_s.instruktur_key SEPARATOR ", ") AS instruktur_all'), DB::raw('GROUP_CONCAT(r_k_m_s.perusahaan_key SEPARATOR ", ") AS perusahaan_all'), DB::raw('GROUP_CONCAT(r_k_m_s.sales_key SEPARATOR ", ") AS sales_all'), DB::raw('CASE WHEN SUM(r_k_m_s.status = 0) > 0 THEN 0 ELSE MIN(r_k_m_s.status) END AS status_all'), DB::raw('SUM(r_k_m_s.pax) AS total_pax'), 'r_k_m_s.tanggal_awal', DB::raw('MAX(r_k_m_s.tanggal_akhir) AS tanggal_akhir'))
                    ->groupBy('r_k_m_s.materi_key', 'r_k_m_s.ruang', 'r_k_m_s.metode_kelas', 'r_k_m_s.event', 'r_k_m_s.tanggal_awal')
                    ->orderBy('status_all', 'asc')
                    ->orderBy('r_k_m_s.tanggal_awal', 'asc')
                    ->get();

                foreach ($rows as $row) {
                    $sales_ids = array_filter(explode(', ', $row->sales_all ?? ''));
                    $perusahaan_ids = array_filter(explode(', ', $row->perusahaan_all ?? ''));

                    $row->sales = Karyawan::whereIn('kode_karyawan', $sales_ids)->get();
                    $row->perusahaan = Perusahaan::whereIn('id', $perusahaan_ids)->get();

                    if ($row->instruktur_all) {
                        $instruktur_ids = array_filter(explode(', ', $row->instruktur_all));
                        $row->instruktur = Karyawan::whereIn('kode_karyawan', $instruktur_ids)->get();
                    }

                    $rkmIds = array_filter(explode(', ', $row->id ?? ''));

                    $bundlingData = BundlingExam::whereIn('id_rkm', $rkmIds)->first();
                    $bundlingVal = $bundlingData ? $bundlingData->bundling : null;

                    if ($bundlingVal === null || $bundlingVal == 0) {
                        $row->bundling_status = 0;
                    } elseif ($bundlingVal == 1) {
                        $row->bundling_status = 1;
                    } elseif ($bundlingVal == 2) {
                        $row->bundling_status = 2;
                    } else {
                        $row->bundling_status = 0;
                    }

                    $eksamData = Eksam::whereIn('id_rkm', $rkmIds)->first();
                    if ($eksamData) {
                        if (!empty($eksamData->rekomendasi) || !empty($eksamData->status_rekomendasi) || !empty($eksamData->tanggal_rekomendasi)) {
                            $row->exam_status = 'sudah_rekomendasi';
                        } else {
                            $row->exam_status = 'belum_rekomendasi';
                        }
                    } else {
                        $row->exam_status = 'belum_pengajuan';
                    }
                }

                $weekRanges[] = ['start' => $start, 'end' => $end, 'data' => $rows];
            }

            $monthRanges[] = [
                'month' => $startOfMonth->translatedFormat('F-Y'),
                'weeksData' => $weekRanges,
            ];

            $date = $date->addMonth();
        }

        return new PostResource(true, 'List Kelas dengan Exam', $monthRanges);
    }

    public function updateBundling(Request $request)
    {
        $ids = explode(', ', $request->input('id'));
        $status = $request->input('status');

        $dataRkm = RKM::with('eksam')->whereIn('id', $ids)->get();

        foreach ($dataRkm as $rkm) {
            $updateData = ['bundling' => $status];

            if ($rkm->eksam) {
                $updateData['id_exam'] = $rkm->eksam->id;
            }

            BundlingExam::updateOrCreate(['id_rkm' => $rkm->id], $updateData);
        }

        return response()->json(['success' => true]);
    }

    public function showDetailExam($id)
    {
        $ids = explode(', ', $id);
        $rkms = RKM::with(['materi', 'eksam'])->whereIn('id', $ids)->get();
        
        $formatDate = function($value) {
            if (empty($value)) return '-';
            
            if ($value instanceof \Carbon\Carbon) {
                return $value->format('d M Y');
            }
            
            if (is_numeric($value) && $value > 1000000000) {
                return \Carbon\Carbon::createFromTimestamp($value)->format('d M Y');
            }
            
            try {
                return \Carbon\Carbon::parse($value)->format('d M Y');
            } catch (\Exception $e) {
                return '-';
            }
        };
        
        $details = [];
        foreach ($rkms as $rkm) {
            $perusahaan_ids = array_filter(explode(', ', $rkm->perusahaan_key ?? ''));
            $perusahaan = Perusahaan::whereIn('id', $perusahaan_ids)->pluck('nama_perusahaan')->implode(', ') ?: '-';
            
            $sales_ids = array_filter(explode(', ', $rkm->sales_key ?? ''));
            $sales = Karyawan::whereIn('kode_karyawan', $sales_ids)->pluck('nama_lengkap')->implode(', ') ?: '-';
            
            $instruktur_ids = array_filter(explode(', ', $rkm->instruktur_key ?? ''));
            $instruktur = Karyawan::whereIn('kode_karyawan', $instruktur_ids)->pluck('nama_lengkap')->implode(', ') ?: '-';

            $instruktur2_ids = array_filter(explode(', ', $rkm->instruktur_key2 ?? ''));
            $instruktur2 = Karyawan::whereIn('kode_karyawan', $instruktur2_ids)->pluck('nama_lengkap')->implode(', ') ?: '-';

            $asisten_ids = array_filter(explode(', ', $rkm->asisten_key ?? ''));
            $asisten = Karyawan::whereIn('kode_karyawan', $asisten_ids)->pluck('nama_lengkap')->implode(', ') ?: '-';

            $tanggalTraining = '-';
            if ($rkm->tanggal_awal && $rkm->tanggal_akhir) {
                $tglAwal = $formatDate($rkm->tanggal_awal);
                $tglAkhir = $formatDate($rkm->tanggal_akhir);
                $tanggalTraining = ($rkm->tanggal_awal == $rkm->tanggal_akhir) ? $tglAwal : $tglAwal . ' s/d ' . $tglAkhir;
            }

            $details[] = [
                'rkm' => [
                    'materi' => $rkm->materi ? $rkm->materi->nama_materi : '-',
                    'perusahaan' => $perusahaan,
                    'sales' => $sales,
                    'harga_jual' => $rkm->harga_jual ? 'Rp ' . number_format($rkm->harga_jual, 0, ',', '.') : '-',
                    'pax' => $rkm->pax ?? '-',
                    'isi_pax' => $rkm->isi_pax ?? '-',
                    'tanggal' => $tanggalTraining,
                    'metode_kelas' => $rkm->metode_kelas ?? '-',
                    'event' => $rkm->event ?? '-',
                    'ruang' => $rkm->ruang ?? '-',
                    'instruktur' => $instruktur,
                    'instruktur2' => $instruktur2,
                    'asisten' => $asisten,
                    'status' => $rkm->status == 0 ? 'Tidak' : ($rkm->status == 1 ? 'Ya' : 'Tidak'),
                    'exam' => $rkm->exam == 1 ? 'Ya' : 'Tidak',
                    'authorize' => $rkm->authorize ?? '-',
                    'registrasi_form' => $rkm->registrasi_form ?? '-',
                    'quartal' => $rkm->quartal ?? '-',
                    'bulan' => $rkm->bulan ?? '-',
                    'tahun' => $rkm->tahun ?? '-',
                    'makanan' => $rkm->makanan ?? '-',
                    'pdf_peserta' => $rkm->pdf_peserta,
                ],
                'eksam' => $rkm->eksam ? [
                    'invoice' => $rkm->eksam->invoice ?? '-',
                    'file_invoice' => $rkm->eksam->file_invoice,
                    'tanggal_pengajuan' => $formatDate($rkm->eksam->tanggal_pengajuan),
                    'tanggal_mulai' => $formatDate($rkm->eksam->tanggal_mulai),
                    'tanggal_selesai' => $formatDate($rkm->eksam->tanggal_selesai),
                    'materi' => $rkm->eksam->materi ?? '-',
                    'perusahaan' => $rkm->eksam->perusahaan ?? '-',
                    'mata_uang' => $rkm->eksam->mata_uang ?? '-',
                    'harga' => $rkm->eksam->harga ? number_format($rkm->eksam->harga, 0, ',', '.') : '-',
                    'biaya_admin' => $rkm->eksam->biaya_admin ? number_format($rkm->eksam->biaya_admin, 0, ',', '.') : '-',
                    'harga_rupiah' => $rkm->eksam->harga_rupiah ? 'Rp ' . number_format($rkm->eksam->harga_rupiah, 0, ',', '.') : '-',
                    'kurs' => $rkm->eksam->kurs ?? '-',
                    'kurs_dollar' => $rkm->eksam->kurs_dollar ?? '-',
                    'pax' => $rkm->eksam->pax ?? '-',
                    'total' => $rkm->eksam->total ? number_format($rkm->eksam->total, 0, ',', '.') : '-',
                    'kode_exam' => $rkm->eksam->kode_exam ?? '-',
                    'total_pax' => $rkm->eksam->total_pax ?? '-',
                    'keterangan' => $rkm->eksam->keterangan ?? '-',
                    'status' => $rkm->eksam->status ?? '-',
                    'kode_karyawan' => $rkm->eksam->kode_karyawan ?? '-',
                ] : null
            ];
        }
        
        return response()->json(['success' => true, 'data' => $details]);
    }
}
