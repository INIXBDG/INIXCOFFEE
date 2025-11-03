<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\Perusahaan;
use App\Models\rekapMengajarInstruktur;
use App\Models\RKM;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\FeedbackController;
use Illuminate\Support\Facades\Log;

class rekapInstrukturController extends Controller
{
    protected $feedbackController;
    public function __construct(feedbackController $feedbackController)
    {
        $this->feedbackController = $feedbackController; // Menginisialisasi controller
    }

    public function index()
    {
        $month = Carbon::now()->format('m');
        $year = Carbon::now()->format('Y');

        $karyawan = Karyawan::where('divisi', 'Education')
            ->where('status_aktif', '1')
            ->where('kode_karyawan', 'NOT LIKE', '%OL%') // Mengecualikan kode karyawan yang mengandung 'OL'
            ->get();
        // return $karyawan;
        return view('rekapinstruktur.index', compact('month', 'year', 'karyawan'));
    }

    public function destroy($id)
    {
        $data = rekapMengajarInstruktur::findOrFail($id);
        // dd($data);
        $data->delete();

        return redirect()->route('rekapmengajarinstruktur.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
    
    public function getListMengajar($bulan, $tahun)
    {
        // Mengambil id_rkm yang ada dari database
        $existingRKMs = rekapMengajarInstruktur::pluck('id_rkm')->toArray();

        // Menggabungkan array menjadi string, jika ada lebih dari satu id_rkm
        $existingRKMsString = implode(',', $existingRKMs);

        // Memisahkan string menjadi array
        $id_rkm = explode(',', $existingRKMsString);

        $uniqueRKMs = array_unique($id_rkm);
        $id_rkm = array_values($uniqueRKMs);

        // Tentukan awal dan akhir bulan target (string)
        $startOfMonth = Carbon::createFromDate($tahun, $bulan, 1)->startOfDay()->toDateString();
        $endOfMonth = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->endOfDay()->toDateString();

        $data = RKM::with('materi')
            ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
            ->where(function ($q) use ($startOfMonth, $endOfMonth) {
                $q->where('r_k_m_s.tanggal_awal', '<=', $endOfMonth)
                ->where('r_k_m_s.tanggal_akhir', '>=', $startOfMonth);
            })
            ->whereNotIn('r_k_m_s.id', $id_rkm)
            ->select(
                'r_k_m_s.materi_key',
                // kita tidak men-select ruang langsung — kita akan hitung ruang_result di bawah
                'r_k_m_s.event',
                DB::raw('GROUP_CONCAT(DISTINCT r_k_m_s.instruktur_key SEPARATOR ",") AS instruktur_all'),
                DB::raw('GROUP_CONCAT(DISTINCT r_k_m_s.perusahaan_key SEPARATOR ",") AS perusahaan_all'),
                DB::raw('GROUP_CONCAT(DISTINCT r_k_m_s.sales_key SEPARATOR ",") AS sales_all'),
                DB::raw('GROUP_CONCAT(DISTINCT r_k_m_s.id SEPARATOR ",") AS id_all'),
                DB::raw('CASE WHEN SUM(CASE WHEN r_k_m_s.status = 0 THEN 1 ELSE 0 END) > 0 THEN 0 ELSE MIN(r_k_m_s.status) END AS status_all'),
                DB::raw('SUM(r_k_m_s.pax) AS total_pax'),
                DB::raw('MIN(r_k_m_s.tanggal_awal) AS tanggal_awal'),
                DB::raw('MAX(r_k_m_s.tanggal_akhir) AS tanggal_akhir'),
                // Prioritaskan metode_kelas: Offline > Virtual > gabungan distinct
                DB::raw("
                    CASE
                        WHEN SUM(CASE WHEN LOWER(r_k_m_s.metode_kelas) = 'offline' THEN 1 ELSE 0 END) > 0 THEN 'Offline'
                        WHEN SUM(CASE WHEN LOWER(r_k_m_s.metode_kelas) = 'virtual' THEN 1 ELSE 0 END) > 0 THEN 'Virtual'
                        ELSE GROUP_CONCAT(DISTINCT r_k_m_s.metode_kelas SEPARATOR ', ')
                    END AS metode_kelas_all
                "),
                // Pilih ruang: ambil ruang dari baris Offline jika ada, else ruang dari Virtual, else gabungkan distinct
                DB::raw("
                    CASE
                        WHEN SUM(CASE WHEN LOWER(r_k_m_s.metode_kelas) = 'offline' THEN 1 ELSE 0 END) > 0
                            THEN MIN(CASE WHEN LOWER(r_k_m_s.metode_kelas) = 'offline' THEN r_k_m_s.ruang ELSE NULL END)
                        WHEN SUM(CASE WHEN LOWER(r_k_m_s.metode_kelas) = 'virtual' THEN 1 ELSE 0 END) > 0
                            THEN MIN(CASE WHEN LOWER(r_k_m_s.metode_kelas) = 'virtual' THEN r_k_m_s.ruang ELSE NULL END)
                        ELSE GROUP_CONCAT(DISTINCT r_k_m_s.ruang SEPARATOR ', ')
                    END AS ruang_prefered
                ")
            )
            ->groupBy(
                'r_k_m_s.materi_key',
                'r_k_m_s.event'
            )
            ->orderBy('status_all', 'asc')
            ->orderBy('tanggal_awal', 'asc')
            ->orderBy('tanggal_akhir', 'asc')
            ->get();

        // return $data;
        // Ambil relasi instruktur/sales/perusahaan seperti sebelumnya
        foreach ($data as $row) {
            $sales_ids = $row->sales_all ? explode(', ', $row->sales_all) : [];
            $perusahaan_ids = $row->perusahaan_all ? explode(', ', $row->perusahaan_all) : [];

            if ($row->instruktur_all == null) {
                $row->sales = Karyawan::whereIn('kode_karyawan', $sales_ids)->get();
                $row->perusahaan = Perusahaan::whereIn('id', $perusahaan_ids)->get();
            } else {
                $instruktur_ids = explode(', ', $row->instruktur_all);
                $row->instruktur = Karyawan::whereIn('kode_karyawan', $instruktur_ids)->first();
                $row->sales = Karyawan::whereIn('kode_karyawan', $sales_ids)->get();
                $row->perusahaan = Perusahaan::whereIn('id', $perusahaan_ids)->get();
            }
        }

        // Filter dan bentuk response berdasarkan mayoritas hari di bulan target
        $result = [];
        foreach ($data as $dataRow) {
            $awal = Carbon::parse($dataRow->tanggal_awal);
            $akhir = Carbon::parse($dataRow->tanggal_akhir);

            // Total durasi RKM (inklusif)
            $totalDays = $akhir->diffInDays($awal) + 1;

            // Hitung overlap dengan bulan target
            $monthStart = Carbon::createFromDate($tahun, $bulan, 1)->startOfDay();
            $monthEnd = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->endOfDay();

            $overlapStart = $awal->greaterThan($monthStart) ? $awal->copy() : $monthStart->copy();
            $overlapEnd = $akhir->lessThan($monthEnd) ? $akhir->copy() : $monthEnd->copy();

            $daysInTarget = 0;
            if ($overlapStart->lte($overlapEnd)) {
                $daysInTarget = $overlapEnd->diffInDays($overlapStart) + 1;
            }

            $daysOther = $totalDays - $daysInTarget;

            // Aturan penempatan:
            // - jika jumlah hari di bulan target > jumlah hari di bulan lain -> masukkan
            // - jika sama (tie) -> tie-break: masukkan ke bulan tanggal_awal (bisa diubah sesuai preferensi)
            $isInThisMonth = false;
            if ($daysInTarget > $daysOther) {
                $isInThisMonth = true;
            } elseif ($daysInTarget == $daysOther) {
                // tie-break: masukkan jika tanggal_awal berada di bulan target
                if ($awal->month == $bulan && $awal->year == $tahun) {
                    $isInThisMonth = true;
                }
            }

            if ($isInThisMonth) {
                $id_rkms = explode(',', $dataRow->id_all)[0];
                $rkm = RKM::with('materi')->findOrFail($id_rkms);

                $result[] = [
                    'id' => $dataRow->id_all,
                    'nama_materi' => $rkm->materi->nama_materi,
                    'instruktur' => $dataRow->instruktur ?? '-',
                    'tanggal_awal' => $dataRow->tanggal_awal,
                    'tanggal_akhir' => $dataRow->tanggal_akhir,
                    'durasi_rkm' => $totalDays,
                    'durasi_materi' => $dataRow->materi->durasi,
                    'rkm' => $rkm,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'List data',
            'data' => collect($result),
        ]);
    }	

    public function store(Request $request)
    {
        // dd($request->all());
        $rkm = RKM::findOrFail($request->id_rkm);
        $materi_key = $rkm->materi_key;
        $tanggal_akhir = $rkm->tanggal_akhir; // Misalnya, formatnya 'YYYY-MM-DD'
        $date = Carbon::parse($tanggal_akhir);
        $tahun = $date->year;
        $bulan = $date->month;
        $tanggal_awal = $rkm->tanggal_awal;
        $datarkm = RKM::with(['sales', 'materi', 'instruktur', 'perusahaan', 'instruktur2', 'asisten'])
            ->where('materi_key', $materi_key)
            ->whereBetween('tanggal_awal', [$tanggal_awal, $tanggal_akhir])
            ->get();
        
            $totalPax = $datarkm->sum('pax');
            $instruktur = $request->instruktur;
            foreach ($instruktur as $inst) {
            
                $status = "Belum Dihitung";
                if ($inst['instruktur'] == null) {
                    continue;
                } else {
                    $data = new rekapMengajarInstruktur();
                    $data->id_rkm = $datarkm->pluck('id')->implode(',');
                    $data->pax = $totalPax;
                    $data->bulan = $bulan;
                    $data->tahun = $tahun;
                    $data->id_instruktur = $inst['instruktur']; // Akses dengan []
                    $data->durasi = $inst['durasi']; // Akses dengan []
                    $data->feedback = $inst['feedback']; // Akses dengan []
                    $data->level = $inst['level']; // Akses dengan []
                    $data->tanggal_awal = $inst['tanggal_awal']; // Akses dengan []
                    $data->tanggal_akhir = $inst['tanggal_akhir']; // Akses dengan []
                    $data->keterangan = $inst['keterangan']; // Akses dengan []
                    $data->status = $status;
                    $data->save();
                }
            }
        return redirect()->route('rekapmengajarinstruktur.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }


    public function cekLevel($id)
    {
        $data = rekapMengajarInstruktur::with('rkm', 'rkm.materi', 'instruktur')
        ->whereHas('rkm', function($query) use ($id) {
            $query->where('materi_key', $id);
        })
        ->get();

        return view('rekapinstruktur.ceklevel', compact('data'));
    }

public function getMengajarInstruktur($id, $month, $year) 
	{
		// Mengambil data rekap mengajar instruktur
		if ($id == 'OL') {
			$data = rekapMengajarInstruktur::with('instruktur')->where('bulan', $month)
				->where('tahun', $year)
				->where('id_instruktur', 'LIKE', '%OL%')
				->get();
		} else {
			$data = rekapMengajarInstruktur::with('instruktur')->where('bulan', $month)
				->where('tahun', $year)
				->where('id_instruktur', $id)
				->get();
		}

		// Ambil hanya RKM pertama jika lebih dari satu
		$data->transform(function ($item) {
			$item->id_rkm = explode(',', $item->id_rkm)[0]; 
			return $item;
		});

		// Ambil data RKM dan relasi feedback
		$rkmData = $data->map(function ($item) {
			return RKM::with('materi', 'instruktur', 'instruktur2', 'asisten', 'nilaifeedback')
				->where('id', $item->id_rkm)
				->first();
		});

		// Inisialisasi total dan counter untuk rata-rata global
		$totalFeedbackPerRKM = 0;
		$totalRKM = 0;

		$result = $data->map(function ($item, $index) use ($rkmData, &$totalFeedbackPerRKM, &$totalRKM) {
			$rkm = $rkmData[$index];

			// Hitung rata-rata I1-I8 dari semua feedback per RKM
			$avgFeedbackRKM = 0;
			$jumlahFeedback = 0;

			if ($rkm && $rkm->nilaifeedback) {
				$feedbackList = $rkm->nilaifeedback;

				$feedbackList->each(function ($feedback) use (&$avgFeedbackRKM, &$jumlahFeedback) {
					$nilai = [
						$feedback->I1,
						$feedback->I2,
						$feedback->I3,
						$feedback->I4,
						$feedback->I5,
						$feedback->I6,
						$feedback->I7,
						$feedback->I8
					];

					$total = array_sum($nilai);
					$avg = $total / 8;

					$avgFeedbackRKM += $avg;
					$jumlahFeedback++;
				});

				if ($jumlahFeedback > 0) {
					$avgFeedbackRKM = $avgFeedbackRKM / $jumlahFeedback;
				}
			}

			// Tambah ke total semua RKM
			$totalFeedbackPerRKM += $avgFeedbackRKM;
			$totalRKM++;

			return [
				'id' => $item->id,
				'id_rkm' => $item->id_rkm,
				'id_instruktur' => $item->id_instruktur,
				'level' => $item->level,
				'durasi' => $item->durasi,
				'keterangan' => $item->keterangan,
				'tanggal_awal' => $item->tanggal_awal ?? null,
				'tanggal_akhir' => $item->tanggal_akhir ?? null,
				'nama_materi' => $rkm->materi->nama_materi ?? null,
				'nama_lengkap' => $item->instruktur->nama_lengkap ?? null,
				'metode_kelas' => $rkm->metode_kelas ?? null,
				'pax' => $item->pax,
				'feedback' => round($avgFeedbackRKM, 1),
				'rkm' => $rkm,
			];
		});

		// Hitung rata-rata keseluruhan feedback instruktur (semua RKM)
		$averageAllFeedback = $totalRKM > 0 ? round($totalFeedbackPerRKM / $totalRKM, 1) : 0;

		return response()->json([
			'success' => true,
			'message' => 'List data',
			'average_feedback_instruktur' => $averageAllFeedback,
			'data' => $result,
		]);
	}



    public function editMengajarInstruktur($id)
    {
        // Mengambil data rekap mengajar instruktur berdasarkan ID
        $data = rekapMengajarInstruktur::with('rkm', 'rkm.materi', 'instruktur')
            ->findOrFail($id);

        // Mengambil id_rkm pertama sebelum koma
        $id_rkm = explode(',', $data->id_rkm)[0];

        // Mengambil RKM berdasarkan id_rkm yang pertama
        $rkm = RKM::with('materi', 'instruktur', 'instruktur2', 'asisten', 'nilaifeedback')
            ->where('id', $id_rkm)
            ->first();

        // Mendapatkan bulan, tahun, ID instruktur, dan tanggal dari data yang diambil
        $month = $data->bulan;
        $year = $data->tahun;
        $id_instruktur = $data->id_instruktur;
        $tanggal_awal = $data->tanggal_awal;
        $tanggal_akhir = $data->tanggal_akhir;

        // Mengambil data rekap mengajar instruktur berdasarkan bulan, tahun, dan kriteria lainnya
        $datas = rekapMengajarInstruktur::with('rkm', 'rkm.materi', 'instruktur', 'rkm.nilaifeedback')
            ->where('bulan', $month)
            ->where('tahun', $year)
            ->where('id_instruktur', $id_instruktur)
            ->where('tanggal_awal', $tanggal_awal)
            ->where('tanggal_akhir', $tanggal_akhir)
            ->get();

        // Mengelompokkan data berdasarkan kriteria tertentu
        $groupedData = $datas->groupBy(function ($item) {
            return $item->id_instruktur . '|' . $item->level . '|' . $item->durasi . '|' . $item->tanggal_awal . '|' . $item->tanggal_akhir;
        });

        // Mengolah data yang telah dikelompokkan
        $result = $groupedData->map(function ($group) use ($rkm) {
            $firstItem = $group->first(); // Ambil item pertama untuk mendapatkan informasi lainnya
            $totalPax = $group->sum(function ($item) {
                return (int)$item->pax; // Jumlahkan pax
            });

            return [
                'id' => $firstItem->id,
                'id_instruktur' => $firstItem->id_instruktur,
                'id_rkm' => $firstItem->id_rkm,
                'level' => $firstItem->level,
                'durasi' => $firstItem->durasi,
                'keterangan' => $firstItem->keterangan,
                'tanggal_awal' => $firstItem->tanggal_awal, // Mengambil tanggal_awal dari rkm
                'tanggal_akhir' => $firstItem->tanggal_akhir, // Mengambil tanggal_akhir dari rkm
                'nama_materi' => $rkm->materi->nama_materi ?? null, // Mengambil nama_materi dari rkm
                'nama_lengkap' => $rkm->instruktur->nama_lengkap ?? null, // Mengambil nama_lengkap dari rkm
                'metode_kelas' => $rkm->metode_kelas ?? null, // Mengambil metode_kelas dari rkm
                'feedback' => $firstItem->feedback, // Anda bisa menghitung rata-rata feedback jika perlu
                'pax' => $totalPax,
                'rkm' => $rkm,
                'instruktur' => $firstItem->instruktur,
            ];
        })->values(); // Mengambil nilai dari koleksi

        // Mengambil hasil pertama dari koleksi
        $singleResult = $result->first();

        // Mengembalikan response JSON
        return response()->json([
            'success' => true,
            'message' => 'List data',
            'data' => $singleResult,
        ]);
    }

    public function update($id, Request $request)
    {
        // return $request->all();
            $data = rekapMengajarInstruktur::with('rkm', 'rkm.materi', 'instruktur')
                    ->findOrFail($id);
            $posts = rekapMengajarInstruktur::where('durasi', $data->durasi)
                    ->where('level', $data->level)
                    ->where('tanggal_awal', $data->tanggal_awal)
                    ->where('tanggal_akhir', $data->tanggal_akhir)
                    ->where('id_instruktur', $request->instruktur)
                    ->get();
                    // return $posts;
            
                foreach ($posts as $post){
                    $post->update([
                        'level'     => $request->level,
                        'durasi'     => $request->durasi,
                        'pax'     => $request->pax,
                        'feedback'     => $request->feedback,
                        'tanggal_awal'     => $request->tanggal_awal,
                        'tanggal_akhir'     => $request->tanggal_akhir,
                        'keterangan'     => $request->keterangan,
                    ]);
                }
      return redirect()->route('rekapmengajarinstruktur.index')->with(['success' => 'Data Berhasil Diupdate!']);
    }

    public function sinkronData()
    {
        $data = rekapMengajarInstruktur::with('rkm', 'instruktur')->get();
        foreach ($data as $value) {
            // Mengambil id_rkm dan memisahkannya menjadi array
            $id_rkm = explode(',', $value->id_rkm); // Gunakan $value->id_rkm
            $totalPax = 0;
            $totalFeedbackInstruktur1 = 0;
            $totalFeedbackInstruktur2 = 0;
            $totalFeedbackAsisten = 0;
            $count = 0; // Untuk menghitung jumlah RKM yang diproses
    
            foreach ($id_rkm as $rkm_id) {
                // Mengambil RKM berdasarkan id_rkm
                $rkm = RKM::with('materi', 'instruktur', 'instruktur2', 'asisten', 'nilaifeedback')
                    ->where('id', $rkm_id)
                    ->first();
    
                if ($rkm) { // Pastikan RKM ditemukan
                    $totalPax += $rkm->pax; // Asumsi bahwa $rkm->pax ada
    
                    // Mengambil feedback
                    $response = $this->feedbackController->getNilaiFeedbackInstRKM($rkm->id);
                    $feedback = json_decode($response->getContent());
    
                    // Menambahkan feedback ke total berdasarkan instruktur
                    if ($value->id_instruktur == $rkm->instruktur_key) {
                        $totalFeedbackInstruktur1 += $feedback->average->instruktur ?? 0; 
                    } elseif ($value->id_instruktur == $rkm->instruktur_key2) {
                        $totalFeedbackInstruktur2 += $feedback->average->instruktur2 ?? 0;
                    } elseif ($value->id_instruktur == $rkm->asisten_key) {
                        $totalFeedbackAsisten += $feedback->average->asisten ?? 0; 
                    }
    
                    $count++; // Meningkatkan jumlah RKM yang diproses
                }
            }
    
            // Menghitung rata-rata feedback jika ada RKM yang diproses
            if ($count > 0) {
                $averageFeedbackInstruktur1 = $count > 0 ? $totalFeedbackInstruktur1 / $count : 0;
                $averageFeedbackInstruktur2 = $count > 0 ? $totalFeedbackInstruktur2 / $count : 0;
                $averageFeedbackAsisten = $count > 0 ? $totalFeedbackAsisten / $count : 0;
    
                // Memperbarui feedback jika ada perubahan
                if ($value->id_instruktur == $rkm->instruktur_key && $value->feedback !== $averageFeedbackInstruktur1) {
                    $value->update(['feedback' => $averageFeedbackInstruktur1]);
                } elseif ($value->id_instruktur == $rkm->instruktur_key2 && $value->feedback !== $averageFeedbackInstruktur2) {
                    $value->update(['feedback' => $averageFeedbackInstruktur2]);
                } elseif ($value->id_instruktur == $rkm->asisten_key && $value->feedback !== $averageFeedbackAsisten) {
                    $value->update(['feedback' => $averageFeedbackAsisten]);
                }
            }
    
            // Memperbarui total pax jika ada perubahan
            if ($value->pax !== $totalPax) {
                $value->update(['pax' => $totalPax]);
            }
        }
    
        return response()->json([
            'success' => true,
            'message' => 'Sudah Di Sinkronisasi, Silahkan Klik ulang Cari Data',
        ]);
    }
    


    public function getTunjanganEdu($id, $month, $year)
    {
        $karyawan = karyawan::findOrFail($id);
        $data = rekapMengajarInstruktur::where('bulan', $month)
                ->where('tahun', $year)
                ->where('id_instruktur', $karyawan->kode_karyawan)
                ->get();

        // Mengambil total_tunjangan dan menghitung total
        $tunjanganEducation = $data->map(function ($datas) {
            return [
                'id_instruktur' => $datas->id_instruktur,
                'total_tunjangan' => (int)$datas->total_tunjangan // Pastikan ini adalah integer
            ];
        });

        // Menghitung total tunjangan
        $totalTunjangan = $tunjanganEducation->sum('total_tunjangan');

        // Mengembalikan data dan total tunjangan
        return [
            'tunjangan' => $tunjanganEducation,
            'total_tunjangan' => $totalTunjangan
        ];
    }



}
