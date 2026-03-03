<?php

namespace App\Http\Controllers\KPI;

use App\Http\Controllers\Controller;
use App\Models\AbsensiKaryawan;
use App\Models\activityLog;
use App\Models\ChecklistKeperluan;
use App\Models\ContentSchedule;
use App\Models\detailPersonKPI;
use App\Models\DetailTargetKPI;
use App\Models\formPenilaian;
use App\Models\karyawan;
use App\Models\Kegiatan;
use App\Models\Nilaifeedback;
use App\Models\nilaiKPI;
use App\Models\outstanding;
use App\Models\RKM;
use App\Models\SurveyKepuasan;
use App\Models\targetKPI;
use App\Models\Tickets;
use Carbon\Carbon;
use Google\Service\CloudDeploy\Target;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TargetKPIController extends Controller
{
    public function kpiIndex()
    {
        $daftarKaryawan = karyawan::where('status_aktif', '1')->get();

        return view('KPIdata.TargetDivisi.index', compact('daftarKaryawan'));
    }

    public function getKaryawanByJabatan(Request $request)
    {
        $jabatanList = $request->input('jabatan', []);

        if (!is_array($jabatanList)) {
            $jabatanList = [$jabatanList];
        }

        $karyawan = Karyawan::whereIn('jabatan', $jabatanList)
            ->select('id', 'nama_lengkap', 'jabatan')
            ->where('status_aktif', '1')
            ->get()
            ->map(function ($k) {
                return [
                    'id' => $k->id,
                    'text' => $k->nama_lengkap . ' (' . $k->jabatan . ')',
                ];
            });

        return response()->json($karyawan);
    }

    public function createTarget(Request $request)
    {
        $validated = $request->validate([
            'id_pembuat' => 'required',
            'judul_kpi' => 'required',
            'jabatan' => 'required|array',
            'jabatan.*' => 'required|string',
            'karyawan' => 'required|array',
            'karyawan.*' => 'required',
            'jangka_target' => 'required|string',
            'detail_jangka' => 'nullable',
            'tipe_target' => 'required|string',
            'nilai_target' => 'required',
            'asistant_route' => 'required',
        ]);

        $createTarget = targetKPI::create([
            'id_assistant' => null,
            'id_pembuat' => $validated['id_pembuat'],
            'judul' => $validated['judul_kpi'],
            'deskripsi' => $request->input('deskripsi_kpi'),
            'asistant_route' => $validated['asistant_route'],
            'status' => '0',
        ]);

        if ($createTarget) {
            foreach ($validated['jabatan'] as $jabatan) {
                $dataDivisi = karyawan::where('jabatan', $jabatan)->first();

                $detailStore = DetailTargetKPI::create([
                    'id_targetKPI' => $createTarget->id,
                    'jabatan' => $jabatan,
                    'divisi' => $dataDivisi?->divisi,
                    'jangka_target' => $validated['jangka_target'],
                    'detail_jangka' => $validated['detail_jangka'],
                    'tipe_target' => $validated['tipe_target'],
                    'nilai_target' => $validated['nilai_target'],
                ]);

                if ($detailStore) {
                    $karyawanDiJabatanIni = karyawan::whereIn('id', $validated['karyawan'])->where('jabatan', $jabatan)->pluck('id')->toArray();

                    foreach ($karyawanDiJabatanIni as $karyawanId) {
                        detailPersonKPI::create([
                            'id_target' => $createTarget->id,
                            'detailTargetKey' => $detailStore->id,
                            'id_karyawan' => $karyawanId,
                        ]);
                    }
                }
            }
        }

        return response()->json(
            [
                'message' => 'Target berhasil dibuat',
            ],
            201,
        );
    }

    public function hapusTarget($id)
    {
        $target = targetKPI::with('detailTargetKPI.detailPersonKPI')->find($id);

        if (!$target) {
            return response()->json(
                [
                    'message' => 'Target tidak ditemukan',
                ],
                404,
            );
        }

        foreach ($target->detailTargetKPI as $detail) {
            $detail->detailPersonKPI()->delete();
        }

        $target->detailTargetKPI()->delete();

        $target->delete();

        return response()->json([
            'message' => 'Berhasil menghapus target',
        ]);
    }

    public function manualValue(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'manual_value' => 'nullable|numeric',
            'manual_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
        ]);

        $dataTarget = targetKPI::where('id', $request->id)->first();

        if (!$dataTarget) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Target KPI tidak ditemukan',
                ],
                404,
            );
        }

        $details = DetailTargetKPI::where('id_targetKPI', $dataTarget->id)->get();

        $existingDocument = $details->first()?->manual_document;

        $updateData = [
            'manual_value' => $request->manual_value,
        ];

        if ($request->hasFile('manual_document') && !$existingDocument) {
            $filePath = $request->file('manual_document')->store('manual_documents', 'public');

            $updateData['manual_document'] = $filePath;
        }

        DetailTargetKPI::where('id_targetKPI', $dataTarget->id)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil memasukkan data manual',
        ]);
    }

    public function getProgressDasboard(Request $request)
    {
        $user = auth()->user();
        $id_pembuat = $user->id;
        $jabatan_pembuat = $user->jabatan;
        $idUser = $request->idUser;
        $typeGet = $request->typeGet;

        $targetEmployeeId = (filled($idUser) && filled($typeGet)) ? $idUser : $id_pembuat;
        $karyawan = karyawan::find($targetEmployeeId);

        if (!$karyawan) {
            return response()->json(['average_progress' => 0, 'message' => 'Karyawan tidak ditemukan'], 404);
        }

        $dashboardData = $this->calculatePersonalKpiDashboard(
            $targetEmployeeId,
            $jabatan_pembuat,
            $id_pembuat,
            $idUser,
            $typeGet
        );

        $divisionTeamData = $this->getDivisionTeamDashboard($id_pembuat, $jabatan_pembuat);

        $divisionKpiData = $this->getDivisionKpiOverview($id_pembuat);

        return response()->json([
            'output_1' => $dashboardData,
            'output_2' => $divisionTeamData,
            'output_3' => $divisionKpiData,
        ]);
    }

    private function getDivisionKpiOverview($id_pembuat)
    {
        $currentYear = now()->year;

        $divisions = karyawan::whereNotNull('divisi')
            ->whereNotIn('divisi', ['', 'Pilih Divisi', 'Direksi'])
            ->distinct()
            ->pluck('divisi');

        $result = [];

        foreach ($divisions as $divisi) {
            $employees = karyawan::where('divisi', $divisi)->get();

            $totalKpiValue = 0;
            $countEmployees = 0;
            $monthlyKpiValues = [];

            foreach ($employees as $employee) {
                $kpiData = $this->calculateEmployeeTargetKpi($employee->id, $currentYear);

                if ($kpiData['avgTargetYearly'] > 0) {
                    $totalKpiValue += $kpiData['avgTargetYearly'];
                    $countEmployees++;
                }

                if (!empty($kpiData['monthlyTargetValues'])) {
                    foreach ($kpiData['monthlyTargetValues'] as $monthKey => $monthVal) {
                        if (!isset($monthlyKpiValues[$monthKey])) {
                            $monthlyKpiValues[$monthKey] = [];
                        }
                        $monthlyKpiValues[$monthKey][] = $monthVal;
                    }
                }
            }

            $avgKpiValue = $countEmployees > 0 ? round($totalKpiValue / $countEmployees, 2) : 0;

            $performance = 0;
            $performanceTitle = 'Stabil';

            if (count($monthlyKpiValues) >= 2) {
                $months = array_keys($monthlyKpiValues);
                sort($months);

                $lastMonth = $months[count($months) - 1];
                $prevMonth = $months[count($months) - 2];

                $currentMonthAvg = count($monthlyKpiValues[$lastMonth]) > 0
                    ? array_sum($monthlyKpiValues[$lastMonth]) / count($monthlyKpiValues[$lastMonth])
                    : 0;

                $prevMonthAvg = count($monthlyKpiValues[$prevMonth]) > 0
                    ? array_sum($monthlyKpiValues[$prevMonth]) / count($monthlyKpiValues[$prevMonth])
                    : 0;

                if ($prevMonthAvg > 0) {
                    $performance = round((($currentMonthAvg - $prevMonthAvg) / $prevMonthAvg) * 100, 2);
                } elseif ($currentMonthAvg > 0) {
                    $performance = 100;
                }

                if ($performance > 5) {
                    $performanceTitle = 'Naik';
                } elseif ($performance < -5) {
                    $performanceTitle = 'Turun';
                }
            }

            $result[] = [
                'divisi' => $divisi,
                'nilai_kpi' => $avgKpiValue,
                'performance' => $performance,
                'performance_title' => $performanceTitle,
                'tahun' => $currentYear
            ];
        }

        return $result;
    }

    private function calculateEmployeeTargetKpi($employeeId, $year)
    {
        $kpiQuery = targetKPI::with(['detailTargetKPI.detailPersonKPI'])
            ->whereYear('created_at', $year);

        $kpiQuery->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($employeeId) {
            $q->where('id_karyawan', $employeeId);
        });

        $listKPI = $kpiQuery->get();

        $totalTargetProgress = 0;
        $countTarget = 0;
        $monthlyTargetValues = [];

        foreach ($listKPI as $item) {
            $progress = $this->getProgressValue($item, $employeeId);
            if (is_numeric($progress)) {
                $totalTargetProgress += $progress;
                $countTarget++;

                $monthKey = $item->created_at->format('Y-m');
                if (!isset($monthlyTargetValues[$monthKey])) {
                    $monthlyTargetValues[$monthKey] = 0;
                }
                $monthlyTargetValues[$monthKey] += $progress;
            }
        }

        $avgTargetYearly = $countTarget > 0 ? round($totalTargetProgress / $countTarget, 2) : 0;

        foreach ($monthlyTargetValues as $monthKey => $total) {
            $count = $listKPI->filter(fn($k) => $k->created_at->format('Y-m') == $monthKey)->count();
            $monthlyTargetValues[$monthKey] = $count > 0 ? round($total / $count, 2) : 0;
        }

        return [
            'avgTargetYearly' => $avgTargetYearly,
            'monthlyTargetValues' => $monthlyTargetValues
        ];
    }

    private function getDivisionTeamDashboard($id_pembuat, $jabatan_pembuat)
    {
        $currentUser = karyawan::find($id_pembuat);

        if (!$currentUser || empty($currentUser->divisi)) {
            return [];
        }

        $divisiUser = $currentUser->divisi;

        $teamMembers = karyawan::where('divisi', $divisiUser)
            ->get();

        $result = [];

        foreach ($teamMembers as $member) {
            $kpiData = $this->calculatePersonalKpiDashboard(
                $member->id,
                $jabatan_pembuat,
                $id_pembuat,
                $member->id,
                'divisi'
            );

            $result[] = [
                'nama_karyawan' => $member->nama_lengkap,
                'jabatan' => $member->divisi,
                'nilaitargetkpi' => $kpiData['nilai_kpi_anda'] ?? 0,
                'performance' => $kpiData['performance_title'] ?? 'Stabil',
                'nilai_performance' => $kpiData['performance'] ?? 0,
            ];
        }

        return $result;
    }


    private function calculatePersonalKpiDashboard($targetEmployeeId, $jabatan_pembuat, $id_pembuat, $idUser, $typeGet)
    {
        $currentYear = now()->year;

        $kpiQuery = targetKPI::with(['detailTargetKPI.detailPersonKPI'])
            ->whereYear('created_at', $currentYear);

        if (filled($idUser) && filled($typeGet)) {
            $kpiQuery->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($targetEmployeeId) {
                $q->where('id_karyawan', $targetEmployeeId);
            });
        } else {
            $hasTarget = targetKPI::whereYear('created_at', $currentYear)
                ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($targetEmployeeId) {
                    $q->where('id_karyawan', $targetEmployeeId);
                })->exists();

            if ($hasTarget) {
                $kpiQuery->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($targetEmployeeId) {
                    $q->where('id_karyawan', $targetEmployeeId);
                });
            } elseif ($jabatan_pembuat !== 'GM') {
                $kpiQuery->where('id_pembuat', $id_pembuat);
            } else {
                $kpiQuery->where('id', -1);
            }
        }

        $listKPI = $kpiQuery->get();

        $listPenilaian = formPenilaian::where('id_karyawan', $targetEmployeeId)
            ->whereYear('created_at', $currentYear)
            ->orderBy('created_at', 'asc')
            ->get();

        $allNilaiKPI = nilaiKPI::where('id_evaluated', $targetEmployeeId)
            ->whereYear('created_at', $currentYear)
            ->get();

        $totalTargetProgress = 0;
        $countTarget = 0;

        foreach ($listKPI as $item) {
            $progress = $this->getProgressValue($item, $targetEmployeeId);
            if (is_numeric($progress)) {
                $totalTargetProgress += $progress;
                $countTarget++;
            }
        }
        $avgTargetYearly = $countTarget > 0 ? round($totalTargetProgress / $countTarget, 2) : 0;

        $avgPenilaianYearly = $this->calculatePenilaianScore($allNilaiKPI);

        $nilaiKpiAnda = 0;
        $titleGetData = '';

        if ($avgTargetYearly == 0 && $avgPenilaianYearly == 0) {
            $nilaiKpiAnda = 0;
            $titleGetData = 'Tidak ada data';
        } elseif ($avgTargetYearly == 0) {
            $nilaiKpiAnda = $avgPenilaianYearly * 0.4;
            $titleGetData = 'Dari Penilaian';
        } elseif ($avgPenilaianYearly == 0) {
            $nilaiKpiAnda = $avgTargetYearly;
            $titleGetData = 'Dari Target KPI';
        } else {
            $nilaiKpiAnda = round(($avgTargetYearly * 0.6) + ($avgPenilaianYearly * 0.4), 2);
            $titleGetData = 'Gabungan Target KPI & Penilaian';
        }

        $kpiPerbulan = [];
        $currentDate = now();

        for ($i = 3; $i >= 0; $i--) {
            $monthDate = $currentDate->copy()->subMonths($i);
            $year = $monthDate->year;
            $month = $monthDate->month;
            $monthLabel = $monthDate->locale('id')->isoFormat('MMMM YYYY');

            $monthlyTargetProgress = 0;
            $monthlyCountTarget = 0;
            $kpiBulanIni = $listKPI->filter(
                fn($k) =>
                $k->created_at->year == $year && $k->created_at->month == $month
            );

            foreach ($kpiBulanIni as $item) {
                $progress = $this->getProgressValue($item, $targetEmployeeId);
                if (is_numeric($progress)) {
                    $monthlyTargetProgress += $progress;
                    $monthlyCountTarget++;
                }
            }
            $valTarget = $monthlyCountTarget > 0 ? round($monthlyTargetProgress / $monthlyCountTarget, 2) : 0;

            $valPenilaian = 0;
            $targetMonthStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
            $targetMonthEnd = $targetMonthStart->copy()->endOfMonth();

            foreach ($listPenilaian as $eval) {
                $evalDate = \Carbon\Carbon::parse($eval->created_at);

                $firstEvalOfYear = $listPenilaian->first(
                    fn($e) =>
                    \Carbon\Carbon::parse($e->created_at)->year == $evalDate->year
                );

                $isFirstEvaluation = ($eval->id == $firstEvalOfYear->id);
                $evalStart = $isFirstEvaluation
                    ? \Carbon\Carbon::create($evalDate->year, 1, 1)->startOfDay()
                    : $evalDate->copy()->startOfMonth();

                $nextEval = $listPenilaian->first(fn($e) => \Carbon\Carbon::parse($e->created_at)->gt($evalDate));

                $evalEnd = $nextEval
                    ? \Carbon\Carbon::parse($nextEval->created_at)->subDay()
                    : \Carbon\Carbon::create($evalDate->year, 12, 31)->endOfDay();

                if ($targetMonthStart >= $evalStart && $targetMonthStart <= $evalEnd) {
                    $nilaiEvalBulanIni = $allNilaiKPI->filter(fn($n) => $n->kode_form == $eval->kode_form);
                    $valPenilaian = $this->calculatePenilaianScore($nilaiEvalBulanIni);
                    break;
                }
            }

            $finalMonthly = 0;
            if ($valTarget == 0 && $valPenilaian == 0)
                $finalMonthly = 0;
            elseif ($valTarget == 0)
                $finalMonthly = $valPenilaian * 0.4;
            elseif ($valPenilaian == 0)
                $finalMonthly = $valTarget;
            else
                $finalMonthly = round(($valTarget * 0.6) + ($valPenilaian), 2);

            $kpiPerbulan[] = [
                'bulan' => $monthLabel,
                'nilai' => $finalMonthly,
            ];
        }

        $performance = 0;
        $performanceTitle = 'Stabil';

        if (count($kpiPerbulan) >= 2) {
            $currentVal = $kpiPerbulan[count($kpiPerbulan) - 1]['nilai'];
            $lastVal = $kpiPerbulan[count($kpiPerbulan) - 2]['nilai'];

            if ($lastVal > 0) {
                $performance = round((($currentVal - $lastVal) / $lastVal) * 100, 2);
            } elseif ($currentVal > 0) {
                $performance = 100;
            }

            if ($performance > 5)
                $performanceTitle = 'Naik';
            elseif ($performance < -5)
                $performanceTitle = 'Turun';
        }

        $deadline = "{$currentYear}-12-31 23:59:59";
        $countdown = 'Deadline terlampaui';
        $now = now();
        $end = \Carbon\Carbon::parse($deadline);

        if ($now->lt($end)) {
            $diff = $now->diff($end);
            $countdown = "{$diff->days} hari {$diff->h} jam";
        }

        return [
            'nilai_kpi_anda' => $nilaiKpiAnda,
            'progress_kpi_perbulan' => $kpiPerbulan,
            'performance' => $performance,
            'performance_title' => $performanceTitle,
            'deadline' => $deadline,
            'countdown' => $countdown,
            'titleGet_data' => $titleGetData,
        ];
    }
    private function calculatePenilaianScore($collectionNilaiKPI)
    {
        $persentaseJenis = [
            'General Manager' => 35,
            'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
            'Rekan Kerja (Satu Divisi)' => 20,
            'Pekerja (Beda Divisi)' => 10,
            'Self Apprisial' => 5
        ];

        $jenisTotalRaw = [];

        foreach ($persentaseJenis as $jenis => $bobot) {
            $nilaiForJenis = $collectionNilaiKPI
                ->where('jenis_penilaian', $jenis)
                ->pluck('nilai')
                ->filter(fn($n) => is_numeric($n));

            if ($nilaiForJenis->isNotEmpty()) {
                $avgNilai = $nilaiForJenis->avg();
                $jenisTotalRaw[$jenis] = ($avgNilai * $bobot) / 100;
            }
        }

        if (empty($jenisTotalRaw)) {
            return 0;
        }

        $totalScore = array_sum($jenisTotalRaw);
        return round($totalScore, 2);
    }

    private function getProgressValue($item, $personId)
    {
        $route = $item->asistant_route;
        $methods = [
            'Kepuasan Pelanggan' => 'calculateProgressKepuasanPelanggan',
            'Pemasukan Kotor' => 'calculatePemasukanKotor',
            'pemasukan bersih' => 'calculatePemasukanBersih',
            'rasio biaya operasional terhadap revenue' => 'calculateRasioBiayaOperasionalTerhadapRevenue',
            'peserta puas dengan pelayanan dan fasilitas training' => 'calculatePesertaPuasDenganPelayananDanFasilitasTraining',
            'dorong inovasi pelayanan' => 'calculateDorongInovasiPelayanan',
            'report persiapan kelas' => 'calculateReportPersiapanKelas',
            'outstanding' => 'calculateOutstanding',
            'kepuasan client ITSM' => 'calculateProgressKepuasanClientITSM',
            'availability sistem internal kritis' => 'calculateAvailabilitySistemInternalKritis',
            'meningkatkan kepuasan dan loyalitas peserta/client' => 'calculateMeningkatkanKepuasanDanLoyalitasPeserta',
            'ketepatan waktu penyelesaian fitur' => 'calculateProgressKetepatanWaktuPenyelesaianFitur',
            'mengukur kualitas aplikasi agar minim bug' => 'calculateMengukurKualitasAplikasiAgarMinimBug',
            'konsistensi campaign digital' => 'calculateKonsistensiCampaignDigital',
            'keberhasilan support memenuhi sla' => 'calculateTingkatKeberhasilanSupportMemenuhiSLA',
            'inisiatif efisiensi keuangan' => 'calculateInisiatifEfisiensiKeuangan',
            'pelaksanaan kegiatan karyawan' => 'calculatePelaksanaanKegiatanKaryawan',
        ];

        if (isset($methods[$route]) && method_exists($this, $methods[$route])) {
            return $this->{$methods[$route]}($item, $personId);
        }
        return null;
    }

    public function getDataTarget(Request $request)
    {
        $user = auth()->user();
        $id_pembuat = $user->id;
        $jabatan_pembuat = $user->jabatan;

        $idUser = $request->idUser;
        $typeGet = $request->typeGet;

        if (filled($idUser) && filled($typeGet)) {
            $karyawan = karyawan::find($idUser);
            if (!$karyawan) {
                return response()->json(['message' => 'Karyawan tidak ditemukan'], 404);
            }

            $divisiUser = $karyawan->divisi;
        } else {
            $karyawan = karyawan::find($id_pembuat);
            if (!$karyawan) {
                return response()->json(['message' => 'Karyawan tidak ditemukan'], 404);
            }

            $divisiUser = $karyawan->divisi;
        }

        if ($jabatan_pembuat === 'GM' || $jabatan_pembuat === 'HRD' || $jabatan_pembuat === 'Direktur Utama') {
            $jabatanKhusus = ['SPV Sales', 'Koordinator ITSM', 'Education Manager', 'GM'];

            $dataJabatan = karyawan::where(function ($q) use ($jabatanKhusus) {
                $q->whereIn('jabatan', $jabatanKhusus)->orWhere('divisi', 'Office');
            })
                ->whereNotIn('jabatan', ['Direktur Utama', 'Direktur'])
                ->distinct()
                ->pluck('jabatan');
        } else {
            $dataJabatan = karyawan::where('divisi', $divisiUser)
                ->whereNotIn('jabatan', ['Direktur Utama', 'Direktur'])
                ->distinct()
                ->pluck('jabatan');
        }

        $query = targetKPI::with(['karyawan', 'detailTargetKPI'])->whereYear('created_at', now()->year);

        if (filled($idUser) && filled($typeGet)) {
            $query->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($idUser) {
                $q->where('id_karyawan', $idUser);
            });
        } elseif ($jabatan_pembuat !== 'GM') {
            $query->where('id_pembuat', $id_pembuat);
        }

        $detailList = $query->get();

        $data = [
            'detail' => $detailList
                ->map(function ($item) use ($idUser) {
                    $detail = $item->detailTargetKPI->first();
                    if (!$detail) {
                        return null;
                    }

                    $tenggat_waktu = null;

                    switch (strtolower($detail->jangka_target)) {
                        case 'tahunan':
                            $year = (int) $detail->detail_jangka;
                            $tenggat_waktu = date('Y-m-d', strtotime("last day of December $year"));
                            break;
                    }

                    $personId = !empty($idUser) ? (int) $idUser : null;

                    $progress = null;
                    //office
                    //GM
                    if ($item->asistant_route === 'Kepuasan Pelanggan') {
                        $progress = $this->calculateProgressKepuasanPelanggan($item, $personId);
                    } elseif ($item->asistant_route === 'Pemasukan Kotor') {
                        $progress = $this->calculatePemasukanKotor($item, $personId);
                    } elseif ($item->asistant_route == 'pemasukan bersih') {
                        $progress = $this->calculatePemasukanBersih($item, $personId);
                    } elseif ($item->asistant_route === 'rasio biaya operasional terhadap revenue') {
                        $progress = $this->calculateRasioBiayaOperasionalTerhadapRevenue($item, $personId);
                    }
                    //CS
                    elseif ($item->asistant_route === 'peserta puas dengan pelayanan dan fasilitas training') {
                        $progress = $this->calculatePesertaPuasDenganPelayananDanFasilitasTraining($item, $personId);
                    } elseif ($item->asistant_route === 'dorong inovasi pelayanan') {
                        $progress = $this->calculateDorongInovasiPelayanan($item, $personId);
                    } elseif ($item->asistant_route === 'report persiapan kelas') {
                        $progress = $this->calculateReportPersiapanKelas($item, $personId);
                    }
                    //finance
                    elseif ($item->asistant_route === 'outstanding') {
                        $progress = $this->calculateOutstanding($item, $personId);
                    } elseif ($item->asistant_route === 'inisiatif efisiensi keuangan') {
                        $progress = $this->calculateInisiatifEfisiensiKeuangan($item, $personId);
                    }

                    //HRD
                    elseif ($item->asistant_route === 'pelaksanaan kegiatan karyawan') {
                        $progress = $this->calculatePelaksanaanKegiatanKaryawan($item, $personId);
                    }

                    //ITSM
                    //All kecuali Koordinator ITSM
                    elseif ($item->asistant_route === 'kepuasan client ITSM') {
                        $progress = $this->calculateProgressKepuasanClientITSM($item, $personId);
                    }
                    //Koordinator ITSM
                    elseif ($item->asistant_route === 'availability sistem internal kritis') {
                        $progress = $this->calculateAvailabilitySistemInternalKritis($item, $personId);
                    } elseif ($item->asistant_route === 'meningkatkan kepuasan dan loyalitas peserta/client') {
                        $progress = $this->calculateMeningkatkanKepuasanDanLoyalitasPeserta($item, $personId);
                    }
                    //Programmmer
                    elseif ($item->asistant_route === 'ketepatan waktu penyelesaian fitur') {
                        $progress = $this->calculateProgressKetepatanWaktuPenyelesaianFitur($item, $personId);
                    } elseif ($item->asistant_route === 'mengukur kualitas aplikasi agar minim bug') {
                        $progress = $this->calculateMengukurKualitasAplikasiAgarMinimBug($item, $personId);
                    }
                    //Tim Digital
                    elseif ($item->asistant_route === 'konsistensi campaign digital') {
                        $progress = $this->calculateKonsistensiCampaignDigital($item, $personId);
                    }
                    //TS
                    elseif ($item->asistant_route === 'keberhasilan support memenuhi sla') {
                        $progress = $this->calculateTingkatKeberhasilanSupportMemenuhiSLA($item, $personId);
                    }

                    return [
                        'id' => $item->id,
                        'pembuat' => $item->karyawan->nama_lengkap ?? null,
                        'id_pembuat' => $item->id_pembuat,
                        'judul' => $item->judul,
                        'deskripsi' => $item->deskripsi,
                        'jabatan' => $item->detailTargetKPI->pluck('jabatan')->unique()->values(),
                        'divisi' => $item->detailTargetKPI->pluck('divisi')->unique()->values(),
                        'asistant_route' => $item->asistant_route,
                        'jangka_target' => $detail->jangka_target,
                        'detail_jangka' => $detail->detail_jangka,
                        'tipe_target' => $detail->tipe_target,
                        'nilai_target' => $detail->nilai_target,
                        'manual_value' => $detail->manual_value,
                        'status' => $item->status,
                        'created_at' => $item->created_at,
                        'tenggat_waktu' => $tenggat_waktu,
                        'progress' => $progress,
                    ];
                })
                ->filter()
                ->values(),
            'jabatan_list' => $dataJabatan,
        ];

        return response()->json($data);
    }

    //Target office
    //target GM
    private function calculateProgressKepuasanPelanggan($item, $personId)
    {
        $detailJangkas = $item->detailTargetKPI->pluck('detail_jangka')->filter();

        if ($detailJangkas->isEmpty()) {
            Log::warning("Tidak ada detail_jangka untuk target ID: {$item->id}");
            return 0;
        }

        $tahun = (int) $detailJangkas->first();

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$item->id}");
            return 0;
        }

        $start = "$tahun-01-01";
        $end = "$tahun-12-31";

        $feedbacks = Nilaifeedback::with('rkm.materi')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        $groupedFeedbacks = $feedbacks
            ->groupBy(function ($feedback) {
                return optional($feedback->rkm)->materi?->nama_materi . '/' . optional($feedback->rkm)->tanggal_awal;
            })
            ->filter();

        $averageFeedbacks = [];

        foreach ($groupedFeedbacks as $group) {
            $totalFeedbacks = $group->count();
            if ($totalFeedbacks === 0) {
                continue;
            }

            $averageM = round(($group->sum('M1') + $group->sum('M2') + $group->sum('M3') + $group->sum('M4')) / ($totalFeedbacks * 4), 1);
            $averageP = round(($group->sum('P1') + $group->sum('P2') + $group->sum('P3') + $group->sum('P4') + $group->sum('P5') + $group->sum('P6') + $group->sum('P7')) / ($totalFeedbacks * 7), 1);
            $averageF = round(($group->sum('F1') + $group->sum('F2') + $group->sum('F3') + $group->sum('F4') + $group->sum('F5')) / ($totalFeedbacks * 5), 1);
            $averageI = round(($group->sum('I1') + $group->sum('I2') + $group->sum('I3') + $group->sum('I4') + $group->sum('I5') + $group->sum('I6') + $group->sum('I7') + $group->sum('I8')) / ($totalFeedbacks * 8), 1);

            $averageIb = round(($group->sum('I1b') + $group->sum('I2b') + $group->sum('I3b') + $group->sum('I4b') + $group->sum('I5b') + $group->sum('I6b') + $group->sum('I7b') + $group->sum('I8b')) / ($totalFeedbacks * 8), 1);
            $averageIas = round(($group->sum('I1as') + $group->sum('I2as') + $group->sum('I3as') + $group->sum('I4as') + $group->sum('I5as') + $group->sum('I6as') + $group->sum('I7as') + $group->sum('I8as')) / ($totalFeedbacks * 8), 1);

            $averageValues = [$averageM, $averageP, $averageF, $averageI];
            if ($averageIb > 0) {
                $averageValues[] = $averageIb;
            }
            if ($averageIas > 0) {
                $averageValues[] = $averageIas;
            }

            $averageTotal = round(array_sum($averageValues) / count($averageValues), 1);
            $averageFeedbacks[] = $averageTotal;
        }

        $total = count($averageFeedbacks);
        if ($total > 0) {
            $above = count(array_filter($averageFeedbacks, fn($v) => $v >= 3.5));
            return round(($above / $total) * 100, 1);
        }

        return 0;
    }

    private function calculatePemasukanKotor($item, $personId)
    {
        $details = $item->detailTargetKPI;

        if ($details->isEmpty()) {
            return 0;
        }

        $tahun = (int) $details->first()->detail_jangka;
        $nilaiTarget = (float) $details->first()->nilai_target;

        if ($nilaiTarget <= 0) {
            return 0;
        }

        $totalSales = RKM::where('status', '0')->whereYear('tanggal_awal', $tahun)->select(DB::raw('SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_sales'))->value('total_sales');

        $totalSales = (float) ($totalSales ?? 0);

        if ($totalSales <= 0) {
            return 0;
        }

        $progress = $totalSales;

        return round($progress);
    }

    private function calculatePemasukanBersih($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        $labaKotor = $this->calculatePemasukanKotor($item, $personId);

        if ($labaKotor == 0) {
            return 0;
        }

        if (is_null($detail) || is_null($detail->manual_value)) {
            return 0;
        }

        $progress = 0;
        $manualValue = (float) $detail->manual_value;

        if ($labaKotor < $manualValue) {
            return 0;
        }

        if ($manualValue > 0) {
            $progress = ($manualValue / $labaKotor) * 100;
            $progress = min(100, $progress);
        }

        return round($progress, 1);
    }

    private function calculateRasioBiayaOperasionalTerhadapRevenue($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        $labaKotor = $this->calculatePemasukanKotor($item, $personId);

        if ($labaKotor == 0) {
            return 0;
        }

        if (is_null($detail) || is_null($detail->manual_value)) {
            return 0;
        }

        $progress = 0;
        $manualValue = (float) $detail->manual_value;

        if ($manualValue > 0) {
            $progress = ($manualValue / $labaKotor) * 100;
            $progress = min(100, $progress);
        }

        return round($progress, 1);
    }

    //CS
    private function calculatePesertaPuasDenganPelayananDanFasilitasTraining($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = [];

        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();
        foreach ($feedbacks as $fb) {
            $f1 = is_numeric($fb->F1) ? (float) $fb->F1 : 0;
            $f2 = is_numeric($fb->F2) ? (float) $fb->F2 : 0;
            $f3 = is_numeric($fb->F3) ? (float) $fb->F3 : 0;
            $f4 = is_numeric($fb->F4) ? (float) $fb->F4 : 0;
            $f5 = is_numeric($fb->F5) ? (float) $fb->F5 : 0;
            $p1 = is_numeric($fb->P1) ? (float) $fb->P1 : 0;
            $p2 = is_numeric($fb->P2) ? (float) $fb->P2 : 0;
            $p3 = is_numeric($fb->P3) ? (float) $fb->P3 : 0;
            $p4 = is_numeric($fb->P4) ? (float) $fb->P4 : 0;
            $p5 = is_numeric($fb->P5) ? (float) $fb->P5 : 0;
            $p6 = is_numeric($fb->P6) ? (float) $fb->P6 : 0;
            $p7 = is_numeric($fb->P7) ? (float) $fb->P7 : 0;

            $avg = ($f1 + $f2 + $f3 + $f4 + $f5 + $p1 + $p2 + $p3 + $p4 + $p5 + $p1 + $p2) / 12;
            $avg = min(4, max(1, $avg));
            $allScores[] = $avg;
        }

        if (empty($allScores)) {
            return 0;
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        return round($progress, 1);
    }

    private function calculateDorongInovasiPelayanan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        $nilaiTarget = (float) $detail->nilai_target;

        $progress = 0;

        if (!is_null($detail->manual_value)) {
            $manualValue = (float) $detail->manual_value;

            if ($manualValue > 0) {
                $progress = ($manualValue / $nilaiTarget) * 100;
                $progress = min(100, $progress);
            }
        }

        $progress = round($progress, 1);
        return round($progress, 1);
    }
    private function calculateReportPersiapanKelas($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        $nilaiTarget = (float) $detail->nilai_target;

        $tahun = (int) ($item->detail_jangka ?? now()->year);

        $totalRkm = RKM::whereYear('tanggal_awal', $tahun)->count();

        $totalTuntas = ChecklistKeperluan::whereYear('created_at', $tahun)
            ->where('materi', 1)
            ->where('kelas', 1)
            ->where('cb', 1)
            ->where('maksi', 1)
            ->where('keperluan_kelas', 1)
            ->count();

        if ($totalRkm > 0) {
            $progress = ($totalTuntas / $totalRkm) * 100;
        } else {
            $progress = 0;
        }

        return round($progress, 1);
    }


    //finance
    private function calculateOutstanding($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $hariIni = now()->startOfDay();

        $totalData = Outstanding::whereBetween('created_at', [$start, $end])->count();

        $tepatTenggat = Outstanding::whereBetween('created_at', [$start, $end])
            ->where('status_pembayaran', '1')
            ->whereDate('due_date', '>', 'tanggal_bayar')
            ->count();

        $presentase = ($tepatTenggat / $totalData) * 100;

        return round($presentase, 1);
    }

    private function calculateInisiatifEfisiensiKeuangan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->manual_value)) {
            return 0;
        }

        $progress = 0;
        $manualValue = (float) $detail->manual_value;
        $targetValue = (float) $detail->nilai_target;

        if ($targetValue == null) {
            return 0;
        }

        if ($manualValue > 0) {
            $progress = ($manualValue / $targetValue) * 100;
            $progress = min(100, $progress);
        }

        return round($progress, 1);
    }

    //HRD
    private function calculatePelaksanaanKegiatanKaryawan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->manual_value)) {
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $startOfYear = Carbon::create($tahun, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($tahun, 12, 31)->endOfDay();

        $kegiatans = Kegiatan::whereBetween('created_at', [$startOfYear, $endOfYear])->get();

        $totalKegiatan = $kegiatans->count();
        $totalKehadiranValid = 0;

        foreach ($kegiatans as $kegiatan) {
            $pesertaIds = is_array($kegiatan->id_peserta) ? $kegiatan->id_peserta : json_decode($kegiatan->id_peserta, true);

            if (empty($pesertaIds)) {
                continue;
            }

            $jumlahPeserta = count($pesertaIds);

            $tanggalKegiatan = Carbon::parse($kegiatan->waktu_kegiatan);
            $startOfDay = $tanggalKegiatan->copy()->startOfDay();
            $endOfDay = $tanggalKegiatan->copy()->endOfDay();

            $jumlahHadir = AbsensiKaryawan::whereIn('id_karyawan', $pesertaIds)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->where('keterangan', 'Masuk')
                ->count();

            $persentase = ($jumlahHadir / $jumlahPeserta) * 100;

            if ($persentase >= 80) {
                $totalKehadiranValid++;
            }
        }

        if ($totalKegiatan == 0) {
            return 0;
        }

        $progress = ($totalKehadiranValid / $totalKegiatan) * 100;

        return round($progress, 1);
    }

    //target ITSM
    //Koordinator ITSM
    private function calculateMeningkatkanKepuasanDanLoyalitasPeserta($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = [];

        $dataSurvey = SurveyKepuasan::whereBetween('created_at', [$start, $end])->get();
        foreach ($dataSurvey as $survey) {
            $nilaiQ1 = match ($survey->q1) {
                1 => 10,
                2 => 20,
                3 => 30,
                4 => 40,
                default => 0,
            };

            $nilaiQ4 = match ($survey->q4) {
                1 => 10,
                2 => 20,
                3 => 30,
                4 => 40,
                default => 0,
            };

            $nilaiQ2 = match ($survey->q2) {
                'Ya' => 20,
                'Tidak' => 10,
                default => 0,
            };

            $totalBaris = min(100, max(0, $nilaiQ1 + $nilaiQ2 + $nilaiQ4));
            $skor = 1 + ($totalBaris * 3) / 100;
            $allScores[] = $skor;
        }

        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();
        foreach ($feedbacks as $fb) {
            $f1 = is_numeric($fb->F1) ? (float) $fb->F1 : 0;
            $f2 = is_numeric($fb->F2) ? (float) $fb->F2 : 0;
            $f3 = is_numeric($fb->F3) ? (float) $fb->F3 : 0;
            $f4 = is_numeric($fb->F4) ? (float) $fb->F4 : 0;
            $f5 = is_numeric($fb->F5) ? (float) $fb->F5 : 0;

            $avg = ($f1 + $f2 + $f3 + $f4 + $f5) / 5;
            $avg = min(4, max(1, $avg));
            $allScores[] = $avg;
        }

        if (empty($allScores)) {
            return 0;
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        return round($progress, 1);
    }

    private function calculateAvailabilitySistemInternalKritis($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $logs = activityLog::whereBetween('status', ['100', '599'])
            ->whereBetween('checked_at', [$start, $end])
            ->get();

        if ($logs->isEmpty()) {
            return 0;
        }

        $totalChecks = $logs->count();
        $upChecks = $logs->where('is_up', 1)->count();

        $availability = ($upChecks / $totalChecks) * 100;

        return round($availability, 1);
    }

    //Tim Digital
    private function calculateKonsistensiCampaignDigital($item, $personId)
    {
        $details = $item->detailTargetKPI;

        if ($details->isEmpty()) {
            return 0;
        }

        $tahun = (int) $details->first()->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 1) {
            $tahun = now()->year;
        }

        $date = now()->format('Y-m-d');

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $contentSchedules = ContentSchedule::whereBetween('created_at', [$start, $end])
            ->whereNotNull('upload_date')
            ->get();

        if ($contentSchedules->isEmpty()) {
            return 0;
        }

        $weeklyCounts = [];
        foreach ($contentSchedules as $schedule) {
            $weekKey = Carbon::parse($schedule->created_at)->format('o-\WW');
            $weeklyCounts[$weekKey] = ($weeklyCounts[$weekKey] ?? 0) + 1;
        }

        $compliantWeeks = 0;
        $totalWeeksWithData = 0;

        foreach ($weeklyCounts as $count) {
            if ($count >= 1) {
                $totalWeeksWithData++;
                if ($count >= 3) {
                    $compliantWeeks++;
                }
            }
        }

        if ($totalWeeksWithData === 0) {
            return 0;
        }

        $konsistensiPersen = ($compliantWeeks / $totalWeeksWithData) * 100;

        return round($konsistensiPersen, 1);
    }

    //Programmer
    private function calculateMengukurKualitasAplikasiAgarMinimBug($item, $personId)
    {
        $details = $item->detailTargetKPI;

        if ($details->isEmpty()) {
            return 0;
        }

        $firstDetail = $details->first();
        $nilaiTarget = (float) $firstDetail->nilai_target;
        $tahun = (int) $firstDetail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun <= 0) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
            ->pluck('id_karyawan')
            ->unique()
            ->toArray();

        if (empty($idKaryawans)) {
            return 0;
        }

        $picNames = karyawan::whereIn('id', $idKaryawans)
            ->pluck('nama_lengkap')
            ->map(fn($nama) => explode(' ', trim($nama))[0] ?? '')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($picNames)) {
            return 0;
        }

        $errorQuery = Tickets::whereBetween('created_at', [$start, $end])
            ->where('kategori', 'Error (Aplikasi)')
            ->where('keperluan', 'Programming')
            ->whereNotNull('tanggal_selesai');

        $requestQuery = Tickets::whereBetween('created_at', [$start, $end])
            ->where('kategori', 'Request');
        if ($personId !== null) {
            $karyawanData = karyawan::find($personId);
            if (!$karyawanData) {
                return 0;
            }
            $firstName = explode(' ', trim($karyawanData->nama_lengkap))[0] ?? '';
            if (!$firstName) {
                return 0;
            }
            $errorQuery->where('pic', $firstName);
            $requestQuery->where('pic', $firstName);
        } else {
            $errorQuery->whereIn('pic', $picNames);
            $requestQuery->whereIn('pic', $picNames);
        }

        $ticketsError = $errorQuery->get();
        $ticketsRequest = $requestQuery->get();

        $jumlahError = $ticketsError->count();
        $jumlahRequest = $ticketsRequest->count();
        $totalTicket = $jumlahRequest + $jumlahError;

        if ($totalTicket === 0) {
            return 0;
        }

        $skorRasio = ($jumlahRequest / $totalTicket) * 100;

        if ($jumlahError === 0) {
            $rataSkorError = 100;
        } else {
            $totalSkorError = 0;

            foreach ($ticketsError as $ticket) {
                $startAt = Carbon::parse($ticket->created_at, 'Asia/Jakarta');
                $endAt = Carbon::parse($ticket->tanggal_selesai . ' ' . $ticket->jam_selesai, 'Asia/Jakarta');
                $durasiJam = $startAt->diffInHours($endAt);

                $skorDurasi = match (true) {
                    $durasiJam <= 4 => 100,
                    $durasiJam <= 8 => 80,
                    $durasiJam <= 24 => 60,
                    default => 30,
                };

                $bobot = match ($ticket->tingkat_kesulitan) {
                    'Major' => 1.5,
                    'Moderate' => 1.2,
                    default => 1.0,
                };

                $skorError = min(100, $skorDurasi * $bobot);
                $totalSkorError += $skorError;
            }

            $rataSkorError = $totalSkorError / $jumlahError;
        }

        $skorKualitas = $skorRasio * 0.5 + $rataSkorError * 0.5;

        $progress = ($skorKualitas / $nilaiTarget) * 100;
        $progress = round($progress, 1);
        $progress = min($progress, 100);

        return $progress;
    }

    private function calculateProgressKetepatanWaktuPenyelesaianFitur($item, $personId)
    {
        $details = $item->detailTargetKPI;

        if ($details->isEmpty()) {
            return 0;
        }

        $firstDetail = $details->first();
        $nilaiTarget = (float) $firstDetail->nilai_target;
        $tahun = (int) $firstDetail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun <= 0) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
            ->pluck('id_karyawan')
            ->unique()
            ->toArray();

        $picNames = karyawan::whereIn('id', $idKaryawans)
            ->pluck('nama_lengkap')
            ->map(fn($nama) => explode(' ', $nama)[0] ?? '')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if ($personId !== null) {
            $picJabatan = karyawan::whereIn('id', $idKaryawans)
                ->pluck('jabatan')
                ->unique()
                ->map(fn($n) => ucwords(strtolower($n)))
                ->values()
                ->toArray();
        } else {
            $targetJabatanList = $details->pluck('jabatan')->unique()->toArray();
            $picJabatan = karyawan::whereIn('jabatan', $targetJabatanList)
                ->pluck('jabatan')
                ->unique()
                ->map(fn($n) => ucwords(strtolower($n)))
                ->values()
                ->toArray();
        }

        $jabatanFilter = array_map(function ($jabatan) {
            return match (strtolower($jabatan)) {
                'programmer', 'koordinator itsm' => 'Programming',
                'technical support' => 'Technical Support',
                'tim digital' => 'Tim Digital',
                default => $jabatan,
            };
        }, $picJabatan);

        $jabatanFilter = array_unique(array_filter($jabatanFilter));

        if (empty($jabatanFilter)) {
            return 0;
        }

        $ticketQuery = Tickets::whereIn('keperluan', $jabatanFilter)
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('tanggal_selesai');

        if (!empty($picNames)) {
            if ($personId !== null) {
                $karyawanData = karyawan::find($personId);
                if (!$karyawanData) {
                    return 0;
                }
                $firstName = explode(' ', $karyawanData->nama_lengkap)[0] ?? '';
                $ticketQuery->where('pic', $firstName);
            } else {
                $ticketQuery->whereIn('pic', $picNames);
            }
        }

        $tickets = $ticketQuery->get();

        if ($tickets->isEmpty()) {
            return 0;
        }

        $metCount = 0;
        $total = $tickets->count();

        foreach ($tickets as $ticket) {
            $priority = 'Other';

            if (in_array($ticket->tingkat_kesulitan, ['Major', 'Moderate'])) {
                $priority = 'High';
            } elseif ($ticket->tingkat_kesulitan === 'Minor' && $ticket->kategori === 'Error (Aplikasi)') {
                $priority = 'Medium';
            } elseif ($ticket->kategori === 'Request') {
                $priority = 'Low';
            }

            $startAt = Carbon::parse($ticket->created_at, 'Asia/Jakarta');
            $endAt = Carbon::parse($ticket->tanggal_selesai . ' ' . $ticket->jam_selesai, 'Asia/Jakarta');
            $actualHours = $startAt->diffInHours($endAt);

            $slaMet = false;
            if ($priority === 'High' && $actualHours <= 24) {
                $slaMet = true;
            } elseif ($priority === 'Medium' && $actualHours <= 40) {
                $slaMet = true;
            } elseif ($priority === 'Low') {
                $slaMet = true;
            }

            if ($slaMet) {
                $metCount++;
            }
        }

        $realisasiPersen = ($metCount / $total) * 100;
        $progress = ($realisasiPersen / $nilaiTarget) * 100;

        return round($progress, 1);
    }

    //TS
    private function calculateTingkatKeberhasilanSupportMemenuhiSLA($item, $personId)
    {
        $details = $item->detailTargetKPI;

        if ($details->isEmpty()) {
            return 0;
        }

        $firstDetail = $details->first();
        $nilaiTarget = (float) $firstDetail->nilai_target;
        $tahun = (int) $firstDetail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun <= 0) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        // ✅ Ambil id_karyawan dari detailPersonKPI berdasarkan detailTargetKey
        $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
            ->pluck('id_karyawan')
            ->unique()
            ->toArray();

        if (empty($idKaryawans)) {
            return 0;
        }

        // ✅ Ambil first name dari karyawan untuk filter pic (sebagai array)
        $picNames = karyawan::whereIn('id', $idKaryawans)
            ->pluck('nama_lengkap')
            ->map(fn($nama) => explode(' ', trim($nama))[0] ?? '')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (empty($picNames)) {
            return 0;
        }

        // ✅ Ambil jabatan untuk mapping keperluan (keperluan = Programming/Technical Support)
        if ($personId !== null) {
            $picJabatan = karyawan::whereIn('id', $idKaryawans)
                ->pluck('jabatan')
                ->unique()
                ->map(fn($n) => ucwords(strtolower($n)))
                ->values()
                ->toArray();
        } else {
            $targetJabatanList = $details->pluck('jabatan')->unique()->toArray();
            $picJabatan = karyawan::whereIn('jabatan', $targetJabatanList)
                ->pluck('jabatan')
                ->unique()
                ->map(fn($n) => ucwords(strtolower($n)))
                ->values()
                ->toArray();
        }

        // ✅ Mapping jabatan ke keperluan
        $keperluanPatterns = [];
        foreach ($picJabatan as $jabatan) {
            $jabatanLower = strtolower($jabatan);
            if (str_contains($jabatanLower, 'programmer') || str_contains($jabatanLower, 'koordinator itsm')) {
                $keperluanPatterns[] = 'Programming';
            } elseif (str_contains($jabatanLower, 'technical support') || str_contains($jabatanLower, 'tech support')) {
                $keperluanPatterns[] = 'Technical Support';
            }
        }

        $keperluanPatterns = array_unique(array_filter($keperluanPatterns));

        if (empty($keperluanPatterns)) {
            return 0;
        }

        // ✅ Build query Tickets
        $ticketQuery = DB::table('tickets')
            ->select('created_at', 'tanggal_response', 'jam_response', 'tanggal_selesai', 'jam_selesai', 'pic', 'keperluan')
            ->whereIn('keperluan', $keperluanPatterns)
            ->whereNotNull('tanggal_selesai')
            ->whereBetween('created_at', [$start, $end]);

        // ✅ Filter by PIC: array jika personId null, single name jika personId ada
        if ($personId !== null) {
            // Specific person: ambil first name orang tersebut
            $karyawanData = karyawan::find($personId);
            if (!$karyawanData) {
                return 0;
            }
            $firstName = explode(' ', trim($karyawanData->nama_lengkap))[0] ?? '';
            if (!$firstName) {
                return 0;
            }
            $ticketQuery->where('pic', $firstName);
        } else {
            // All PICs: filter menggunakan array first name dari detailPersonKPI
            $ticketQuery->whereIn('pic', $picNames);
        }

        $rawTickets = $ticketQuery->get();

        if ($rawTickets->isEmpty()) {
            return 0;
        }

        $totalTickets = 0;
        $resolutionMet = 0;

        foreach ($rawTickets as $ticket) {
            try {
                $createdAt = Carbon::parse($ticket->created_at);

                $responseAt = null;
                if (!empty($ticket->tanggal_response) && !empty($ticket->jam_response)) {
                    $responseAt = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->tanggal_response . ' ' . $ticket->jam_response);
                }

                $resolvedAt = null;
                if (!empty($ticket->tanggal_selesai) && !empty($ticket->jam_selesai)) {
                    $resolvedAt = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->tanggal_selesai . ' ' . $ticket->jam_selesai);
                }

                if (!$resolvedAt || !$createdAt) {
                    continue;
                }

                $totalTickets++;

                $startResolution = $responseAt ?? $createdAt;

                $current = clone $startResolution;
                $endCalc = clone $resolvedAt;
                $totalHours = 0;

                $workStartHour = 8;
                $workEndHour = 17;

                while ($current->lt($endCalc)) {
                    if (in_array($current->dayOfWeek, [0, 6])) {
                        $current->startOfDay()->addDays(1)->setTime($workStartHour, 0, 0);
                        continue;
                    }

                    if ($current->hour < $workStartHour) {
                        $current->setTime($workStartHour, 0, 0);
                    }

                    if ($current->hour >= $workEndHour) {
                        $current->startOfDay()->addDays(1)->setTime($workStartHour, 0, 0);
                        continue;
                    }

                    $endOfWorkDay = clone $current;
                    $endOfWorkDay->setTime($workEndHour, 0, 0);

                    $segmentEnd = $endOfWorkDay->lt($endCalc) ? $endOfWorkDay : $endCalc;

                    $diffInMinutes = $current->diffInMinutes($segmentEnd);
                    $totalHours += $diffInMinutes / 60.0;

                    $current = clone $segmentEnd;
                    if ($current->lt($endCalc)) {
                        $current->startOfDay()->addDays(1)->setTime($workStartHour, 0, 0);
                    }
                }

                $actualResolutionHours = round($totalHours, 2);

                if ($actualResolutionHours <= 8) {
                    $resolutionMet++;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        if ($totalTickets === 0) {
            return 0;
        }

        $progress = ($resolutionMet / $totalTickets) * 100;
        return round($progress, 1);
    }

    //all jabatan kecuali Kooordinator ITSM
    private function calculateProgressKepuasanClientITSM($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = [];

        $dataSurvey = SurveyKepuasan::whereBetween('created_at', [$start, $end])->get();
        foreach ($dataSurvey as $survey) {
            $nilaiQ1 = match ($survey->q1) {
                1 => 10,
                2 => 20,
                3 => 30,
                4 => 40,
                default => 0,
            };

            $nilaiQ4 = match ($survey->q4) {
                1 => 10,
                2 => 20,
                3 => 30,
                4 => 40,
                default => 0,
            };

            $nilaiQ2 = match ($survey->q2) {
                'Ya' => 20,
                'Tidak' => 10,
                default => 0,
            };

            $totalBaris = min(100, max(0, $nilaiQ1 + $nilaiQ2 + $nilaiQ4));
            $skor = 1 + ($totalBaris * 3) / 100;
            $allScores[] = $skor;
        }

        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();
        foreach ($feedbacks as $fb) {
            $f1 = is_numeric($fb->F1) ? (float) $fb->F1 : 0;
            $f2 = is_numeric($fb->F2) ? (float) $fb->F2 : 0;
            $f3 = is_numeric($fb->F3) ? (float) $fb->F3 : 0;
            $f4 = is_numeric($fb->F4) ? (float) $fb->F4 : 0;
            $f5 = is_numeric($fb->F5) ? (float) $fb->F5 : 0;

            $avg = ($f1 + $f2 + $f3 + $f4 + $f5) / 5;
            $avg = min(4, max(1, $avg));
            $allScores[] = $avg;
        }

        if (empty($allScores)) {
            return 0;
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        return round($progress, 1);
    }

    //detail_target
    public function detailData(Request $request)
    {
        $idTarget = $request->id;
        $personId = $request->idUser ?? null;

        $query = targetKPI::with([
            'karyawan',
            'detailTargetKPI.detailPersonKPI.karyawan'
        ])->where('id', $idTarget);

        if ($personId !== null) {
            $query->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($personId) {
                $q->where('id_karyawan', $personId);
            });
        }

        $detailList = $query->get();

        $data = [
            'detail' => $detailList
                ->map(function ($itemDetail) use ($personId) {
                    $detail = $itemDetail->detailTargetKPI->first();
                    if (!$detail) {
                        return null;
                    }

                    $tenggat_waktu = null;

                    switch (strtolower($detail->jangka_target)) {
                        case 'tahunan':
                            $year = (int) $detail->detail_jangka;
                            $tenggat_waktu = date('Y-m-d', strtotime("last day of December $year"));
                            break;

                        case 'bulanan':
                            [$bulan, $tahun] = explode('-', str_replace(' ', '', $detail->detail_jangka));
                            $tenggat_waktu = date('Y-m-d', strtotime("last day of $tahun-$bulan"));
                            break;

                        case 'kuartalan':
                            if (preg_match('/Q(\d)\s*-\s*(\d{4})/i', $detail->detail_jangka, $m)) {
                                $bulan = $m[1] * 3;
                                $tenggat_waktu = date('Y-m-t', strtotime("{$m[2]}-$bulan-01"));
                            }
                            break;

                        case 'mingguan':
                            $tenggat_waktu = $detail->detail_jangka;
                            break;
                    }

                    $data = null;

                    // Target Detail Office - GM
                    if ($itemDetail->asistant_route === 'Kepuasan Pelanggan') {
                        $data = $this->calculateProgressKepuasanPelangganDetail($itemDetail);
                    } elseif ($itemDetail->asistant_route === 'Pemasukan Kotor') {
                        $data = $this->calculatePemasukanKotorDetail($itemDetail);
                    } elseif ($itemDetail->asistant_route === 'pemasukan bersih') {
                        $data = $this->calculatePemasukanBersihDetail($itemDetail);
                    } elseif ($itemDetail->asistant_route === 'rasio biaya operasional terhadap revenue') {
                        $data = $this->calculateRasioBiayaOperasionalTerhadapRevenueDetail($itemDetail);
                    }
                    // CS
                    elseif ($itemDetail->asistant_route === 'peserta puas dengan pelayanan dan fasilitas training') {
                        $data = $this->calculatePesertaPuasDenganPelayananDanFasilitasTrainingDetail($itemDetail);
                    } elseif ($itemDetail->asistant_route === 'dorong inovasi pelayanan') {
                        $data = $this->calculateDorongInovasiPelayananDetail($itemDetail);
                    } elseif ($itemDetail->asistant_route === 'report persiapan kelas') {
                        $data = $this->calculateReportPersiapanKelasDetail($itemDetail, $personId);
                    }
                    // Finance
                    elseif ($itemDetail->asistant_route === 'outstanding') {
                        $data = $this->calculateOutstandingDetail($itemDetail);
                    } elseif ($itemDetail->asistant_route === 'inisiatif efisiensi keuangan') {
                        $data = $this->calculateInisiatifEfisiensiKeuanganDetail($itemDetail);
                    }
                    // ITSM - all kecuali Koordinator ITSM
                    elseif ($itemDetail->asistant_route === 'kepuasan client ITSM') {
                        $data = $this->calculateProgressKepuasanClientITSMDetail($itemDetail);
                    }
                    // Koordinator ITSM
                    elseif ($itemDetail->asistant_route === 'availability sistem internal kritis') {
                        $data = $this->calculateAvailabilitySistemInternalKritisDetail($itemDetail);
                    } elseif ($itemDetail->asistant_route === 'meningkatkan kepuasan dan loyalitas peserta/client') {
                        $data = $this->calculateMeningkatkanKepuasanDanLoyalitasPesertaDetail($itemDetail);
                    }
                    // Programmer
                    elseif ($itemDetail->asistant_route === 'ketepatan waktu penyelesaian fitur') {
                        $data = $this->calculateProgressKetepatanWaktuPenyelesaianFiturDetail($itemDetail, $personId);
                    } elseif ($itemDetail->asistant_route === 'mengukur kualitas aplikasi agar minim bug') {
                        $data = $this->calculateMengukurKualitasAplikasiAgarMinimBugDetail($itemDetail, $personId);
                    }
                    // Tim Digital
                    elseif ($itemDetail->asistant_route === 'konsistensi campaign digital') {
                        $data = $this->calculateKonsistensiCampaignDigitalDetail($itemDetail);
                    }
                    // TS
                    elseif ($itemDetail->asistant_route === 'keberhasilan support memenuhi sla') {
                        $data = $this->calculateTingkatKeberhasilanSupportMemenuhiSLADetail($itemDetail, $personId);
                    }

                    $dataOutput = [
                        'pembuat' => $itemDetail->karyawan->nama_lengkap,
                        'judul' => $itemDetail->judul,
                        'condition' => $itemDetail->asistant_route,
                        'deskripsi' => $itemDetail->deskripsi,
                        'jabatan_kpi' => $detail->jabatan,
                        'divisi_kpi' => $detail->divisi,
                        'karyawan' => $itemDetail->detailTargetKPI
                            ->flatMap(function ($detail) {
                                return $detail->detailPersonKPI->map(function ($person) {
                                    return [
                                        'nama_lengkap' => $person->karyawan->nama_lengkap ?? null,
                                        'jabatan' => $person->karyawan->jabatan ?? null,
                                    ];
                                });
                            })
                            ->values(),
                        'jangka_target' => $detail->jangka_target,
                        'detail_jangka' => $detail->detail_jangka,
                        'tipe_target' => $detail->tipe_target,
                        'nilai_target' => $detail->nilai_target,
                        'tenggat_waktu' => $tenggat_waktu,
                        'data_detail' => $data,
                    ];

                    return [
                        'data' => $dataOutput,
                    ];
                })
                ->filter()
                ->values(),
        ];

        return response()->json($data);
    }

    //Target Detail Office
    //gm
    private function calculateProgressKepuasanPelangganDetail($itemDetail)
    {
        $detailJangkas = $itemDetail->detailTargetKPI->pluck('detail_jangka')->filter();

        if ($detailJangkas->isEmpty()) {
            Log::warning("detail_jangka tidak ditemukan untuk target ID: {$itemDetail->id}");
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $tahun = (int) $detailJangkas->first();

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk target ID: {$itemDetail->id}");
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $start = "$tahun-01-01";
        $end = "$tahun-12-31";

        $feedbacks = Nilaifeedback::with('rkm.materi')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        if ($feedbacks->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $groupedFeedbacks = $feedbacks
            ->groupBy(function ($feedback) {
                $materiNama = optional($feedback->rkm)->materi?->nama_materi ?? 'unknown';
                $tanggalAwal = optional($feedback->rkm)->tanggal_awal ?? '0000-00-00';
                return $materiNama . '/' . $tanggalAwal;
            })
            ->filter();

        $averageFeedbacks = [];
        $dailyAverages = [];

        foreach ($groupedFeedbacks as $group) {
            $totalFeedbacks = $group->count();
            if ($totalFeedbacks === 0) {
                continue;
            }

            $sums = [
                'M' => $group->sum('M1') + $group->sum('M2') + $group->sum('M3') + $group->sum('M4'),
                'P' => $group->sum('P1') + $group->sum('P2') + $group->sum('P3') + $group->sum('P4') + $group->sum('P5') + $group->sum('P6') + $group->sum('P7'),
                'F' => $group->sum('F1') + $group->sum('F2') + $group->sum('F3') + $group->sum('F4') + $group->sum('F5'),
                'I' => $group->sum('I1') + $group->sum('I2') + $group->sum('I3') + $group->sum('I4') + $group->sum('I5') + $group->sum('I6') + $group->sum('I7') + $group->sum('I8'),
                'Ib' => $group->sum('I1b') + $group->sum('I2b') + $group->sum('I3b') + $group->sum('I4b') + $group->sum('I5b') + $group->sum('I6b') + $group->sum('I7b') + $group->sum('I8b'),
                'Ias' => $group->sum('I1as') + $group->sum('I2as') + $group->sum('I3as') + $group->sum('I4as') + $group->sum('I5as') + $group->sum('I6as') + $group->sum('I7as') + $group->sum('I8as'),
            ];

            $avgM = round($sums['M'] / ($totalFeedbacks * 4), 1);
            $avgP = round($sums['P'] / ($totalFeedbacks * 7), 1);
            $avgF = round($sums['F'] / ($totalFeedbacks * 5), 1);
            $avgI = round($sums['I'] / ($totalFeedbacks * 8), 1);
            $avgIb = round($sums['Ib'] / ($totalFeedbacks * 8), 1);
            $avgIas = round($sums['Ias'] / ($totalFeedbacks * 8), 1);

            $values = [$avgM, $avgP, $avgF, $avgI];
            if ($avgIb > 0) {
                $values[] = $avgIb;
            }
            if ($avgIas > 0) {
                $values[] = $avgIas;
            }

            $finalAvg = round(array_sum($values) / count($values), 1);
            $averageFeedbacks[] = $finalAvg;

            $sampleDate = $group->first()->created_at->format('Y-m-d');
            $dailyAverages[$sampleDate] = $finalAvg;
        }

        $totalGroups = count($averageFeedbacks);
        $above = count(array_filter($averageFeedbacks, fn($v) => $v >= 3.5));
        $below = $totalGroups - $above;
        $progress = $totalGroups > 0 ? round(($above / $totalGroups) * 100, 1) : 0;

        $nilaiTarget = $itemDetail->detailTargetKPI->pluck('nilai_target')->first();

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $avg;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;
        }

        $monthlyAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    private function calculatePemasukanKotorDetail($itemDetail)
    {
        $details = $itemDetail->detailTargetKPI;

        $tahun = (int) optional($details->first())->detail_jangka;
        $nilaiTarget = (float) optional($details->first())->nilai_target;

        if ($details->isEmpty() || $nilaiTarget <= 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $sales = RKM::where('status', '0')->whereYear('tanggal_awal', $tahun)->select(DB::raw('tanggal_awal, SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total'))->groupBy('tanggal_awal')->get();

        $progress = 0;
        $dailyBreakdownPerMonth = [];
        $monthlyDataTemp = [];

        foreach ($sales as $row) {
            $date = Carbon::parse($row->tanggal_awal);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');

            $total = (float) $row->total;
            $progress += $total;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dateKey] = $total;

            if (!isset($monthlyDataTemp[$monthKey])) {
                $monthlyDataTemp[$monthKey] = [];
            }
            $monthlyDataTemp[$monthKey][] = $total;
        }

        $monthlyData = [];
        foreach ($monthlyDataTemp as $month => $totals) {
            $monthlyData[$month] = round(array_sum($totals) / count($totals), 1);
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    private function calculatePemasukanBersihDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $item = $itemDetail;
        $personId = 0;

        $labaKotor = $this->calculatePemasukanKotor($item, $personId);

        if ($labaKotor == 0) {
            return 0;
        }

        if (is_null($detail) || is_null($detail->manual_value)) {
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $progress = 0;

        if (!is_null($detail->manual_value)) {
            $manualValue = (float) $detail->manual_value;

            if ($labaKotor < $manualValue) {
                return 0;
            }

            if ($manualValue > 0) {
                $progress = ($manualValue / $labaKotor) * 100;
                $progress = min(100, $progress);
            }
        }

        $progress = round($progress, 1);
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
        ];
    }

    private function calculateRasioBiayaOperasionalTerhadapRevenueDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $item = $itemDetail;
        $personId = 0;

        $labaKotor = $this->calculatePemasukanKotor($item, $personId);

        if ($labaKotor == 0) {
            return 0;
        }

        if (is_null($detail) || is_null($detail->manual_value)) {
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $progress = 0;

        if (!is_null($detail->manual_value)) {
            $manualValue = (float) $detail->manual_value;

            if ($labaKotor < $manualValue) {
                return 0;
            }

            if ($manualValue > 0) {
                $progress = ($manualValue / $labaKotor) * 100;
                $progress = min(100, $progress);
            }
        }

        $progress = round($progress, 1);
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
        ];
    }

    //cs
    private function calculatePesertaPuasDenganPelayananDanFasilitasTrainingDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = [];
        $scoreDatePairs = [];

        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();
        foreach ($feedbacks as $fb) {
            $f1 = is_numeric($fb->F1) ? (float) $fb->F1 : 0;
            $f2 = is_numeric($fb->F2) ? (float) $fb->F2 : 0;
            $f3 = is_numeric($fb->F3) ? (float) $fb->F3 : 0;
            $f4 = is_numeric($fb->F4) ? (float) $fb->F4 : 0;
            $f5 = is_numeric($fb->F5) ? (float) $fb->F5 : 0;
            $p1 = is_numeric($fb->P1) ? (float) $fb->P1 : 0;
            $p2 = is_numeric($fb->P2) ? (float) $fb->P2 : 0;
            $p3 = is_numeric($fb->P3) ? (float) $fb->P3 : 0;
            $p4 = is_numeric($fb->P4) ? (float) $fb->P4 : 0;
            $p5 = is_numeric($fb->P5) ? (float) $fb->P5 : 0;
            $p6 = is_numeric($fb->P6) ? (float) $fb->P6 : 0;
            $p7 = is_numeric($fb->P7) ? (float) $fb->P7 : 0;

            $avg = ($f1 + $f2 + $f3 + $f4 + $f5 + $p1 + $p2 + $p3 + $p4 + $p5 + $p6 + $p7) / 12;
            $avg = min(4, max(1, $avg));

            $allScores[] = $avg;
            $scoreDatePairs[] = [
                'score' => $avg,
                'date' => $fb->created_at->format('Y-m-d'),
            ];
        }

        if (empty($allScores)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        foreach ($scoreDatePairs as $pair) {
            $date = Carbon::parse($pair['date']);
            $monthKey = $date->format('Y-m');
            $dayKey = $pair['date'];
            $score = $pair['score'];

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $score;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $score;
        }

        $monthlyAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $respondenPuas,
                'below' => $totalResponden - $respondenPuas,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    private function calculateDorongInovasiPelayananDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $progress = 0;

        if (!is_null($detail->manual_value)) {
            $manualValue = (float) $detail->manual_value;

            if ($manualValue > 0) {
                $progress = ($manualValue / $nilaiTarget) * 100;
                $progress = min(100, $progress);
            }
        }

        $progress = round($progress, 1);
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
        ];
    }

    private function calculateReportPersiapanKelasDetail($itemdetail, $personId)
    {
        $details = $itemdetail->detailTargetKPI;
        $detail = $details->first();

        // Mengambil nilai target dan tahun dengan safe access seperti function target
        $nilaiTarget = (float) optional($detail)->nilai_target;
        $tahun = (int) optional($detail)->detail_jangka ?? now()->year;

        // Validasi awal sesuai struktur function target
        if ($details->isEmpty() || $nilaiTarget <= 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        // Alur Bisnis: Menghitung Total RKM (Denominator)
        $totalRkm = RKM::whereYear('tanggal_awal', $tahun)->count();

        // Alur Bisnis: Mengambil Detail Checklist Keperluan (Numerator)
        $checklistItems = ChecklistKeperluan::whereYear('created_at', $tahun)
            ->where('materi', 1)
            ->where('kelas', 1)
            ->where('cb', 1)
            ->where('maksi', 1)
            ->where('keperluan_kelas', 1)
            ->select('created_at')
            ->get();

        $totalTuntas = $checklistItems->count();

        // Alur Bisnis: Kalkulasi Progress Utama (Persentase)
        if ($totalRkm > 0) {
            $progress = ($totalTuntas / $totalRkm) * 100;
        } else {
            $progress = 0;
        }

        // Struktur Data: Membentuk breakdown monthly dan daily
        $dailyBreakdownPerMonth = [];
        $monthlyTotals = []; // Ubah nama variabel agar lebih jelas: menyimpan total per bulan

        foreach ($checklistItems as $row) {
            $date = Carbon::parse($row->created_at);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');

            $value = 1; // Setiap checklist bernilai 1

            // Inisialisasi array bulanan jika belum ada
            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }

            // Akumulasi nilai per tanggal
            if (!isset($dailyBreakdownPerMonth[$monthKey][$dateKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dateKey] = 0;
            }
            $dailyBreakdownPerMonth[$monthKey][$dateKey] += $value;

            // Akumulasi total per bulan (untuk monthly_data)
            if (!isset($monthlyTotals[$monthKey])) {
                $monthlyTotals[$monthKey] = 0;
            }
            $monthlyTotals[$monthKey] += $value;
        }

        // Struktur Data: monthly_data = total tuntas per bulan (bukan rata-rata)
        $monthlyData = $monthlyTotals;

        // Urutkan berdasarkan key (bulan) agar chart tampil kronologis
        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        // Struktur Data: Menghitung Gap (Progress - Target)
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        // Struktur Data: Pie Chart
        // Above = total yang sudah tuntas, Below = sisa target yang belum tuntas
        $pieChart = [
            'above' => $totalTuntas,
            'below' => max(0, $totalRkm - $totalTuntas) // Pastikan tidak negatif
        ];

        return [
            'progress' => round($progress, 1),
            'gap' => $gap,
            'pie_chart' => $pieChart,
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    //finance
    private function calculateOutstandingDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->nilai_target) || !is_numeric($detail->detail_jangka)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $outstandings = Outstanding::whereBetween('created_at', [$start, $end])->get();

        if ($outstandings->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $totalData = $outstandings->count();

        $tepatTenggat = $outstandings->where('due_date', '<', 'tanggal_bayar')->where('status_pembayaran', '1');

        $above = $tepatTenggat->count();
        $below = $totalData - $above;

        $progress = $totalData > 0 ? ($above / $totalData) * 100 : 0;

        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;

        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        foreach ($outstandings as $data) {
            $date = Carbon::parse($data->created_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $isTepat = $data->status_pembayaran == '1' && $data->tanggal_bayar && $data->due_date && Carbon::parse($data->tanggal_bayar)->lt(Carbon::parse($data->due_date)) ? 1 : 0;

            $monthlyData[$monthKey][] = $isTepat * 100;

            $dailyBreakdownPerMonth[$monthKey][$dayKey][] = $isTepat * 100;
        }

        $monthlyAverages = [];
        foreach ($monthlyData as $month => $values) {
            $monthlyAverages[$month] = round(array_sum($values) / count($values), 1);
        }

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            foreach ($days as $day => $values) {
                $dailyBreakdownPerMonth[$month][$day] = round(array_sum($values) / count($values), 1);
            }
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $above,
                'below' => $below,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    private function calculateInisiatifEfisiensiKeuanganDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $item = $itemDetail;
        $personId = 0;

        if (is_null($detail) || is_null($detail->manual_value)) {
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $manualValue = (float) $detail->manual_value;

        if ($manualValue == null) {
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $progress = 0;

        if (!is_null($detail->manual_value)) {
            $manualValue = (float) $detail->manual_value;

            if ($manualValue > 0) {
                $progress = ($manualValue / $nilaiTarget) * 100;
                $progress = min(100, $progress);
            }
        }

        $progress = round($progress, 1);
        $gapRaw = $progress - $nilaiTarget;
        $gap = $nilaiTarget - $manualValue;

        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => [
                'manual_document' => $detail->manual_document,
            ],
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
        ];
    }

    //Target Detail itsm
    //Koordinator ITSM
    private function calculateMeningkatkanKepuasanDanLoyalitasPesertaDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = []; // Hanya skor
        $scoreDatePairs = []; // Simpan [skor, tanggal] untuk breakdown

        // === SurveyKepuasan ===
        $dataSurvey = SurveyKepuasan::whereBetween('created_at', [$start, $end])->get();
        foreach ($dataSurvey as $survey) {
            $nilaiQ1 = match ($survey->q1) {
                1 => 10,
                2 => 20,
                3 => 30,
                4 => 40,
                default => 0,
            };

            $nilaiQ4 = match ($survey->q4) {
                1 => 10,
                2 => 20,
                3 => 30,
                4 => 40,
                default => 0,
            };

            $nilaiQ2 = match ($survey->q2) {
                'Ya' => 20,
                'Tidak' => 10,
                default => 0,
            };

            $totalBaris = min(100, max(0, $nilaiQ1 + $nilaiQ2 + $nilaiQ4));
            $skor = 1 + ($totalBaris * 3) / 100;

            $allScores[] = $skor;
            $scoreDatePairs[] = [
                'score' => $skor,
                'date' => $survey->created_at->format('Y-m-d'),
            ];
        }

        // === Nilaifeedback ===
        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();
        foreach ($feedbacks as $fb) {
            $f1 = is_numeric($fb->F1) ? (float) $fb->F1 : 0;
            $f2 = is_numeric($fb->F2) ? (float) $fb->F2 : 0;
            $f3 = is_numeric($fb->F3) ? (float) $fb->F3 : 0;
            $f4 = is_numeric($fb->F4) ? (float) $fb->F4 : 0;
            $f5 = is_numeric($fb->F5) ? (float) $fb->F5 : 0;

            $avg = ($f1 + $f2 + $f3 + $f4 + $f5) / 5;
            $avg = min(4, max(1, $avg));

            $allScores[] = $avg;
            $scoreDatePairs[] = [
                'score' => $avg,
                'date' => $fb->created_at->format('Y-m-d'),
            ];
        }

        if (empty($allScores)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        foreach ($scoreDatePairs as $pair) {
            $date = Carbon::parse($pair['date']);
            $monthKey = $date->format('Y-m');
            $dayKey = $pair['date'];
            $score = $pair['score'];

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $score;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $score;
        }

        $monthlyAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $respondenPuas,
                'below' => $totalResponden - $respondenPuas,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    private function calculateAvailabilitySistemInternalKritisDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = []; // Hanya skor
        $scoreDatePairs = []; // Simpan [skor, tanggal] untuk breakdown

        // === SurveyKepuasan ===
        $dataSurvey = SurveyKepuasan::whereBetween('created_at', [$start, $end])->get();
        foreach ($dataSurvey as $survey) {
            $nilaiQ1 = match ($survey->q1) {
                1 => 10,
                2 => 20,
                3 => 30,
                4 => 40,
                default => 0,
            };

            $nilaiQ4 = match ($survey->q4) {
                1 => 10,
                2 => 20,
                3 => 30,
                4 => 40,
                default => 0,
            };

            $nilaiQ2 = match ($survey->q2) {
                'Ya' => 20,
                'Tidak' => 10,
                default => 0,
            };

            $totalBaris = min(100, max(0, $nilaiQ1 + $nilaiQ2 + $nilaiQ4));
            $skor = 1 + ($totalBaris * 3) / 100;

            $allScores[] = $skor;
            $scoreDatePairs[] = [
                'score' => $skor,
                'date' => $survey->created_at->format('Y-m-d'),
            ];
        }

        // === Nilaifeedback ===
        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();
        foreach ($feedbacks as $fb) {
            $f1 = is_numeric($fb->F1) ? (float) $fb->F1 : 0;
            $f2 = is_numeric($fb->F2) ? (float) $fb->F2 : 0;
            $f3 = is_numeric($fb->F3) ? (float) $fb->F3 : 0;
            $f4 = is_numeric($fb->F4) ? (float) $fb->F4 : 0;
            $f5 = is_numeric($fb->F5) ? (float) $fb->F5 : 0;

            $avg = ($f1 + $f2 + $f3 + $f4 + $f5) / 5;
            $avg = min(4, max(1, $avg));

            $allScores[] = $avg;
            $scoreDatePairs[] = [
                'score' => $avg,
                'date' => $fb->created_at->format('Y-m-d'),
            ];
        }

        if (empty($allScores)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        // === Breakdown berdasarkan scoreDatePairs ===
        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        foreach ($scoreDatePairs as $pair) {
            $date = Carbon::parse($pair['date']);
            $monthKey = $date->format('Y-m');
            $dayKey = $pair['date'];
            $score = $pair['score'];

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $score;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $score;
        }

        $monthlyAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $respondenPuas,
                'below' => $totalResponden - $respondenPuas,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    //Tim Digital
    private function calculateKonsistensiCampaignDigitalDetail($itemDetail)
    {
        $details = $itemDetail->detailTargetKPI;

        if ($details->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $tahun = (int) $details->first()->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 1) {
            $tahun = now()->year;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $contentSchedules = ContentSchedule::whereBetween('created_at', [$start, $end])
            ->whereNotNull('upload_date')
            ->get();

        if ($contentSchedules->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        // Hitung jumlah konten per minggu
        $weeklyCounts = [];
        foreach ($contentSchedules as $schedule) {
            $weekKey = Carbon::parse($schedule->created_at)->format('o-\WW');
            $weeklyCounts[$weekKey] = ($weeklyCounts[$weekKey] ?? 0) + 1;
        }

        $compliantWeeks = 0;
        $totalWeeksWithData = 0;

        foreach ($weeklyCounts as $count) {
            if ($count >= 1) {
                $totalWeeksWithData++;
                if ($count >= 3) {
                    $compliantWeeks++;
                }
            }
        }

        if ($totalWeeksWithData === 0) {
            $progress = 0;
        } else {
            $progress = round(($compliantWeeks / $totalWeeksWithData) * 100, 1);
        }

        $nilaiTarget = $details->pluck('nilai_target')->first() ?? 0;
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $compliantWeeks;
        $below = $totalWeeksWithData - $compliantWeeks;

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        foreach ($contentSchedules as $schedule) {
            $date = Carbon::parse($schedule->created_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = 1;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = ($dailyBreakdownPerMonth[$monthKey][$dayKey] ?? 0) + 1;
        }

        $monthlyAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    //Programmer
    private function calculateMengukurKualitasAplikasiAgarMinimBugDetail($itemDetail, $personId = null)
    {
        $details = $itemDetail->detailTargetKPI;

        if ($details->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $firstDetail = $details->first();
        $nilaiTarget = (float) $firstDetail->nilai_target;
        $tahun = (int) $firstDetail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $picNames = [];

        if ($personId !== null) {
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
                ->where('id_karyawan', $personId)
                ->pluck('id_karyawan')
                ->unique()
                ->toArray();

            if (!empty($idKaryawans)) {
                $namaLengkapList = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->toArray();

                $picNames = array_map(function ($nama) {
                    return explode(' ', trim($nama))[0] ?? '';
                }, $namaLengkapList);
            }
        } else {
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
                ->pluck('id_karyawan')
                ->unique()
                ->toArray();

            if (!empty($idKaryawans)) {
                $namaLengkapList = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->toArray();

                // Ekstrak nama depan (bisa jadi array berisi banyak nama)
                $picNames = array_map(function ($nama) {
                    return explode(' ', trim($nama))[0] ?? '';
                }, $namaLengkapList);
            }
        }

        // Filter nama yang kosong
        $picNames = array_filter($picNames);

        // Jika tidak ada nama PIC yang ditemukan, return kosong
        if (empty($picNames)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        // ✅ QUERY TIKET (MENGGUNAKAN whereIn UNTUK MENDUKUNG BANYAK PIC)
        if ($personId !== null) {
            // Jika spesifik 1 orang, bisa pakai where (tapi whereIn juga aman)
            $ticketsError = Tickets::whereBetween('created_at', [$start, $end])
                ->where('kategori', 'Error (Aplikasi)')
                ->where('keperluan', 'Programming')
                ->whereIn('pic', $picNames) // Tetap pakai whereIn untuk konsistensi
                ->whereNotNull('tanggal_selesai')
                ->get();

            $ticketsRequest = Tickets::whereBetween('created_at', [$start, $end])
                ->where('kategori', 'Request')
                ->whereIn('pic', $picNames)
                ->get();
        } else {
            // Jika null, ambil semua tiket dari daftar PIC yang ditemukan di detailPersonKPI
            $ticketsError = Tickets::whereBetween('created_at', [$start, $end])
                ->where('kategori', 'Error (Aplikasi)')
                ->where('keperluan', 'Programming')
                ->whereIn('pic', $picNames) // ✅ PENTING: Filter berdasarkan array nama dari detailPerson
                ->whereNotNull('tanggal_selesai')
                ->get();

            $ticketsRequest = Tickets::whereBetween('created_at', [$start, $end])
                ->where('kategori', 'Request')
                ->whereIn('pic', $picNames) // ✅ PENTING: Filter berdasarkan array nama dari detailPerson
                ->get();
        }

        $jumlahError = $ticketsError->count();
        $jumlahRequest = $ticketsRequest->count();
        $totalTicket = $jumlahRequest + $jumlahError;

        if ($totalTicket === 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $skorRasio = ($jumlahRequest / $totalTicket) * 100;

        if ($jumlahError === 0) {
            $rataSkorError = 100;
            $above = 0;
            $below = 0;
            $monthlyAverages = [];
            $dailyBreakdownPerMonth = [];
        } else {
            $totalSkorError = 0;
            $ticketScores = [];

            foreach ($ticketsError as $ticket) {
                $startAt = Carbon::parse($ticket->created_at, 'Asia/Jakarta');
                $endAt = Carbon::parse($ticket->tanggal_selesai . ' ' . $ticket->jam_selesai, 'Asia/Jakarta');
                $durasiJam = $startAt->diffInHours($endAt);

                $skorDurasi = match (true) {
                    $durasiJam <= 4 => 100,
                    $durasiJam <= 8 => 80,
                    $durasiJam <= 24 => 60,
                    default => 30,
                };

                $bobot = match ($ticket->tingkat_kesulitan) {
                    'Major' => 1.5,
                    'Moderate' => 1.2,
                    default => 1.0,
                };

                $skorError = min(100, $skorDurasi * $bobot);
                $totalSkorError += $skorError;

                $dateKey = $endAt->format('Y-m-d');
                $ticketScores[$dateKey] = $skorError;
            }

            $rataSkorError = $totalSkorError / $jumlahError;

            $above = 0;
            foreach ($ticketScores as $score) {
                if ($score >= 70) {
                    $above++;
                }
            }
            $below = $jumlahError - $above;

            $monthlyData = [];
            $dailyBreakdownPerMonth = [];

            foreach ($ticketScores as $dateStr => $score) {
                $date = Carbon::parse($dateStr);
                $monthKey = $date->format('Y-m');
                $dayKey = $dateStr;

                if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                    $dailyBreakdownPerMonth[$monthKey] = [];
                }
                $dailyBreakdownPerMonth[$monthKey][$dayKey] = round($score, 1);

                if (!isset($monthlyData[$monthKey])) {
                    $monthlyData[$monthKey] = [];
                }
                $monthlyData[$monthKey][] = $score;
            }

            $monthlyAverages = [];
            foreach ($monthlyData as $month => $scores) {
                $monthlyAverages[$month] = round(array_sum($scores) / count($scores), 1);
            }

            ksort($monthlyAverages);
            ksort($dailyBreakdownPerMonth);
        }

        $skorKualitas = $skorRasio * 0.5 + $rataSkorError * 0.5;
        $progress = ($skorKualitas / $nilaiTarget) * 100;
        $progress = round($progress, 1);
        $progress = min($progress, 100);

        $gapRaw = $progress - $nilaiTarget;
        $gap = $gapRaw < 0 ? abs($gapRaw) : 0;
        $gap = rtrim(rtrim(sprintf('%.1f', $gap), '0'), '.');

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages ?? [],
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth ?? [],
        ];
    }

    private function calculateProgressKetepatanWaktuPenyelesaianFiturDetail($itemDetail, $personId = null)
    {
        $details = $itemDetail->detailTargetKPI;

        if ($details->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $firstDetail = $details->first();
        $nilaiTarget = (float) $firstDetail->nilai_target;
        $tahun = (int) $firstDetail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        // ✅ LOGIKA PENGAMBILAN PIC NAME (SELALU DARI detailPersonKPI)
        $picNames = [];

        if ($personId !== null) {
            // 1. Jika ada personId: Ambil hanya karyawan tersebut dari detailPersonKPI
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
                ->where('id_karyawan', $personId)
                ->pluck('id_karyawan')
                ->unique()
                ->toArray();

            if (!empty($idKaryawans)) {
                $namaLengkapList = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->toArray();

                // Ekstrak nama depan
                $picNames = array_map(function ($nama) {
                    return explode(' ', trim($nama))[0] ?? '';
                }, $namaLengkapList);
            }
        } else {
            // 2. Jika personId NULL: Ambil SEMUA karyawan dari detailPersonKPI (BUKAN dari jabatan)
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
                ->pluck('id_karyawan')
                ->unique()
                ->toArray();

            if (!empty($idKaryawans)) {
                $namaLengkapList = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->toArray();

                // Ekstrak nama depan (bisa jadi array berisi banyak nama)
                $picNames = array_map(function ($nama) {
                    return explode(' ', trim($nama))[0] ?? '';
                }, $namaLengkapList);
            }
        }

        // Filter nama yang kosong
        $picNames = array_filter($picNames);

        // Jika tidak ada nama PIC yang ditemukan dari detailPersonKPI, return kosong
        if (empty($picNames)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        // ✅ AMBIL TARGET JABATAN UNTUK FILTER KEPERLUAN
        $targetJabatanList = $details->pluck('jabatan')->unique()->toArray();
        if (empty($targetJabatanList)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $picJabatan = karyawan::whereIn('jabatan', $targetJabatanList)
            ->pluck('jabatan')
            ->unique()
            ->map(fn($n) => ucwords(strtolower($n)))
            ->toArray();

        $jabatanFilter = array_map(function ($jabatan) {
            return match (strtolower($jabatan)) {
                'programmer', 'koordinator itsm' => 'Programming',
                'technical support' => 'Technical Support',
                'tim digital' => 'Tim Digital',
                default => $jabatan,
            };
        }, $picJabatan);

        $jabatanFilter = array_unique(array_filter($jabatanFilter));

        if (empty($jabatanFilter)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        // ✅ QUERY TIKET (MENGGUNAKAN whereIn UNTUK MENDUKUNG BANYAK PIC)
        $tickets = Tickets::whereIn('keperluan', $jabatanFilter)
            ->whereBetween('created_at', [$start, $end])
            ->whereIn('pic', $picNames) // ✅ PENTING: Filter berdasarkan PIC dari detailPersonKPI
            ->whereNotNull('tanggal_selesai')
            ->get();

        if ($tickets->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $metCount = 0;
        $total = $tickets->count();
        $slaResults = [];

        foreach ($tickets as $ticket) {
            $priority = 'Other';

            if (in_array($ticket->tingkat_kesulitan, ['Major', 'Moderate'])) {
                $priority = 'High';
            } elseif ($ticket->tingkat_kesulitan === 'Minor' && $ticket->kategori === 'Error (Aplikasi)') {
                $priority = 'Medium';
            } elseif ($ticket->kategori === 'Request') {
                $priority = 'Low';
            }

            $startAt = Carbon::parse($ticket->created_at, 'Asia/Jakarta');
            $endAt = Carbon::parse($ticket->tanggal_selesai . ' ' . $ticket->jam_selesai, 'Asia/Jakarta');
            $actualHours = $startAt->diffInHours($endAt);

            $slaMet = false;
            if ($priority === 'High' && $actualHours <= 24) {
                $slaMet = true;
            } elseif ($priority === 'Medium' && $actualHours <= 40) {
                $slaMet = true;
            } elseif ($priority === 'Low') {
                $slaMet = true;
            }

            if ($slaMet) {
                $metCount++;
            }

            $dateKey = $endAt->format('Y-m-d');
            $slaResults[$dateKey] = $slaMet;
        }

        $realisasiPersen = ($metCount / $total) * 100;
        $progress = ($realisasiPersen / $nilaiTarget) * 100;
        $progress = round($progress, 1);
        $progress = min($progress, 100);

        $gapRaw = $progress - 100;
        $gap = $gapRaw < 0 ? abs($gapRaw) : 0;
        $gap = rtrim(rtrim(sprintf('%.1f', $gap), '0'), '.');

        $pieChart = [
            'above' => $metCount,
            'below' => $total - $metCount,
        ];

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        foreach ($slaResults as $dateStr => $met) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $dateStr;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $met ? 1 : 0;

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $met ? 1 : 0;
        }

        $monthlyAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $avg = (array_sum($dailyVals) / count($dailyVals)) * 100;
            $monthlyAverages[$month] = round($avg, 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => $pieChart,
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    //all kecuali Koordinator ITSM
    private function calculateProgressKepuasanClientITSMDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka) || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $allScores = []; // Hanya skor
        $scoreDatePairs = []; // Simpan [skor, tanggal] untuk breakdown

        // === SurveyKepuasan ===
        $dataSurvey = SurveyKepuasan::whereBetween('created_at', [$start, $end])->get();
        foreach ($dataSurvey as $survey) {
            $nilaiQ1 = match ($survey->q1) {
                1 => 10,
                2 => 20,
                3 => 30,
                4 => 40,
                default => 0,
            };

            $nilaiQ4 = match ($survey->q4) {
                1 => 10,
                2 => 20,
                3 => 30,
                4 => 40,
                default => 0,
            };

            $nilaiQ2 = match ($survey->q2) {
                'Ya' => 20,
                'Tidak' => 10,
                default => 0,
            };

            $totalBaris = min(100, max(0, $nilaiQ1 + $nilaiQ2 + $nilaiQ4));
            $skor = 1 + ($totalBaris * 3) / 100;

            $allScores[] = $skor;
            $scoreDatePairs[] = [
                'score' => $skor,
                'date' => $survey->created_at->format('Y-m-d'),
            ];
        }

        // === Nilaifeedback ===
        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();
        foreach ($feedbacks as $fb) {
            $f1 = is_numeric($fb->F1) ? (float) $fb->F1 : 0;
            $f2 = is_numeric($fb->F2) ? (float) $fb->F2 : 0;
            $f3 = is_numeric($fb->F3) ? (float) $fb->F3 : 0;
            $f4 = is_numeric($fb->F4) ? (float) $fb->F4 : 0;
            $f5 = is_numeric($fb->F5) ? (float) $fb->F5 : 0;

            $avg = ($f1 + $f2 + $f3 + $f4 + $f5) / 5;
            $avg = min(4, max(1, $avg));

            $allScores[] = $avg;
            $scoreDatePairs[] = [
                'score' => $avg,
                'date' => $fb->created_at->format('Y-m-d'),
            ];
        }

        if (empty($allScores)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.5) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        // === Breakdown berdasarkan scoreDatePairs ===
        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        foreach ($scoreDatePairs as $pair) {
            $date = Carbon::parse($pair['date']);
            $monthKey = $date->format('Y-m');
            $dayKey = $pair['date'];
            $score = $pair['score'];

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $score;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $score;
        }

        $monthlyAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $respondenPuas,
                'below' => $totalResponden - $respondenPuas,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    //TS
    private function calculateTingkatKeberhasilanSupportMemenuhiSLADetail($itemDetail, $personId = null)
    {
        $details = $itemDetail->detailTargetKPI;

        if ($details->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $firstDetail = $details->first();
        $nilaiTarget = (float) $firstDetail->nilai_target;
        $tahun = (int) $firstDetail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $picNames = [];

        if ($personId !== null) {
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
                ->where('id_karyawan', $personId)
                ->pluck('id_karyawan')
                ->unique()
                ->toArray();

            if (!empty($idKaryawans)) {
                $namaLengkapList = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->toArray();

                $picNames = array_map(function ($nama) {
                    return explode(' ', trim($nama))[0] ?? '';
                }, $namaLengkapList);
            }
        } else {
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)
                ->pluck('id_karyawan')
                ->unique()
                ->toArray();

            if (!empty($idKaryawans)) {
                $namaLengkapList = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->toArray();

                $picNames = array_map(function ($nama) {
                    return explode(' ', trim($nama))[0] ?? '';
                }, $namaLengkapList);
            }
        }

        $picNames = array_filter($picNames);

        if (empty($picNames)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $targetJabatanList = $details->pluck('jabatan')->unique()->toArray();
        if (empty($targetJabatanList)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $keperluanPatterns = [];
        foreach ($targetJabatanList as $jabatan) {
            $jabatanLower = strtolower($jabatan);
            if (str_contains($jabatanLower, 'programmer')) {
                $keperluanPatterns[] = 'Programming';
            } elseif (str_contains($jabatanLower, 'technical support') || str_contains($jabatanLower, 'tech support')) {
                $keperluanPatterns[] = 'Technical Support';
            }
        }

        $keperluanPatterns = array_unique($keperluanPatterns);

        if (empty($keperluanPatterns)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $rawTickets = DB::table('tickets')
            ->select('created_at', 'tanggal_response', 'jam_response', 'tanggal_selesai', 'jam_selesai')
            ->whereIn('keperluan', $keperluanPatterns)
            ->whereIn('pic', $picNames)
            ->whereNotNull('tanggal_selesai')
            ->whereBetween('created_at', [$start, $end])
            ->get();

        if ($rawTickets->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $totalTickets = 0;
        $resolutionMet = 0;
        $dailyResults = [];

        foreach ($rawTickets as $ticket) {
            try {
                $createdAt = Carbon::parse($ticket->created_at);

                $responseAt = null;
                if (!empty($ticket->tanggal_response) && !empty($ticket->jam_response)) {
                    $responseAt = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->tanggal_response . ' ' . $ticket->jam_response);
                }

                $resolvedAt = null;
                if (!empty($ticket->tanggal_selesai) && !empty($ticket->jam_selesai)) {
                    $resolvedAt = Carbon::createFromFormat('Y-m-d H:i:s', $ticket->tanggal_selesai . ' ' . $ticket->jam_selesai);
                }

                if (!$resolvedAt || !$createdAt) {
                    continue;
                }

                $totalTickets++;

                $startResolution = $responseAt ?? $createdAt;

                $current = clone $startResolution;
                $endCalc = clone $resolvedAt;
                $totalHours = 0;

                $workStartHour = 8;
                $workEndHour = 17;

                while ($current->lt($endCalc)) {
                    if (in_array($current->dayOfWeek, [0, 6])) {
                        $current->startOfDay()->addDays(1)->setTime($workStartHour, 0, 0);
                        continue;
                    }

                    if ($current->hour < $workStartHour) {
                        $current->setTime($workStartHour, 0, 0);
                    }

                    if ($current->hour >= $workEndHour) {
                        $current->startOfDay()->addDays(1)->setTime($workStartHour, 0, 0);
                        continue;
                    }

                    $endOfWorkDay = clone $current;
                    $endOfWorkDay->setTime($workEndHour, 0, 0);

                    $segmentEnd = $endOfWorkDay->lt($endCalc) ? $endOfWorkDay : $endCalc;

                    $diffInMinutes = $current->diffInMinutes($segmentEnd);
                    $totalHours += $diffInMinutes / 60.0;

                    $current = clone $segmentEnd;
                    if ($current->lt($endCalc)) {
                        $current->startOfDay()->addDays(1)->setTime($workStartHour, 0, 0);
                    }
                }

                $actualResolutionHours = round($totalHours, 2);
                $metSLA = $actualResolutionHours <= 8;

                if ($metSLA) {
                    $resolutionMet++;
                }

                $dateKey = $resolvedAt->format('Y-m-d');
                if (!isset($dailyResults[$dateKey])) {
                    $dailyResults[$dateKey] = [];
                }
                $dailyResults[$dateKey][] = $metSLA ? 1 : 0;
            } catch (\Exception $e) {
                continue;
            }
        }

        if ($totalTickets === 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $progress = round(($resolutionMet / $totalTickets) * 100, 1);
        $progress = min($progress, 100);

        $gapRaw = $progress - 100;
        $gap = $gapRaw < 0 ? abs($gapRaw) : 0;
        $gap = rtrim(rtrim(sprintf('%.1f', $gap), '0'), '.');

        $above = $resolutionMet;
        $below = $totalTickets - $resolutionMet;

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        foreach ($dailyResults as $dateStr => $results) {
            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $dateStr;

            $dailyAvg = round((array_sum($results) / count($results)) * 100, 1);

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
            }
            $monthlyData[$monthKey][] = $dailyAvg;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $dailyAvg;
        }

        $monthlyAverages = [];
        foreach ($monthlyData as $month => $dailyVals) {
            $monthlyAverages[$month] = round(array_sum($dailyVals) / count($dailyVals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    public function personalIndex()
    {
        return view('KPIdata.TargetSubDivisi.overviewKaryawan');
    }

    public function getDataOverviewPersonal(Request $request)
    {
        try {
            if (!Auth()->user()->karyawan) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Data karyawan tidak ditemukan',
                    ],
                    404,
                );
            }

            $karyawanId = Auth()->user()->id;
            $tahunFilter = $request->tahun ?? now()->year;

            $allTargets = targetKPI::with(['karyawan', 'detailTargetKPI.detailPersonKPI.karyawan'])
                ->whereYear('created_at', $tahunFilter)
                ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($karyawanId) {
                    $q->where('id_karyawan', $karyawanId);
                })
                ->get();

            $totalTarget = $allTargets->count();

            $progressValues = [];
            $kpiAktif = 0;
            $kpiSelesai = 0;
            $distribusiStatus = [
                'Selesai' => 0,
                'Aktif' => 0,
                'Belum Mulai' => 0,
            ];

            foreach ($allTargets as $target) {
                $detail = $target->detailTargetKPI->first();
                if (!$detail) {
                    continue;
                }

                $progress = $this->calculateProgress($target, $karyawanId);
                $nilaiTarget = $detail->nilai_target;

                if ($progress !== null) {
                    $progressValues[] = $progress;

                    if ($progress >= $nilaiTarget) {
                        $kpiSelesai++;
                        $distribusiStatus['Selesai']++;
                    } elseif ($progress > 0) {
                        $kpiAktif++;
                        $distribusiStatus['Aktif']++;
                    } else {
                        $distribusiStatus['Belum Mulai']++;
                    }
                }
            }

            $progressValuesNonZero = array_filter($progressValues, fn($v) => $v > 0);
            $rataRataProgress = count($progressValuesNonZero) > 0 ? round(array_sum($progressValuesNonZero) / count($progressValuesNonZero), 2) : 0;

            $statistikPerTarget = $this->getPersonalTargetStatistics($karyawanId, $tahunFilter);

            $daftarTargetPribadi = $this->getAllPersonalTargets($karyawanId, $tahunFilter);

            $karyawan = karyawan::where('id', $karyawanId)->first();

            return response()->json([
                'success' => true,
                'user_info' => [
                    'nama' => $karyawan->nama_lengkap,
                    'jabatan' => $karyawan->jabatan,
                    'divisi' => $karyawan->divisi,
                ],
                'total_target' => $totalTarget,
                'rata_rata_progress' => $rataRataProgress,
                'kpi_aktif' => $kpiAktif,
                'kpi_selesai' => $kpiSelesai,
                'statistik_per_target' => $statistikPerTarget,
                'distribusi_status' => $distribusiStatus,
                'daftar_target_pribadi' => $daftarTargetPribadi,
                'tahun' => $tahunFilter,
            ]);
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    private function getPersonalTargetStatistics($karyawanId, $tahun)
    {
        $statistik = [];

        $detailPersons = detailPersonKPI::whereHas('detailTargetKPI.targetKPI', function ($q) use ($tahun) {
            $q->whereYear('created_at', $tahun);
        })
            ->where('id_karyawan', $karyawanId)
            ->with(['detailTargetKPI.targetKPI'])
            ->get();

        foreach ($detailPersons as $dp) {
            $target = $dp->detailTargetKPI->targetKPI ?? null;
            if (!$target) {
                continue;
            }

            $detail = $target->detailTargetKPI->first();
            if (!$detail) {
                continue;
            }

            $progress = $this->calculateProgress($target, $karyawanId);
            $nilaiTarget = $detail->nilai_target;

            $statistik[] = [
                'judul' => $target->judul,
                'periode' => $detail->jangka_target . ' ' . $detail->detail_jangka,
                'target' => $nilaiTarget,
                'progress' => $progress ?? 0,
                'status' => $progress >= $nilaiTarget ? 'Selesai' : ($progress > 0 ? 'Aktif' : 'Belum Mulai'),
            ];
        }

        return $statistik;
    }

    private function getAllPersonalTargets($karyawanId, $tahun)
    {
        $targets = [];

        $detailPersons = detailPersonKPI::whereHas('detailTargetKPI.targetKPI', function ($q) use ($tahun) {
            $q->whereYear('created_at', $tahun);
        })
            ->where('id_karyawan', $karyawanId)
            ->with(['detailTargetKPI.targetKPI', 'detailTargetKPI'])
            ->get();

        foreach ($detailPersons as $dp) {
            $target = $dp->detailTargetKPI->targetKPI ?? null;
            if (!$target) {
                continue;
            }

            $detail = $target->detailTargetKPI->first();
            if (!$detail) {
                continue;
            }

            $progress = $this->calculateProgress($target, $karyawanId);
            $nilaiTarget = $detail->nilai_target;
            $status = $progress >= $nilaiTarget ? 'Selesai' : 'Aktif';

            $targets[] = [
                'id' => $target->id,
                'judul' => $target->judul,
                'periode' => $detail->jangka_target . ' ' . $detail->detail_jangka,
                'target' => $nilaiTarget,
                'progress' => round($progress ?? 0, 2),
                'progress_display' => $progress !== null ? round($progress, 2) . '%' : '-',
                'status' => $status,
                'status_badge' => $status === 'Selesai' ? 'bg-success' : 'bg-warning text-dark',
                'deskripsi' => $detail->deskripsi ?? '-',
                'created_at' => $target->created_at->format('d M Y'),
            ];
        }

        return $targets;
    }

    private function calculateProgress($target, $personId)
    {
        switch (strtolower($target->asistant_route)) {
            //Target Office
            //GM
            case 'Kepuasan Pelanggan':
                return $this->calculateProgressKepuasanPelanggan($target, $personId);
            case 'Pemasukan Kotor':
                return $this->calculatePemasukanKotor($target, $personId);
            case 'pemasukan bersih':
                return $this->calculatePemasukanBersih($target, $personId);
            case 'rasio biaya operasional terhadap revenue':
                return $this->calculateRasioBiayaOperasionalTerhadapRevenue($target, $personId);
            //CS
            case 'peserta puas dengan pelayanan dan fasilitas training':
                return $this->calculatePesertaPuasDenganPelayananDanFasilitasTraining($target, $personId);
            case 'dorong inovasi pelayanan':
                return $this->calculateDorongInovasiPelayanan($target, $personId);
            case 'report persiapan kelas':
                return $this->calculateReportPersiapanKelas($target, $personId);
            //Finance
            case 'inisiatif efesiensi keuangan':
                return $this->calculateInisiatifEfisiensiKeuangan($target, $personId);
            case 'outstanding':
                return $this->calculateOutstanding($target, $personId);

            //Target ITSM
            //All kecuali Koordinator ITSM
            case 'kepuasan client itsm':
                return $this->calculateProgressKepuasanClientITSM($target, $personId);
            //Koordinator ITSM
            case 'availability sistem internal kritis':
                return $this->calculateAvailabilitySistemInternalKritis($target, $personId);
            case 'meningkatkan kepuasan dan loyalitas peserta/client':
                return $this->calculateMeningkatkanKepuasanDanLoyalitasPeserta($target, $personId);
            //Programmer
            case 'ketepatan waktu penyelesaian fitur':
                return $this->calculateProgressKetepatanWaktuPenyelesaianFitur($target, $personId);
            case 'mengukur kualitas aplikasi agar minim bug':
                return $this->calculateMengukurKualitasAplikasiAgarMinimBug($target, $personId);
            //Tim Digital
            case 'konsistensi campaign digital':
                return $this->calculateKonsistensiCampaignDigital($target, $personId);
            //TS
            case 'keberhasilan support memenuhi sla':
                return $this->calculateTingkatKeberhasilanSupportMemenuhiSLA($target, $personId);
            default:
                return null;
        }
    }

    public function kpiOverview()
    {
        $userId = Auth()->id();

        $userKaryawan = karyawan::where('id', $userId)->first();
        $divisi = $userKaryawan->divisi ?? null;
        $jabatan = $userKaryawan->jabatan ?? null;

        if ($jabatan === 'GM') {
            $departments = karyawan::where('divisi', '!=', 'Direksi')->whereNotNull('divisi')->distinct()->pluck('divisi')->values();
        } else {
            $departments = collect([$divisi])->filter();
        }

        return view('KPIdata.TargetDivisi.overview', compact('departments', 'divisi', 'jabatan'));
    }
    public function getDataOverview(Request $request)
    {
        $divisiFilter = $request->divisi;
        $tahunFilter = $request->tahun;

        if (!$divisiFilter || !$tahunFilter) {
            return response()->json(['message' => 'Divisi dan tahun harus diisi'], 400);
        }

        $karyawanDiDivisi = karyawan::where('divisi', $divisiFilter)->get();
        $karyawanIds = $karyawanDiDivisi->pluck('id')->toArray();

        if (empty($karyawanIds)) {
            return response()->json([
                'total_target' => 0,
                'rata_rata_progress' => 0,
                'kpi_aktif' => 0,
                'kpi_selesai' => 0,
                'karyawan_departemen' => [],
                'statistik_karyawan' => [],
                'distribusi_nilai' => [
                    '0-25%' => 0,
                    '26-50%' => 0,
                    '51-75%' => 0,
                    '76-100%' => 0,
                    '>100%' => 0,
                ],
                'daftar_target_kpi' => [],
            ]);
        }

        $allTargets = targetKPI::with(['karyawan', 'detailTargetKPI.detailPersonKPI.karyawan'])
            ->whereYear('created_at', $tahunFilter)
            ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($karyawanIds) {
                $q->whereIn('id_karyawan', $karyawanIds);
            })
            ->get();

        $totalTarget = $allTargets->count();

        $progressValues = [];
        $kpiAktif = 0;
        $kpiSelesai = 0;
        $distribusi = ['0-25%' => 0, '26-50%' => 0, '51-75%' => 0, '76-100%' => 0];

        foreach ($allTargets as $target) {
            $detail = $target->detailTargetKPI->first();
            if (!$detail) {
                continue;
            }

            $personIds = $detail->detailPersonKPI->pluck('id_karyawan')->toArray();
            if (empty($personIds)) {
                continue;
            }

            $targetProgressValues = [];
            foreach ($personIds as $personId) {
                $progress = $this->calculateProgress($target, $personId);
                if ($progress !== null && $progress > 0) {
                    $targetProgressValues[] = $progress;
                }
            }

            if (!empty($targetProgressValues)) {
                $progressRataRata = array_sum($targetProgressValues) / count($targetProgressValues);
                $progressValues[] = $progressRataRata;

                if ($progressRataRata <= 25) {
                    $distribusi['0-25%']++;
                } elseif ($progressRataRata <= 50) {
                    $distribusi['26-50%']++;
                } elseif ($progressRataRata <= 75) {
                    $distribusi['51-75%']++;
                } elseif ($progressRataRata <= 100) {
                    $distribusi['76-100%']++;
                } else {
                    $distribusi['>100%']++;
                }

                if ($progressRataRata < $detail->nilai_target) {
                    $kpiAktif++;
                } else {
                    $kpiSelesai++;
                }
            }
        }

        $rataRataProgress = count($progressValues) > 0 ? round(array_sum($progressValues) / count($progressValues), 2) : 0;

        $karyawanDepartemen = [];
        foreach ($karyawanDiDivisi as $karyawan) {
            $karyawanTargets = targetKPI::with(['detailTargetKPI.detailPersonKPI'])
                ->whereYear('created_at', $tahunFilter)
                ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($karyawan) {
                    $q->where('id_karyawan', $karyawan->id);
                })
                ->get();

            $progressList = [];
            $targetBelumTercapai = 0;

            foreach ($karyawanTargets as $target) {
                $detail = $target->detailTargetKPI->first();
                if (!$detail) {
                    continue;
                }

                $progress = $this->calculateProgress($target, $karyawan->id);
                $nilaiTarget = $detail->nilai_target;

                if ($progress !== null) {
                    if ($progress > 0) {
                        $progressList[] = $progress;
                    }

                    if ($progress < $nilaiTarget) {
                        $targetBelumTercapai++;
                    }
                }
            }

            $rataRata = count($progressList) > 0 ? round(array_sum($progressList) / count($progressList), 2) : 0;

            $karyawanDepartemen[] = [
                'nama' => $karyawan->nama_lengkap,
                'jabatan' => $karyawan->jabatan,
                'total_target_belum_tercapai' => $targetBelumTercapai,
                'rata_rata_progress' => $rataRata,
            ];
        }

        $statistikKaryawan = $this->getEmployeeStatistics($tahunFilter, $karyawanIds, $divisiFilter);

        $daftarTargetKPI = [];
        foreach ($allTargets as $target) {
            $detail = $target->detailTargetKPI->first();
            if (!$detail) {
                continue;
            }

            $personIds = $detail->detailPersonKPI->pluck('id_karyawan')->toArray();
            $progressList = [];
            foreach ($personIds as $personId) {
                $progress = $this->calculateProgress($target, $personId);
                if ($progress !== null && $progress > 0) {
                    $progressList[] = $progress;
                }
            }

            $progressRataRata = count($progressList) > 0 ? array_sum($progressList) / count($progressList) : 0;

            $status = $progressRataRata >= $detail->nilai_target ? 'Selesai' : 'Belum Selesai';

            $daftarTargetKPI[] = [
                'judul' => $target->judul,
                'periode' => $detail->jangka_target . ' ' . $detail->detail_jangka,
                'target' => $detail->nilai_target,
                'progress' => round($progressRataRata, 2),
                'status' => $status,
            ];
        }

        return response()->json([
            'total_target' => $totalTarget,
            'rata_rata_progress' => $rataRataProgress,
            'kpi_aktif' => $kpiAktif,
            'kpi_selesai' => $kpiSelesai,
            'karyawan_departemen' => $karyawanDepartemen,
            'statistik_karyawan' => $statistikKaryawan,
            'distribusi_nilai' => $distribusi,
            'daftar_target_kpi' => $daftarTargetKPI,
        ]);
    }

    private function getEmployeeStatistics($tahun, $karyawanIds, $divisi)
    {
        $statistik = [];

        $allKaryawan = karyawan::whereIn('id', $karyawanIds)->get();

        foreach ($allKaryawan as $karyawan) {
            $detailPersons = detailPersonKPI::whereHas('detailTargetKPI.targetKPI', function ($q) use ($tahun) {
                $q->whereYear('created_at', $tahun);
            })
                ->where('id_karyawan', $karyawan->id)
                ->with(['detailTargetKPI.targetKPI'])
                ->get();

            $totalTarget = $detailPersons->count();
            $progressList = [];
            $targetAktif = 0;
            $targetSelesai = 0;

            foreach ($detailPersons as $dp) {
                $target = $dp->detailTargetKPI->targetKPI ?? null;
                if (!$target) {
                    continue;
                }

                $detail = $target->detailTargetKPI->first();
                if (!$detail) {
                    continue;
                }

                $progress = $this->calculateProgress($target, $karyawan->id);
                $nilaiTarget = $detail->nilai_target;

                if ($progress !== null && $progress > 0) {
                    $progressList[] = $progress;
                }

                if ($progress < $nilaiTarget) {
                    $targetAktif++;
                } else {
                    $targetSelesai++;
                }
            }

            $rataRataProgress = count($progressList) > 0 ? round(array_sum($progressList) / count($progressList), 2) : 0;

            $statistik[] = [
                'nama' => explode(' ', $karyawan->nama_lengkap)[0],
                'jabatan' => $karyawan->jabatan,
                'total_target' => $totalTarget,
                'target_aktif' => $targetAktif,
                'target_selesai' => $targetSelesai,
                'rata_rata_progress' => $rataRataProgress,
            ];
        }

        return $statistik;
    }
}
