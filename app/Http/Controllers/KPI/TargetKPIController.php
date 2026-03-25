<?php

namespace App\Http\Controllers\KPI;

use App\Http\Controllers\Controller;
use App\Models\AbsensiKaryawan;
use App\Models\ActivityInstruktur;
use App\Models\activityLog;
use App\Models\BiayaTransportasiDriver;
use App\Models\ChecklistKeperluan;
use App\Models\colaborator;
use App\Models\ContentSchedule;
use App\Models\detailPersonKPI;
use App\Models\DetailTargetKPI;
use App\Models\formPenilaian;
use App\Models\JenisTunjangan;
use App\Models\IdeInovasi;
use App\Models\karyawan;
use App\Models\KategoriDaftarTugas;
use App\Models\Kegiatan;
use App\Models\KomplainPeserta;
use App\Models\KondisiKendaraan;
use App\Models\KontrolTugas;
use App\Models\LaporanHarianSales;
use App\Models\Materi;
use App\Models\Nilaifeedback;
use App\Models\nilaiKPI;
use App\Models\NomorModul;
use App\Models\outstanding;
use App\Models\Pelatihan;
use App\Models\Peluang;
use App\Models\PengajuanBarang;
use App\Models\PenilaianExam;
use App\Models\PerbaikanKendaraan;
use App\Models\perhitunganNetSales;
use App\Models\pickupDriver;
use App\Models\RekomendasiLanjutan;
use App\Models\RKM;
use App\Models\Sertifikasi;
use App\Models\SurveyKepuasan;
use App\Models\targetKPI;
use App\Models\Tickets;
use App\Models\trackingTagihanPerusahaan;
use App\Models\TunjanganKaryawan;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Google\Service\CloudDeploy\Target;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TargetKPIController extends Controller
{
    public function kpiIndex()
    {
        $daftarKaryawan = karyawan::where('status_aktif', '1')->whereNot('divisi', 'Direksi')->get();

        return view('KPIdata.TargetDivisi.index', compact('daftarKaryawan'));
    }

    public function getKaryawanByJabatan(Request $request)
    {
        $jabatanList = $request->input('jabatan', []);

        if (!is_array($jabatanList)) {
            $jabatanList = [$jabatanList];
        }

        $karyawan = karyawan::whereIn('jabatan', $jabatanList)
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
                $dataDivisi = karyawan::where('jabatan', $jabatan)->whereNot('divisi', 'Direksi')->first();

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
            'manual_value' => 'nullable|string',
            'biaya_gaji_tahunan' => 'nullable|numeric',
            'biaya_bpjs_tahunan' => 'nullable|numeric',
            'biaya_rekrutmen_tahunan' => 'nullable|numeric',
            'manual_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $detail = DetailTargetKPI::where('id', $request->id)->first();

        if (!$detail) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Detail Target KPI tidak ditemukan',
                ],
                404,
            );
        }

        $existingDocument = $detail->manual_document;
        $manualValue = $request->manual_value;

        if (!is_null($request->biaya_gaji_tahunan) || !is_null($request->biaya_bpjs_tahunan) || !is_null($request->biaya_rekrutmen_tahunan)) {
            $gaji = $request->biaya_gaji_tahunan ?? 0;
            $bpjs = $request->biaya_bpjs_tahunan ?? 0;
            $rekrutmen = $request->biaya_rekrutmen_tahunan ?? 0;

            $manualValue = (int) $gaji . ',' . (int) $bpjs . ',' . (int) $rekrutmen;
        }

        $updateData = [
            'manual_value' => $manualValue,
        ];

        if ($request->hasFile('manual_document')) {
            $file = $request->file('manual_document');

            if ($existingDocument && Storage::disk('public')->exists($existingDocument)) {
                Storage::disk('public')->delete($existingDocument);
            }

            $filePath = $file->store('manual_documents', 'public');
            $updateData['manual_document'] = $filePath;
        }

        $detail->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil memasukkan data manual',
            'data' => [
                'id' => $detail->id,
                'manual_value' => $detail->manual_value,
                'manual_document' => $detail->manual_document,
            ],
        ]);
    }

    public function getProgressDashboard(Request $request)
    {
        $user = auth()->user();
        $id_pembuat = $user->id;
        $jabatan_pembuat = $user->jabatan;
        $idUser = $request->idUser;
        $typeGet = $request->typeGet;
        $currentYear = now()->year;

        $targetEmployeeId = filled($idUser) && filled($typeGet) ? $idUser : $id_pembuat;
        $karyawan = karyawan::find($targetEmployeeId);

        if (!$karyawan) {
            return response()->json(['average_progress' => 0, 'message' => 'Karyawan tidak ditemukan'], 404);
        }

        $calculatePenilaianScore = function ($collectionNilaiKPI) {
            $persentaseJenis = [
                'General Manager' => 35,
                'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
                'Rekan Kerja (Satu Divisi)' => 20,
                'Pekerja (Beda Divisi)' => 10,
                'Self Apprisial' => 5,
            ];
            $jenisTotalRaw = [];
            foreach ($persentaseJenis as $jenis => $bobot) {
                $nilaiForJenis = $collectionNilaiKPI->where('jenis_penilaian', $jenis)->pluck('nilai')->filter(fn($n) => is_numeric($n));
                if ($nilaiForJenis->isNotEmpty()) {
                    $avgNilai = $nilaiForJenis->avg();
                    $jenisTotalRaw[$jenis] = ($avgNilai * $bobot) / 100;
                }
            }
            return empty($jenisTotalRaw) ? 0 : round(array_sum($jenisTotalRaw), 2);
        };

        $getProgressValue = function ($item, $personId) use ($calculatePenilaianScore) {
            $route = $item->asistant_route;

            // Office / GM
            if ($route === 'Kepuasan Pelanggan') {
                return method_exists($this, 'calculateProgressKepuasanPelanggan') ? $this->calculateProgressKepuasanPelanggan($item, $personId) : null;
            } elseif ($route === 'Pemasukan Kotor') {
                return method_exists($this, 'calculatePemasukanKotor') ? $this->calculatePemasukanKotor($item, $personId) : null;
            } elseif ($route == 'pemasukan bersih') {
                return method_exists($this, 'calculatePemasukanBersih') ? $this->calculatePemasukanBersih($item, $personId) : null;
            } elseif ($route === 'rasio biaya operasional terhadap revenue') {
                return method_exists($this, 'calculateRasioBiayaOperasionalTerhadapRevenue') ? $this->calculateRasioBiayaOperasionalTerhadapRevenue($item, $personId) : null;
            } elseif ($route === 'pencairan biaya operasional') {
                return method_exists($this, 'calculatePencairanBiayaOperasional') ? $this->calculatePencairanBiayaOperasional($item, $personId) : null;
            }
            // CS
            elseif ($route === 'peserta puas dengan pelayanan dan fasilitas training') {
                return method_exists($this, 'calculatePesertaPuasDenganPelayananDanFasilitasTraining') ? $this->calculatePesertaPuasDenganPelayananDanFasilitasTraining($item, $personId) : null;
            } elseif ($route === 'dorong inovasi pelayanan') {
                return method_exists($this, 'calculateDorongInovasiPelayanan') ? $this->calculateDorongInovasiPelayanan($item, $personId) : null;
            } elseif ($route === 'penanganan komplain peserta') {
                return method_exists($this, 'calculatePenangananKomplainPerseta') ? $this->calculatePenangananKomplainPerseta($item, $personId) : null;
            } elseif ($route === '') {
                return method_exists($this, 'calculateReportPersiapanKelas') ? $this->calculateReportPersiapanKelas($item, $personId) : null;
            }

            // Finance
            elseif ($route === 'outstanding') {
                return method_exists($this, 'calculateOutstanding') ? $this->calculateOutstanding($item, $personId) : null;
            } elseif ($route === 'inisiatif efisiensi keuangan') {
                return method_exists($this, 'calculateInisiatifEfisiensiKeuangan') ? $this->calculateInisiatifEfisiensiKeuangan($item, $personId) : null;
            } elseif ($route === 'mengurangi manual work dan error') {
                return method_exists($this, 'calculateMengurangiManualWorkDanError') ? $this->calculateMengurangiManualWorkDanError($item, $personId) : null;
            } elseif ($route === 'laporan analisis keuangan') {
                return method_exists($this, 'calculateLaporanAnalisisKeuangan') ? $this->calculateLaporanAnalisisKeuangan($item, $personId) : null;
            } elseif ($route === 'penyelesaian tagihan perusahaan') {
                return method_exists($this, 'calculatePenyelesaianTagihanPerusahaan') ? $this->calculatePenyelesaianTagihanPerusahaan($item, $personId) : null;
            }
            // HRD
            elseif ($route === 'pelaksanaan kegiatan karyawan') {
                return method_exists($this, 'calculatePelaksanaanKegiatanKaryawan') ? $this->calculatePelaksanaanKegiatanKaryawan($item, $personId) : null;
            } elseif ($route === 'pengeluaran biaya karyawan') {
                return method_exists($this, 'calculatePengeluaranBiayaKaryawan') ? $this->calculatePengeluaranBiayaKaryawan($item, $personId) : null;
            } elseif ($route === 'administrasi karyawan') {
                return method_exists($this, 'calculateAdministrasiKaryawan') ? $this->calculateAdministrasiKaryawan($item, $personId) : null;
            }

            // Driver
            elseif ($route === 'perbaikan kendaraan') {
                return method_exists($this, 'calculatePerbaikanKendaraan') ? $this->calculatePerbaikanKendaraan($item, $personId) : null;
            } elseif ($route === 'kontrol pengeluaran transportasi') {
                return method_exists($this, 'calculateKontrolPengeluaranTransportasi') ? $this->calculateKontrolPengeluaranTransportasi($item, $personId) : null;
            } elseif ($route === 'report kondisi kendaraan') {
                return method_exists($this, 'calculateReportKondisiKendaraan') ? $this->calculateReportKondisiKendaraan($item, $personId) : null;
            }
            // OB
            elseif ($route === 'feedback kebersihan dan kenyamanan') {
                return method_exists($this, 'calculateFeedbackKebersihanDanKenyamanan') ? $this->calculateFeedbackKebersihanDanKenyamanan($item, $personId) : null;
            } elseif ($item->asistant_route === 'penyelesaian tugas harian') {
                return method_exists($this, 'calculatePenyelesaianTugasHarian') ? $this->calculatePenyelesaianTugasHarian($item, $personId) : null;
            }

            // ITSM
            elseif ($route === 'kepuasan client ITSM') {
                return method_exists($this, 'calculateProgressKepuasanClientITSM') ? $this->calculateProgressKepuasanClientITSM($item, $personId) : null;
            } elseif ($route === 'inovation adaption rate') {
                return method_exists($this, 'calculateInovationAdaptionRate') ? $this->calculateInovationAdaptionRate($item, $personId) : null;
            } elseif ($route === 'availability sistem internal kritis') {
                return method_exists($this, 'calculateAvailabilitySistemInternalKritis') ? $this->calculateAvailabilitySistemInternalKritis($item, $personId) : null;
            } elseif ($route === 'meningkatkan kepuasan dan loyalitas peserta/client') {
                return method_exists($this, 'calculateMeningkatkanKepuasanDanLoyalitasPeserta') ? $this->calculateMeningkatkanKepuasanDanLoyalitasPeserta($item, $personId) : null;
            }
            // Programmer
            elseif ($route === 'ketepatan waktu penyelesaian fitur') {
                return method_exists($this, 'calculateProgressKetepatanWaktuPenyelesaianFitur') ? $this->calculateProgressKetepatanWaktuPenyelesaianFitur($item, $personId) : null;
            } elseif ($route === 'mengukur kualitas aplikasi agar minim bug') {
                return method_exists($this, 'calculateMengukurKualitasAplikasiAgarMinimBug') ? $this->calculateMengukurKualitasAplikasiAgarMinimBug($item, $personId) : null;
            }
            // Tim Digital
            elseif ($route === 'konsistensi campaign digital') {
                return method_exists($this, 'calculateKonsistensiCampaignDigital') ? $this->calculateKonsistensiCampaignDigital($item, $personId) : null;
            } elseif ($route === 'efektifitas diital marketing') {
                return method_exists($this, 'calculateEfektifitasDiitalMarketing') ? $this->calculateEfektifitasDiitalMarketing($item, $personId) : null;
            }
            // TS
            elseif ($route === 'keberhasilan support memenuhi sla') {
                return method_exists($this, 'calculateTingkatKeberhasilanSupportMemenuhiSLA') ? $this->calculateTingkatKeberhasilanSupportMemenuhiSLA($item, $personId) : null;
            } elseif ($route === 'kualitas layanan exam') {
                return method_exists($this, 'calculateKualitasLayananExam') ? $this->calculateKualitasLayananExam($item, $personId) : null;
            }
            // Education - Instruktur
            elseif ($route === 'kepuasan peserta pelatihan') {
                return method_exists($this, 'calculateKepuasanPesertaPelatihan') ? $this->calculateKepuasanPesertaPelatihan($item, $personId) : null;
            } elseif ($route === 'upseling lanjutan materi') {
                return method_exists($this, 'calculateUpselingLanjutanMateri') ? $this->calculateUpselingLanjutanMateri($item, $personId) : null;
            } elseif ($route === 'sertifikasi kompetensi internal') {
                return method_exists($this, 'calculateSertifikasiKompetensiInternal') ? $this->calculateSertifikasiKompetensiInternal($item, $personId) : null;
            } elseif ($route === 'pelatihan kompetensi eksternal') {
                return method_exists($this, 'calculatePelatihanKompetensiEksternal') ? $this->calculatePelatihanKompetensiEksternal($item, $personId) : null;
            }
            // Education Manager
            elseif ($route === 'pengembangan kurikulum pelatihan') {
                return method_exists($this, 'calculatePengembanganKurikulumPelatihan') ? $this->calculatePengembanganKurikulumPelatihan($item, $personId) : null;
            } elseif ($route === 'peningkatan knowledge sharing') {
                return method_exists($this, 'calculatePeningkatanKnowledgeSharing') ? $this->calculatePeningkatanKnowledgeSharing($item, $personId) : null;
            }

            //Sales & Marketing
            else if ($route === 'target penjualan tahunan') {
                return method_exists($this, 'calculateTargetPenjualanTahunan') ? $this->calculateTargetPenjualanTahunan($item, $personId) : null;
            }// SPV Sales
            elseif ($route === 'meningkatkan revenue perusahaan') {
                return method_exists($this, 'calculateMeningkatkanRevenuePerusahaan') ? $this->calculateMeningkatkanRevenuePerusahaan($item, $personId) : null;
            }

            // Adm Sales
            elseif($route === 'laporan mom') {
                return method_exists($this, 'calculateLaporanMOM') ? $this->calculateLaporanMOM($item) : null;
            }

            // Adm Holding
            elseif($route === 'ketepatan waktu po') {
                return method_exists($this, 'calculateKetepatanWaktuPo') ? $this->calculateKetepatanWaktuPo($item) : null;
            }

            return null;
        };

        $calculateEmployeeTargetKpi = function ($empId, $yr) use ($getProgressValue) {
            $kpiQuery = targetKPI::with(['detailTargetKPI.detailPersonKPI'])
                ->whereYear('created_at', $yr)
                ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($empId) {
                    $q->where('id_karyawan', $empId);
                });
            $listKPI = $kpiQuery->get();
            $totalTargetProgress = 0;
            $countTarget = 0;
            $monthlyTargetValues = [];

            foreach ($listKPI as $item) {
                $progress = $getProgressValue($item, $empId);
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
            return ['avgTargetYearly' => $avgTargetYearly, 'monthlyTargetValues' => $monthlyTargetValues];
        };

        $calculatePersonalKpiDashboard = function ($tEmpId, $jPembuat, $iPembuat, $iUser, $tGet, $cYear) use ($getProgressValue, $calculatePenilaianScore) {
            $kpiQuery = targetKPI::with(['detailTargetKPI.detailPersonKPI'])->whereYear('created_at', $cYear);
            if (filled($iUser) && filled($tGet)) {
                $kpiQuery->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($tEmpId) {
                    $q->where('id_karyawan', $tEmpId);
                });
            } else {
                $hasTarget = targetKPI::whereYear('created_at', $cYear)
                    ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($tEmpId) {
                        $q->where('id_karyawan', $tEmpId);
                    })
                    ->exists();
                if ($hasTarget) {
                    $kpiQuery->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($tEmpId) {
                        $q->where('id_karyawan', $tEmpId);
                    });
                } elseif ($jPembuat !== 'GM') {
                    $kpiQuery->where('id_pembuat', $iPembuat);
                } else {
                    $kpiQuery->where('id', -1);
                }
            }
            $listKPI = $kpiQuery->get();
            $listPenilaian = formPenilaian::where('id_karyawan', $tEmpId)->whereYear('created_at', $cYear)->orderBy('created_at', 'asc')->get();
            $allNilaiKPI = nilaiKPI::where('id_evaluated', $tEmpId)->whereYear('created_at', $cYear)->get();

            $totalTargetProgress = 0;
            $countTarget = 0;
            foreach ($listKPI as $item) {
                $progress = $getProgressValue($item, $tEmpId);
                if (is_numeric($progress)) {
                    $totalTargetProgress += $progress;
                    $countTarget++;
                }
            }
            $avgTargetYearly = $countTarget > 0 ? round($totalTargetProgress / $countTarget, 2) : 0;
            $avgPenilaianYearly = $calculatePenilaianScore($allNilaiKPI);

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
                $nilaiKpiAnda = round($avgTargetYearly * 0.6 + $avgPenilaianYearly * 0.4, 2);
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
                $kpiBulanIni = $listKPI->filter(fn($k) => $k->created_at->year == $year && $k->created_at->month == $month);
                foreach ($kpiBulanIni as $item) {
                    $progress = $getProgressValue($item, $tEmpId);
                    if (is_numeric($progress)) {
                        $monthlyTargetProgress += $progress;
                        $monthlyCountTarget++;
                    }
                }
                $valTarget = $monthlyCountTarget > 0 ? round($monthlyTargetProgress / $monthlyCountTarget, 2) : 0;

                $valPenilaian = 0;
                $targetMonthStart = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
                foreach ($listPenilaian as $eval) {
                    $evalDate = \Carbon\Carbon::parse($eval->created_at);
                    $firstEvalOfYear = $listPenilaian->first(fn($e) => \Carbon\Carbon::parse($e->created_at)->year == $evalDate->year);
                    $isFirstEvaluation = $eval->id == $firstEvalOfYear->id;
                    $evalStart = $isFirstEvaluation ? \Carbon\Carbon::create($evalDate->year, 1, 1)->startOfDay() : $evalDate->copy()->startOfMonth();
                    $nextEval = $listPenilaian->first(fn($e) => \Carbon\Carbon::parse($e->created_at)->gt($evalDate));
                    $evalEnd = $nextEval ? \Carbon\Carbon::parse($nextEval->created_at)->subDay() : \Carbon\Carbon::create($evalDate->year, 12, 31)->endOfDay();
                    if ($targetMonthStart >= $evalStart && $targetMonthStart <= $evalEnd) {
                        $nilaiEvalBulanIni = $allNilaiKPI->filter(fn($n) => $n->kode_form == $eval->kode_form);
                        $valPenilaian = $calculatePenilaianScore($nilaiEvalBulanIni);
                        break;
                    }
                }

                $finalMonthly = 0;
                if ($valTarget == 0 && $valPenilaian == 0) {
                    $finalMonthly = 0;
                } elseif ($valTarget == 0) {
                    $finalMonthly = $valPenilaian * 0.4;
                } elseif ($valPenilaian == 0) {
                    $finalMonthly = $valTarget;
                } else {
                    $finalMonthly = round($valTarget * 0.6 + $valPenilaian * 0.4, 2);
                }

                $kpiPerbulan[] = ['bulan' => $monthLabel, 'nilai' => $finalMonthly];
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
                if ($performance > 5) {
                    $performanceTitle = 'Naik';
                } elseif ($performance < -5) {
                    $performanceTitle = 'Turun';
                }
            }

            $deadline = "{$cYear}-12-31 23:59:59";
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
        };

        $personalDashboard = $calculatePersonalKpiDashboard($targetEmployeeId, $jabatan_pembuat, $id_pembuat, $idUser, $typeGet, $currentYear);

        $divisionTeamData = [];
        $currentUser = karyawan::find($id_pembuat);
        if ($currentUser && !empty($currentUser->divisi)) {
            $divisiUser = $currentUser->divisi;
            $teamMembers = karyawan::where('divisi', $divisiUser)->get();
            foreach ($teamMembers as $member) {
                $kpiData = $calculatePersonalKpiDashboard($member->id, $jabatan_pembuat, $id_pembuat, $member->id, 'divisi', $currentYear);
                $divisionTeamData[] = [
                    'nama_karyawan' => $member->nama_lengkap,
                    'jabatan' => $member->divisi,
                    'nilaitargetkpi' => $kpiData['nilai_kpi_anda'] ?? 0,
                    'performance' => $kpiData['performance_title'] ?? 'Stabil',
                    'nilai_performance' => $kpiData['performance'] ?? 0,
                ];
            }
        }

        $divisionKpiData = [];
        $divisions = karyawan::whereNotNull('divisi')
            ->whereNotIn('divisi', ['', 'Pilih Divisi', 'Direksi'])
            ->distinct()
            ->pluck('divisi');
        foreach ($divisions as $divisi) {
            $employees = karyawan::where('divisi', $divisi)->get();
            $totalKpiValue = 0;
            $countEmployees = 0;
            $monthlyKpiValues = [];
            foreach ($employees as $employee) {
                $kpiData = $calculateEmployeeTargetKpi($employee->id, $currentYear);
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
                $currentMonthAvg = count($monthlyKpiValues[$lastMonth]) > 0 ? array_sum($monthlyKpiValues[$lastMonth]) / count($monthlyKpiValues[$lastMonth]) : 0;
                $prevMonthAvg = count($monthlyKpiValues[$prevMonth]) > 0 ? array_sum($monthlyKpiValues[$prevMonth]) / count($monthlyKpiValues[$prevMonth]) : 0;
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
            $divisionKpiData[] = [
                'divisi' => $divisi,
                'nilai_kpi' => $avgKpiValue,
                'performance' => $performance,
                'performance_title' => $performanceTitle,
                'tahun' => $currentYear,
            ];
        }

        return response()->json([
            'output_1' => $personalDashboard,
            'output_2' => $divisionTeamData,
            'output_3' => $divisionKpiData,
        ]);
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
                    } elseif ($item->asistant_route === 'performa KPI departemen') {
                        $progress = $this->calculatePerformaKPIDepartemen($item, $personId);
                    }
                    //CS
                    elseif ($item->asistant_route === 'peserta puas dengan pelayanan dan fasilitas training') {
                        $progress = $this->calculatePesertaPuasDenganPelayananDanFasilitasTraining($item, $personId);
                    } elseif ($item->asistant_route === 'dorong inovasi pelayanan') {
                        $progress = $this->calculateDorongInovasiPelayanan($item, $personId);
                    } elseif ($item->asistant_route === 'penanganan komplain peserta') {
                        $progress = $this->calculatePenangananKomplainPerseta($item, $personId);
                    } elseif ($item->asistant_route === 'report persiapan kelas') {
                        $progress = $this->calculateReportPersiapanKelas($item, $personId);
                    }

                    //finance
                    elseif ($item->asistant_route === 'outstanding') {
                        $progress = $this->calculateOutstanding($item, $personId);
                    } elseif ($item->asistant_route === 'inisiatif efisiensi keuangan') {
                        $progress = $this->calculateInisiatifEfisiensiKeuangan($item, $personId);
                    } elseif ($item->asistant_route === 'mengurangi manual work dan error') {
                        $progress = $this->calculateMengurangiManualWorkDanError($item, $personId);
                    } elseif ($item->asistant_route === 'laporan analisis keuangan') {
                        $progress = $this->calculateLaporanAnalisisKeuangan($item, $personId);
                    } elseif ($item->asistant_route === 'pencairan biaya operasional') {
                        $progress = $this->calculatePencairanBiayaOperasional($item, $personId);
                    } elseif ($item->asistant_route === 'penyelesaian tagihan perusahaan') {
                        $progress = $this->calculatePenyelesaianTagihanPerusahaan($item, $personId);
                    }

                    //HRD
                    elseif ($item->asistant_route === 'pelaksanaan kegiatan karyawan') {
                        $progress = $this->calculatePelaksanaanKegiatanKaryawan($item, $personId);
                    } elseif ($item->asistant_route === 'pengeluaran biaya karyawan') {
                        $progress = $this->calculatePengeluaranBiayaKaryawan($item, $personId);
                    } elseif ($item->asistant_route === 'administrasi karyawan') {
                        $progress = $this->calculateAdministrasiKaryawan($item, $personId);
                    }

                    //Driver
                    elseif ($item->asistant_route === 'perbaikan kendaraan') {
                        $progress = $this->calculatePerbaikanKendaraan($item, $personId);
                    } elseif ($item->asistant_route === 'kontrol pengeluaran transportasi') {
                        $progress = $this->calculateKontrolPengeluaranTransportasi($item, $personId);
                    } elseif ($item->asistant_route === 'report kondisi kendaraan') {
                        $progress = $this->calculateReportKondisiKendaraan($item, $personId);
                    }

                    //OB
                    elseif ($item->asistant_route === 'feedback kebersihan dan kenyamanan') {
                        $progress = $this->calculateFeedbackKebersihanDanKenyamanan($item, $personId);
                    } elseif ($item->asistant_route === 'penyelesaian tugas harian') {
                        $progress = $this->calculatePenyelesaianTugasHarian($item, $personId);
                    }

                    //ITSM
                    //All kecuali Koordinator ITSM
                    elseif ($item->asistant_route === 'kepuasan client ITSM') {
                        $progress = $this->calculateProgressKepuasanClientITSM($item, $personId);
                    } elseif ($item->asistant_route === 'inovation adaption rate') {
                        $progress = $this->calculateInovationAdaptionRate($item, $personId);
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
                    } elseif ($item->asistant_route === 'efektifitas diital marketing') {
                        $progress = $this->calculateEfektifitasDiitalMarketing($item, $personId);
                    }

                    //TS
                    elseif ($item->asistant_route === 'keberhasilan support memenuhi sla') {
                        $progress = $this->calculateTingkatKeberhasilanSupportMemenuhiSLA($item, $personId);
                    } elseif ($item->asistant_route === 'kualitas layanan exam') {
                        $progress = $this->calculateKualitasLayananExam($item, $personId);
                    }

                    //Education
                    //Instruktur
                    elseif ($item->asistant_route === 'kepuasan peserta pelatihan') {
                        $progress = $this->calculateKepuasanPesertaPelatihan($item, $personId);
                    } elseif ($item->asistant_route === 'upseling lanjutan materi') {
                        $progress = $this->calculateUpselingLanjutanMateri($item, $personId);
                    } elseif ($item->asistant_route === 'sertifikasi kompetensi internal') {
                        $progress = $this->calculateSertifikasiKompetensiInternal($item, $personId);
                    } elseif ($item->asistant_route === 'pelatihan kompetensi eksternal') {
                        $progress = $this->calculatePelatihanKompetensiEksternal($item, $personId);
                    }

                    //Education Manager
                    elseif ($item->asistant_route === 'pengembangan kurikulum pelatihan') {
                        $progress = $this->calculatePengembanganKurikulumPelatihan($item, $personId);
                    } elseif ($item->asistant_route === 'peningkatan knowledge sharing') {
                        $progress = $this->calculatePeningkatanKnowledgeSharing($item, $personId);
                    }

                    //Sales & Marketing
                    elseif ($item->asistant_route === 'target penjualan tahunan') {
                        $progress = $this->calculateTargetPenjualanTahunan($item, $personId);
                    }
                  
                    //SPV Sales
                    elseif ($item->asistant_route === 'meningkatkan revenue perusahaan') {
                        $progress = $this->calculateMeningkatkanRevenuePerusahaan($item, $personId);
                    }

                    // Adm Sales
                    elseif ($item->asistant_route === 'laporan mom') {
                        $progress = $this->calculateLaporanMOM($item, $personId);
                    }

                    // Adm Holding
                    elseif ($item->asistant_route === 'ketepatan waktu po') {
                        $progress = $this->calculateKetepatanWaktuPo($item, $personId);
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
            $progress = ($manualValue / $labaKotor) * 10;
        }

        return round($progress, 1);
    }

    private function calculateRasioBiayaOperasionalTerhadapRevenue($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        $nilaiTarget = (float) $detail->nilai_target;

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
            $rasio = ($manualValue / $labaKotor) * 100;

            $batas = $nilaiTarget;

            $progress = ($batas / $rasio) * 100;
        }

        return round($progress, 1);
    }

    private function calculatePerformaKPIDepartemen($item, $personId)
    {
        $allTargets = targetKPI::with(['detailTargetKPI'])
            ->whereYear('created_at', now()->year)
            ->get();

        $targetsByDivisi = [];

        foreach ($allTargets as $target) {
            $details = $target->detailTargetKPI;
            if (!$details || $details->isEmpty()) {
                continue;
            }

            $divisions = $details->pluck('divisi')->unique()->filter();

            foreach ($divisions as $divisi) {
                if (!isset($targetsByDivisi[$divisi])) {
                    $targetsByDivisi[$divisi] = [];
                }
                $targetsByDivisi[$divisi][] = $target;
            }
        }

        $divisionAverages = [];

        foreach ($targetsByDivisi as $divisi => $targets) {
            $progresses = [];

            foreach ($targets as $item) {
                if ($target->asistant_route === 'performa KPI departemen') {
                    continue;
                }

                $progress = null;

                // Office / GM
                if ($item->asistant_route === 'Kepuasan Pelanggan') {
                    $progress = $this->calculateProgressKepuasanPelanggan($item, $personId);
                } elseif ($item->asistant_route === 'Pemasukan Kotor') {
                    $progress = $this->calculatePemasukanKotor($item, $personId);
                } elseif ($item->asistant_route == 'pemasukan bersih') {
                    $progress = $this->calculatePemasukanBersih($item, $personId);
                } elseif ($item->asistant_route === 'rasio biaya operasional terhadap revenue') {
                    $progress = $this->calculateRasioBiayaOperasionalTerhadapRevenue($item, $personId);
                }
                // CS
                elseif ($item->asistant_route === 'peserta puas dengan pelayanan dan fasilitas training') {
                    $progress = $this->calculatePesertaPuasDenganPelayananDanFasilitasTraining($item, $personId);
                } elseif ($item->asistant_route === 'dorong inovasi pelayanan') {
                    $progress = $this->calculateDorongInovasiPelayanan($item, $personId);
                } elseif ($item->asistant_route === 'penanganan komplain peserta') {
                    $progress = $this->calculatePenangananKomplainPerseta($item, $personId);
                } elseif ($item->asistant_route === 'report persiapan kelas') {
                    $progress = $this->calculateReportPersiapanKelas($item, $personId);
                }
                // Finance
                elseif ($target->asistant_route === 'outstanding') {
                    $progress = $this->calculateOutstanding($target, $personId);
                } elseif ($target->asistant_route === 'inisiatif efisiensi keuangan') {
                    $progress = $this->calculateInisiatifEfisiensiKeuangan($target, $personId);
                } elseif ($target->asistant_route === 'mengurangi manual work dan error') {
                    $progress = $this->calculateMengurangiManualWorkDanError($target, $personId);
                } elseif ($target->asistant_route === 'laporan analisis keuangan') {
                    $progress = $this->calculateLaporanAnalisisKeuangan($target, $personId);
                } elseif ($target->asistant_route === 'pencairan biaya operasional') {
                    $progress = $this->calculatePencairanBiayaOperasional($target, $personId);
                } elseif ($target->asistant_route === 'penyelesaian tagihan tepat waktu') {
                    $progress = $this->calculatePenyelesaianTagihanTepatWaktu($target, $personId);
                } elseif ($target->asistant_route === 'penyelesaian tagihan perusahaan') {
                    $progress = $this->calculatePenyelesaianTagihanPerusahaan($target, $personId);
                }

                // HRD
                elseif ($item->asistant_route === 'pelaksanaan kegiatan karyawan') {
                    $progress = $this->calculatePelaksanaanKegiatanKaryawan($item, $personId);
                } elseif ($item->asistant_route === 'pengeluaran biaya karyawan') {
                    $progress = $this->calculatePengeluaranBiayaKaryawan($item, $personId);
                } elseif ($item->asistant_route === 'administrasi karyawan') {
                    $progress = $this->calculateAdministrasiKaryawan($item, $personId);
                }
                // Driver
                elseif ($target->asistant_route === 'perbaikan kendaraan') {
                    $progress = $this->calculatePerbaikanKendaraan($target, $personId);
                } elseif ($target->asistant_route === 'kontrol pengeluaran transportasi') {
                    $progress = $this->calculateKontrolPengeluaranTransportasi($target, $personId);
                } elseif ($target->asistant_route === 'report kondisi kendaraan') {
                    $progress = $this->calculateReportKondisiKendaraan($target, $personId);
                } elseif ($target->asistant_route === 'feedback kenyamanan berkendara') {
                    $progress = $this->calculateFeedbackKenyamananBerkendara($target, $personId);
                }
                // OB
                elseif ($item->asistant_route === 'feedback kebersihan dan kenyamanan') {
                    $progress = $this->calculateFeedbackKebersihanDanKenyamanan($item, $personId);
                } elseif ($item->asistant_route === 'penyelesaian tugas harian') {
                    $progress = $this->calculatePenyelesaianTugasHarian($item, $personId);
                }
                // ITSM
                elseif ($item->asistant_route === 'kepuasan client ITSM') {
                    $progress = $this->calculateProgressKepuasanClientITSM($item, $personId);
                }
                elseif ($target->asistant_route === 'kepuasan client ITSM') {
                    $progress = $this->calculateProgressKepuasanClientITSM($target, $personId);
                } elseif ($target->asistant_route === 'inovation adaption rate') {
                    $progress = $this->calculateInovationAdaptionRate($target, $personId);
                }
                // Koordinator ITSM
                elseif ($item->asistant_route === 'availability sistem internal kritis') {
                    $progress = $this->calculateAvailabilitySistemInternalKritis($item, $personId);
                } elseif ($item->asistant_route === 'meningkatkan kepuasan dan loyalitas peserta/client') {
                    $progress = $this->calculateMeningkatkanKepuasanDanLoyalitasPeserta($item, $personId);
                }
                // Programmer
                elseif ($item->asistant_route === 'ketepatan waktu penyelesaian fitur') {
                    $progress = $this->calculateProgressKetepatanWaktuPenyelesaianFitur($item, $personId);
                } elseif ($item->asistant_route === 'mengukur kualitas aplikasi agar minim bug') {
                    $progress = $this->calculateMengukurKualitasAplikasiAgarMinimBug($item, $personId);
                }
                // Tim Digital
                elseif ($item->asistant_route === 'konsistensi campaign digital') {
                    $progress = $this->calculateKonsistensiCampaignDigital($item, $personId);
                } elseif ($item->asistant_route === 'efektifitas diital marketing') {
                    $progress = $this->calculateEfektifitasDiitalMarketing($item, $personId);
                }
                // TS
                elseif ($item->asistant_route === 'keberhasilan support memenuhi sla') {
                    $progress = $this->calculateTingkatKeberhasilanSupportMemenuhiSLA($item, $personId);
                } elseif ($item->asistant_route === 'kualitas layanan exam') {
                    $progress = $this->calculateKualitasLayananExam($item, $personId);
                }
                // Education - Instruktur
                elseif ($item->asistant_route === 'kepuasan peserta pelatihan') {
                    $progress = $this->calculateKepuasanPesertaPelatihan($item, $personId);
                } elseif ($item->asistant_route === 'upseling lanjutan materi') {
                    $progress = $this->calculateUpselingLanjutanMateri($item, $personId);
                } elseif ($item->asistant_route === 'sertifikasi kompetensi internal') {
                    $progress = $this->calculateSertifikasiKompetensiInternal($item, $personId);
                } elseif ($item->asistant_route === 'pelatihan kompetensi eksternal') {
                    $progress = $this->calculatePelatihanKompetensiEksternal($item, $personId);
                }
                // Education Manager
                elseif ($item->asistant_route === 'pengembangan kurikulum pelatihan') {
                    $progress = $this->calculatePengembanganKurikulumPelatihan($item, $personId);
                } elseif ($item->asistant_route === 'peningkatan knowledge sharing') {
                    $progress = $this->calculatePeningkatanKnowledgeSharing($item, $personId);
                }
                // Sales & Marketing
                elseif ($target->asistant_route === 'total penjualan setahun') {
                    $progress = $this->calculateTotalPenjualanSetahun($target, $personId);
                }

                //Sales & Marketing
                // SPV Sales
                elseif($target->asistant_route === 'meningkatkan revenue perusahaan') {
                    $progress = $this->calculateMeningkatkanRevenuePerusahaan($target, $personId);
                }

                // ADM Sales
                elseif($target->asistant_route === 'laporan mom') {
                    $progress = $this->calculateLaporanMOM($target);
                }

                // ADM Holding
                elseif($target->asistant_route === 'ketepatan waktu po') {
                    $progress = $this->calculateKetepatanWaktuPo($target);
                }

                if ($progress !== null && is_numeric($progress)) {
                    $progresses[] = $progress;
                }
            }

            if (!empty($progresses)) {
                $divisionAvg = array_sum($progresses) / count($progresses);
                $divisionAverages[] = $divisionAvg;
            }
        }

        if (!empty($divisionAverages)) {
            $finalProgress = array_sum($divisionAverages) / count($divisionAverages);
            return round($finalProgress, 1);
        }

        return 0;
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
            }
        }

        $progress = round($progress, 1);

        return round($progress, 1);
    }

    private function calculatePenangananKomplainPerseta($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail) {
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $komplainData = KomplainPeserta::whereBetween('created_at', [$start, $end])->get();

        $totalData = $komplainData->count();

        if ($totalData === 0) {
            return 0;
        }

        $dataTepatWaktu = 0;

        foreach ($komplainData as $data) {
            if ($data->tanggal_selesai) {
                $createdDate = Carbon::parse($data->created_at);
                $finishedDate = Carbon::parse($data->tanggal_selesai);

                if ($createdDate->format('Y-m-d') === $finishedDate->format('Y-m-d')) {
                    $dataTepatWaktu++;
                }
            }
        }

        $presentase = ($dataTepatWaktu / $totalData) * 100;

        return round($presentase, 1);
    }

    private function calculateReportPersiapanKelas($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        $nilaiTarget = (float) $detail->nilai_target;

        $tahun = (int) ($item->detail_jangka ?? now()->year);

        $totalRkm = RKM::whereYear('tanggal_awal', $tahun)->count();

        $totalTuntas = ChecklistKeperluan::whereYear('created_at', $tahun)->where('materi', 1)->where('kelas', 1)->where('cb', 1)->where('maksi', 1)->where('keperluan_kelas', 1)->count();

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

        if (!$detail) {
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $outstandings = Outstanding::whereBetween('created_at', [$start, $end])->get();

        if ($outstandings->isEmpty()) {
            return 0;
        }

        $totalData = $outstandings->count();

        $tepatTenggat = $outstandings->filter(function ($data) {
            return $data->status_pembayaran == 1
                && $data->tanggal_bayar
                && $data->due_date
                && Carbon::parse($data->tanggal_bayar)->lt(Carbon::parse($data->due_date));
        })->count();

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
            $progress = ($manualValue / $targetValue) * 10;
        }

        return round($progress, 1);
    }

    private function calculateMengurangiManualWorkDanError($item, $personId)
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
            $progress = ($manualValue / $targetValue) * 10;
        }

        return round($progress, 1);
    }

    private function calculateLaporanAnalisisKeuangan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        $nilaiTarget = (float) $detail->nilai_target;

        $progress = 0;

        if (!is_null($detail->manual_value)) {
            $manualValue = (float) $detail->manual_value;

            if ($manualValue > 0) {
                $progress = ($manualValue / $nilaiTarget) * 100;
            }
        }

        $progress = round($progress, 1);

        return round($progress, 1);
    }

    private function calculatePencairanBiayaOperasional($item, $personId)
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

    private function calculatePenyelesaianTagihanPerusahaan($item, $personId)
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

        $query = trackingTagihanPerusahaan::whereBetween('tanggal_perkiraan_mulai', [$start, $end]);

        $totalTagihan = $query->count();

        $tagihanSelesai = (clone $query)
            ->where(function ($q) {
                $q->where('status', 'selesai')
                    ->where('tracking', 'Selesai');
            })
            ->count();

        $progress = $totalTagihan > 0 ? ($tagihanSelesai / $totalTagihan) * 100 : 0;

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

    private function calculatePengeluaranBiayaKaryawan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail) {
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $startOfYear = Carbon::create($tahun, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($tahun, 12, 31)->endOfDay();

        $parts = explode(',', $detail->manual_value ?? '');

        $gaji = (float) ($parts[0] ?? 0);
        $bpjsManual = (float) ($parts[1] ?? 0);
        $rekrutmenManual = (float) ($parts[2] ?? 0);

        $bpjsIds = JenisTunjangan::whereIn('nama_tunjangan', [
            'BPJS Tenaga Kerja',
            'BPJS Kesehatan'
        ])->pluck('id');

        $bpjsBudget = TunjanganKaryawan::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->whereIn('jenis_tunjangan', $bpjsIds)
            ->sum('total');

        $rekrutmenBudget = Kegiatan::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('tipe', 'rekrutment')
            ->sum('realisasi');

        $kegiatanBudget = 0;
        $kegiatanRealisasi = 0;

        $kegiatans = Kegiatan::with('pengajuan_barang.detail')
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('tipe', 'kegiatan')
            ->get();

        foreach ($kegiatans as $kegiatan) {

            $kegiatanRealisasi += (float) $kegiatan->realisasi;

            if ($kegiatan->pengajuan_barang) {
                foreach ($kegiatan->pengajuan_barang as $pengajuan) {

                    if ($pengajuan->detail) {
                        foreach ($pengajuan->detail as $d) {

                            $qty = (float) ($d->qty ?? 0);
                            $harga = (float) ($d->harga ?? 0);

                            $kegiatanBudget += $qty * $harga;
                        }
                    }
                }
            }
        }

        $score = 0;

        if ($gaji > 0) {
            $score++;
        }

        if ($bpjsManual > 0 && $bpjsManual <= $bpjsBudget) {
            $score++;
        }

        if ($rekrutmenManual > 0 && $rekrutmenManual <= $rekrutmenBudget) {
            $score++;
        }

        if ($kegiatanRealisasi > 0 && $kegiatanRealisasi <= $kegiatanBudget) {
            $score++;
        }

        $progress = ($score / 4) * 100;

        return round($progress, 1);
    }
    
    private function calculateAdministrasiKaryawan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (is_null($detail)) {
            return 0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $bulanTuntas = 0;

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $adaTunjangan = TunjanganKaryawan::where('tahun', $tahun)->where('bulan', $bulan)->whereDay('created_at', '<=', 10)->exists();

            if ($adaTunjangan) {
                $bulanTuntas++;
            }
        }

        $progressTunajangan = ($bulanTuntas / 12) * 100;

        return round($progressTunajangan, 1);
    }

    //Driver
    private function calculatePerbaikanKendaraan($item, $personId)
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
        if ($personId !== null) {
            $totalData = PerbaikanKendaraan::whereBetween('created_at', [$start, $end])
                ->where('id_user', $personId)
                ->count();
        } else {
            $totalData = PerbaikanKendaraan::whereBetween('created_at', [$start, $end])->count();
        }

        $dataDiperbaiki = PerbaikanKendaraan::whereBetween('created_at', [$start, $end])
            ->where('status', 'Selesai')
            ->count();

        $presentase = ($dataDiperbaiki / $totalData) * 100;

        return round($presentase, 1);
    }

    private function calculateKontrolPengeluaranTransportasi($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail) {
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        if ($personId !== null) {
            $DataPickup = pickupDriver::whereBetween('created_at', [$start, $end])
                ->whereNotNull('budget')
                ->where('id_karyawan', $personId)
                ->get();

            $totalData = $DataPickup->count();
        } else {
            $DataPickup = pickupDriver::whereBetween('created_at', [$start, $end])
                ->whereNotNull('budget')
                ->get();

            $totalData = $DataPickup->count();
        }

        if ($totalData === 0) {
            return 0;
        }

        $countAman = 0;

        foreach ($DataPickup as $data) {
            $totalBiaya = BiayaTransportasiDriver::where('id_pickup_driver', $data->id)->sum('harga');

            if ($totalBiaya <= $data->budget) {
                $countAman++;
            }
        }

        $presentase = ($countAman / $totalData) * 100;

        return round($presentase, 1);
    }

    private function calculateReportKondisiKendaraan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail) {
            return 0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        $startPeriode = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endPeriode = Carbon::createFromDate($tahun, 12, 31)->endOfDay();
        $hariIni = now()->startOfDay();

        if ($hariIni > $endPeriode) {
            $hariIni = $endPeriode;
        }

        if ($personId !== null) {
            $firstReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startPeriode, $hariIni])
                ->where('user_id', $personId)
                ->whereNotNull('tanggal_pemeriksaan')
                ->orderBy('tanggal_pemeriksaan', 'asc')
                ->first();
        } else {
            $firstReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startPeriode, $hariIni])
                ->whereNotNull('tanggal_pemeriksaan')
                ->orderBy('tanggal_pemeriksaan', 'asc')
                ->first();
        }

        if (!$firstReport) {
            return 0;
        }

        $startMinggu = Carbon::parse($firstReport->tanggal_pemeriksaan)->startOfWeek(Carbon::MONDAY);

        $today = Carbon::now();
        $dayOfWeek = $today->dayOfWeek;

        if ($dayOfWeek < 6) {
            $checkUntil = $today->copy()->subWeek()->endOfWeek(Carbon::SUNDAY);
        } else {
            $checkUntil = $today->endOfDay();
        }

        if ($checkUntil > $endPeriode) {
            $checkUntil = $endPeriode;
        }

        $totalMinggu = ceil($startMinggu->diffInDays($checkUntil) / 7);

        if ($totalMinggu < 1) {
            $totalMinggu = 1;
        }

        $jumlahReportTepat = 0;

        for ($i = 0; $i < $totalMinggu; $i++) {
            $weekStart = $startMinggu->copy()->addWeeks($i)->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            if ($weekEnd > $checkUntil) {
                $weekEnd = $checkUntil;
            }

            if ($personId !== null) {
                $hasReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$weekStart, $weekEnd])
                    ->where('user_id', $personId)
                    ->whereNotNull('tanggal_pemeriksaan')
                    ->exists();
            } else {
                $hasReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$weekStart, $weekEnd])
                    ->whereNotNull('tanggal_pemeriksaan')
                    ->exists();
            }

            if ($hasReport) {
                $jumlahReportTepat++;
            }
        }

        $presentase = ($jumlahReportTepat / $totalMinggu) * 100;

        return round($presentase, 1);
    }
    private function calculateFeedbackKenyamananBerkendara($item, $personId)
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
            $p8 = is_numeric($fb->p8) ? (float) $fb->p8 : 0;

            $avg = ($p8) / 1;
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

    //OB
    private function calculateFeedbackKebersihanDanKenyamanan($item, $personId)
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

    private function calculatePenyelesaianTugasHarian($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0;
        }

        if ($personId !== null) {
            $daftarTugas = KontrolTugas::whereYear('created_at', $tahun)->where('id_karyawan', $personId);
        } else {
            $daftarTugas = KontrolTugas::whereYear('created_at', $tahun);
        }

        $jumlahTugas = $daftarTugas->count();

        $jumlahTugasSelesai = $daftarTugas->where('status', '1')->count();

        $presentase = ($jumlahTugasSelesai / $jumlahTugas) * 100;

        return round($presentase, 1);
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

            // Konversi ke skala 1 - 4
            $skor = 1 + ($totalBaris * 3) / 100;

            $allScores[] = $skor;
        }

        if (empty($allScores)) {
            return 0;
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.0) {
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

        $contentSchedules = ContentSchedule::whereBetween('upload_date', [$start, $end])
            ->whereNotNull('upload_date')
            ->get();

        if ($contentSchedules->isEmpty()) {
            return 0;
        }

        $weeklyCounts = [];
        foreach ($contentSchedules as $schedule) {
            $weekKey = Carbon::parse($schedule->upload_date)->format('o-\WW');
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

    private function calculateEfektifitasDiitalMarketing($item, $personId)
    {
        $details = $item->detailTargetKPI;

        if ($details->isEmpty()) {
            return 0;
        }

        $tahun = (int) $details->first()->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 1) {
            $tahun = now()->year;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $dataColaborator = Colaborator::whereBetween('created_at', [$start, $end])->get();

        $totalQuarters = 4;

        $quartersWith = [];

        foreach ($dataColaborator as $colab) {
            $month = $colab->created_at->month;
            $quarter = ceil($month / 3);

            $quartersWith[$quarter] = true;
        }

        $filledQuartersCount = count($quartersWith);

        $konsistensiPersen = ($filledQuartersCount / $totalQuarters) * 100;

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

        $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)->pluck('id_karyawan')->unique()->toArray();

        if (empty($idKaryawans)) {
            return 0;
        }

        $picNames = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->map(fn($nama) => explode(' ', trim($nama))[0] ?? '')->filter()->unique()->values()->toArray();

        if (empty($picNames)) {
            return 0;
        }

        $errorQuery = Tickets::whereBetween('created_at', [$start, $end])
            ->where('kategori', 'Error (Aplikasi)')
            ->where('keperluan', 'Programming')
            ->whereNotNull('tanggal_selesai');

        $requestQuery = Tickets::whereBetween('created_at', [$start, $end])->where('kategori', 'Request');
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
                if (strlen($ticket->tanggal_selesai) > 10) {
                    // sudah datetime
                    $endAt = Carbon::parse($ticket->tanggal_selesai, 'Asia/Jakarta');
                } else {
                    // hanya date
                    $endAt = Carbon::parse($ticket->tanggal_selesai . ' ' . $ticket->jam_selesai, 'Asia/Jakarta');
                }
                $durasiJam = $this->hitungJamKerja($startAt, $endAt);

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

        $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)->pluck('id_karyawan')->unique()->toArray();

        $picNames = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->map(fn($nama) => explode(' ', $nama)[0] ?? '')->filter()->unique()->values()->toArray();

        if ($personId !== null) {
            $picJabatan = karyawan::whereIn('id', $idKaryawans)->pluck('jabatan')->unique()->map(fn($n) => ucwords(strtolower($n)))->values()->toArray();
        } else {
            $targetJabatanList = $details->pluck('jabatan')->unique()->toArray();
            $picJabatan = karyawan::whereIn('jabatan', $targetJabatanList)->pluck('jabatan')->unique()->map(fn($n) => ucwords(strtolower($n)))->values()->toArray();
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
            if (strlen($ticket->tanggal_selesai) > 10) {
                // sudah datetime
                $endAt = Carbon::parse($ticket->tanggal_selesai, 'Asia/Jakarta');
            } else {
                // hanya date
                $endAt = Carbon::parse($ticket->tanggal_selesai . ' ' . $ticket->jam_selesai, 'Asia/Jakarta');
            }
            $actualHours = $this->hitungJamKerja($startAt, $endAt);
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

        $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)->pluck('id_karyawan')->unique()->toArray();

        if (empty($idKaryawans)) {
            return 0;
        }

        $picNames = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->map(fn($nama) => explode(' ', trim($nama))[0] ?? '')->filter()->unique()->values()->toArray();

        if (empty($picNames)) {
            return 0;
        }

        if ($personId !== null) {
            $picJabatan = karyawan::whereIn('id', $idKaryawans)->pluck('jabatan')->unique()->map(fn($n) => ucwords(strtolower($n)))->values()->toArray();
        } else {
            $targetJabatanList = $details->pluck('jabatan')->unique()->toArray();
            $picJabatan = karyawan::whereIn('jabatan', $targetJabatanList)->pluck('jabatan')->unique()->map(fn($n) => ucwords(strtolower($n)))->values()->toArray();
        }

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

        $ticketQuery = DB::table('tickets')
            ->select('created_at', 'tanggal_response', 'jam_response', 'tanggal_selesai', 'jam_selesai', 'pic', 'keperluan')
            ->whereIn('keperluan', $keperluanPatterns)
            ->whereNotNull('tanggal_selesai')
            ->whereBetween('created_at', [$start, $end]);

        if ($personId !== null) {
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

    private function calculateKualitasLayananExam($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail) {
            return 0.0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0.0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = PenilaianExam::selectRaw('id_rkm, AVG(nilai_emote) as nilai')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('id_rkm')
            ->groupBy('id_rkm');

        $data = $query->get();

        $totalPenilaian = $data->count();

        if ($totalPenilaian == 0) {
            return 0.0;
        }

        $qualifiedPenilaian = $data
            ->filter(function ($item) {
                return $item->nilai >= 3.5;
            })
            ->count();

        $progress = ($qualifiedPenilaian / $totalPenilaian) * 100;

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

        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

        foreach ($feedbacks as $fb) {
            $f1 = is_numeric($fb->F1) ? (float) $fb->F1 : 0;
            $f2 = is_numeric($fb->F2) ? (float) $fb->F2 : 0;
            $f3 = is_numeric($fb->F3) ? (float) $fb->F3 : 0;
            $f4 = is_numeric($fb->F4) ? (float) $fb->F4 : 0;
            $f5 = is_numeric($fb->F5) ? (float) $fb->F5 : 0;

            $avg = ($f1 + $f2 + $f3 + $f4 + $f5) / 5;

            // Pastikan tetap di skala 1 - 4
            $avg = min(4, max(1, $avg));

            $allScores[] = $avg;
        }

        if (empty($allScores)) {
            return 0;
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $skor) {
            if ($skor >= 3.0) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;

        return round($progress, 1);
    }

    private function calculateInovationAdaptionRate($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();
        $nilaiTarget = (float) $detail->nilai_target;

        $tahun = (int) ($detail->detail_jangka ?? now()->year);

        $totalIde = IdeInovasi::whereYear('created_at', $tahun)->count();

        if ($nilaiTarget <= 0) {
            return 0;
        }

        $progress = ($totalIde / $totalIde) * 100;

        return round($progress, 1);
    }

    //Education
    //Instruktur
    private function calculateKepuasanPesertaPelatihan($item, $personId)
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

        if ($personId !== null) {
            $kodeKaryawan = karyawan::where('id', $personId)->first();

            if ($kodeKaryawan) {
                $rkmList = RKM::where('instruktur_key', $kodeKaryawan->kode_karyawan)->orWhere('instruktur_key2', $kodeKaryawan->kode_karyawan)->orWhere('asisten_key', $kodeKaryawan->kode_karyawan)->get();

                if (!$rkmList->isEmpty()) {
                    $rkmIds = $rkmList->pluck('id_rkm')->filter()->toArray();

                    $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])
                        ->whereIn('id_rkm', $rkmIds)
                        ->get();

                    foreach ($feedbacks as $fb) {
                        $rkm = $rkmList->firstWhere('id_rkm', $fb->id_rkm);

                        if (!$rkm) {
                            continue;
                        }

                        $avg = 0;

                        if ($rkm->instruktur_key == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float) ($fb->I1 ?? 0), (float) ($fb->I2 ?? 0), (float) ($fb->I3 ?? 0), (float) ($fb->I4 ?? 0), (float) ($fb->I5 ?? 0), (float) ($fb->I6 ?? 0), (float) ($fb->I7 ?? 0), (float) ($fb->I8 ?? 0)];
                            $avg = array_sum($scores) / 8;
                        } elseif ($rkm->instruktur_key2 == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float) ($fb->I1b ?? 0), (float) ($fb->I2b ?? 0), (float) ($fb->I3b ?? 0), (float) ($fb->I4b ?? 0), (float) ($fb->I5b ?? 0), (float) ($fb->I6b ?? 0), (float) ($fb->I7b ?? 0), (float) ($fb->I8b ?? 0)];
                            $avg = array_sum($scores) / 8;
                        } elseif ($rkm->asisten_key == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float) ($fb->I1as ?? 0), (float) ($fb->I2as ?? 0), (float) ($fb->I3as ?? 0), (float) ($fb->I4as ?? 0), (float) ($fb->I5as ?? 0), (float) ($fb->I6as ?? 0), (float) ($fb->I7as ?? 0), (float) ($fb->I8as ?? 0)];
                            $avg = array_sum($scores) / 8;
                        }

                        $avg = min(4, max(1, $avg));
                        $allScores[] = $avg;
                    }
                }
            }
        } else {
            $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

            foreach ($feedbacks as $fb) {
                $i1 = (float) ($fb->I1 ?? 0);
                $i2 = (float) ($fb->I2 ?? 0);
                $i3 = (float) ($fb->I3 ?? 0);
                $i4 = (float) ($fb->I4 ?? 0);
                $i5 = (float) ($fb->I5 ?? 0);
                $i6 = (float) ($fb->I6 ?? 0);
                $i7 = (float) ($fb->I7 ?? 0);
                $i8 = (float) ($fb->I8 ?? 0);
                $sumBase = $i1 + $i2 + $i3 + $i4 + $i5 + $i6 + $i7 + $i8;

                $i1b = (float) ($fb->I1b ?? 0);
                $i2b = (float) ($fb->I2b ?? 0);
                $i3b = (float) ($fb->I3b ?? 0);
                $i4b = (float) ($fb->I4b ?? 0);
                $i5b = (float) ($fb->I5b ?? 0);
                $i6b = (float) ($fb->I6b ?? 0);
                $i7b = (float) ($fb->I7b ?? 0);
                $i8b = (float) ($fb->I8b ?? 0);
                $sumB = $i1b + $i2b + $i3b + $i4b + $i5b + $i6b + $i7b + $i8b;

                if ($sumB > 0) {
                    $totalSum = $sumBase + $sumB;
                    $totalItem = 16;
                } else {
                    $totalSum = $sumBase;
                    $totalItem = 8;
                }

                if ($totalItem > 0) {
                    $avg = $totalSum / $totalItem;
                    $avg = min(4, max(1, $avg));
                    $allScores[] = $avg;
                }
            }
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

    private function calculateUpselingLanjutanMateri($item, $personId): float
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail) {
            return 0.0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0.0;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();
        if ($personId !== null) {
            $kodeKaryawan = karyawan::where('id', $personId)->first();

            $rkmQuery = RKM::whereBetween('created_at', [$start, $end])
                ->where('instruktur_key', $kodeKaryawan->kode_karyawan)
                ->where('tanggal_akhir', '<', now());
        } else {
            $rkmQuery = RKM::whereBetween('created_at', [$start, $end])->where('tanggal_akhir', '<', now());
        }

        $totalData = $rkmQuery->count();

        if ($totalData === 0) {
            return 0.0;
        }

        $rkmIds = $rkmQuery->pluck('id');

        $totalRekomendasi = RekomendasiLanjutan::whereIn('id_rkm', $rkmIds)->count();

        $presentase = ($totalRekomendasi / $totalData) * 100;

        return round($presentase, 1);
    }

    private function calculateSertifikasiKompetensiInternal($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail) {
            return 0.0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0.0;
        }

        $startYear = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endYear = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = detailPersonKPI::where('detailTargetKey', $detail->id);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $detailPersons = $query->get();
        $totalData = $detailPersons->count();

        if ($totalData === 0) {
            return 0.0;
        }

        $countAchieved = 0;

        foreach ($detailPersons as $personItem) {
            $validSertifikasi = Sertifikasi::where('user_id', $personItem->id_karyawan)
                ->where('tanggal_berlaku_dari', '<=', $endYear)
                ->where(function ($q) use ($startYear) {
                    $q->where('tanggal_berlaku_sampai', '>=', $startYear)->orWhereNull('tanggal_berlaku_sampai');
                })
                ->count();

            if ($personId !== null) {
                $countAchieved += $validSertifikasi;
            } else {
                if ($validSertifikasi > 0) {
                    $countAchieved += 1;
                }
            }
        }

        if ($personId !== null) {
            $progress = min(100, $countAchieved * 100);
        } else {
            $progress = ($countAchieved / $totalData) * 100;
        }

        return round($progress, 1);
    }

    private function calculatePelatihanKompetensiEksternal($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail) {
            return 0.0;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return 0.0;
        }

        $startYear = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endYear = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = detailPersonKPI::where('detailTargetKey', $detail->id);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $detailPersons = $query->get();
        $totalData = $detailPersons->count();

        if ($totalData === 0) {
            return 0.0;
        }

        $countAchieved = 0;

        foreach ($detailPersons as $personItem) {
            $validSertifikasi = Pelatihan::where('user_id', $personItem->id_karyawan)
                ->whereYear('tanggal_selesai', [$startYear, $endYear])
                ->count();

            if ($personId !== null) {
                $countAchieved += $validSertifikasi;
            } else {
                if ($validSertifikasi > 0) {
                    $countAchieved += 1;
                }
            }
        }

        if ($personId !== null) {
            $progress = min(100, $countAchieved * 100);
        } else {
            $progress = ($countAchieved / $totalData) * 100;
        }

        return round($progress, 1);
    }

    //Manager Education
    private function calculatePengembanganKurikulumPelatihan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return 0.0;
        }

        $dataMateri = Materi::whereYear('created_at', $tahun)->get();

        $totalBulanDalamTahun = 12;

        $bulanYangAdaMateri = $dataMateri
            ->pluck('created_at')
            ->map(function ($date) {
                return Carbon::parse($date)->month;
            })
            ->unique()
            ->count();

        if ($totalBulanDalamTahun == 0) {
            return 0.0;
        }

        $progress = ($bulanYangAdaMateri / $totalBulanDalamTahun) * 100;

        return round($progress, 1);
    }

    private function calculatePeningkatanKnowledgeSharing($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return 0.0;
        }

        $dataMateri = ActivityInstruktur::whereYear('activity_date', $tahun)->where('activity_type', 'Sharing Knowledge')->get();

        $totalMingguDalamTahun = Carbon::create($tahun, 1, 1)->weeksInYear;

        $mingguYangSudahJalan = [];

        foreach ($dataMateri as $activity) {
            $nomorMinggu = Carbon::parse($activity->activity_date)->week;

            $mingguYangSudahJalan[$nomorMinggu] = true;
        }

        $jumlahMingguTerisi = count($mingguYangSudahJalan);

        if ($totalMingguDalamTahun == 0) {
            $progress = 0.0;
        } else {
            $progress = ($jumlahMingguTerisi / $totalMingguDalamTahun) * 100;
        }

        if ($progress > 100) {
            $progress = 100;
        }

        return round($progress, 1);
    }

    //Sales & Marketing
    //Sales
    private function calculateTargetPenjualanTahunan($item, $personId)
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

        $totalSales = RKM::where('status', '0')
            ->whereYear('tanggal_awal', $tahun);

        if ($personId !== null) {

            $personId = detailPersonKPI::where('detailTargetKey', $detail->id)->first()?->id_karyawan;

            $kodeKaryawan = Karyawan::where('id', $personId)->value('kode_karyawan');
            if (!$kodeKaryawan) {
                return 0;
            }

            $totalSales = $totalSales->where('sales_key', $kodeKaryawan)
                ->select(DB::raw('SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_sales'))
                ->value('total_sales');

        } else {

            $totalSales = $totalSales
                ->select(DB::raw('SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total_sales'))
                ->value('total_sales');
        }

        $progress = (float) ($totalSales ?? 0);
    }

    private function calculateMeningkatkanRevenuePerusahaan($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return 0.0;
        }

        $peluang = Peluang::with('rkm.perhitunganNetSales')->whereYear('created_at', $tahun)->get();

        $progress = 0;

        foreach ($peluang as $p) {
            $kotor = $p->harga * $p->pax;

            $perhitungan = $p->rkm->perhitunganNetSales;

            $totalBiaya = 0;
            if ($perhitungan) {
                foreach ($p->rkm->perhitunganNetSales as $perhitungan) {
                    $totalBiaya += $perhitungan->transportasi
                        + $perhitungan->akomodasi_peserta
                        + $perhitungan->akomodasi_tim
                        + $perhitungan->fresh_money
                        + $perhitungan->entertaint
                        + $perhitungan->souvenir
                        + $perhitungan->cashback
                        + $perhitungan->sewa_laptop;
                }
            }

            $bersih = $kotor - $totalBiaya;

            $progress += $bersih;
        }

        return round($progress, 1);
    }

    private function calculateLaporanMOM($item)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return 0.0;
        }

        $momCount = LaporanHarianSales::whereYear('created_at', $tahun)->count();
        $progress = $momCount/$momCount * 100;

        return round($progress, 1);
    }

    private function calculateKetepatanWaktuPo($item)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail) {
            return 0.0;
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return 0.0;
        }

        $pos = NomorModul::with('moduls')
            ->whereYear('created_at', $tahun)
            ->get();

        if ($pos->isEmpty()) {
            return 0.0;
        }

        $totalPercent = 0;
        $count = 0;

        foreach ($pos as $po) {

            if (!$po->uploaded) {
                continue;
            }

            $uploaded = Carbon::parse($po->uploaded)->startOfDay();

            foreach ($po->moduls as $modul) {

                if (!$modul->awal_training) {
                    continue;
                }

                $awalTraining = Carbon::parse($modul->awal_training)->startOfDay();

                $daysBefore = $awalTraining->diffInDays($uploaded); // selalu positif jika uploaded sebelum training

                if ($daysBefore >= 7) {
                    $percent = 100;
                } elseif ($daysBefore > 0) {
                    $percent = ($daysBefore * 100) / 7;
                } else {
                    $percent = 0;
                }

                $totalPercent += $percent;
                $count++;
            }
        }

        if ($count === 0) {
            return 0.0;
        }

        $progress = $totalPercent / $count;

        return round($progress, 1);
    }
    //detail_target
    public function detailData(Request $request)
    {
        $idTarget = $request->id;
        $personId = $request->idUser ?? null;

        $query = targetKPI::with(['karyawan', 'detailTargetKPI.detailPersonKPI.karyawan'])->where('id', $idTarget);

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

                    $dataCalculation = $this->getCalculationByRoute($itemDetail, $personId);

                    $dataOutput = [
                        'pembuat' => $itemDetail->karyawan->nama_lengkap,
                        'judul' => $itemDetail->judul,
                        'condition' => $itemDetail->asistant_route,
                        'deskripsi' => $itemDetail->deskripsi,
                        'jabatan_kpi' => $detail->jabatan,
                        'divisi_kpi' => $detail->divisi,
                        'karyawan' => $itemDetail->detailTargetKPI
                            ->flatMap(function ($detailItem) {
                                return $detailItem->detailPersonKPI->map(function ($person) {
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
                        'data_detail' => $dataCalculation,
                    ];

                    return ['data' => $dataOutput];
                })
                ->filter()
                ->values(),
        ];

        return response()->json($data);
    }

    private function getCalculationByRoute($itemDetail, $personId)
    {
        $route = $itemDetail->asistant_route;

        // --- Target Detail Office - GM ---
        if ($route === 'Kepuasan Pelanggan') {
            return $this->calculateProgressKepuasanPelangganDetail($itemDetail);
        } elseif ($route === 'Pemasukan Kotor') {
            return $this->calculatePemasukanKotorDetail($itemDetail);
        } elseif ($route === 'pemasukan bersih') {
            return $this->calculatePemasukanBersihDetail($itemDetail);
        } elseif ($route === 'rasio biaya operasional terhadap revenue') {
            return $this->calculateRasioBiayaOperasionalTerhadapRevenueDetail($itemDetail);
        } elseif ($route === 'performa KPI departemen') {
            return $this->calculatePerformaKPIDepartemenDetail($itemDetail, $personId);
        }

        // --- CS ---
        elseif ($route === 'peserta puas dengan pelayanan dan fasilitas training') {
            return $this->calculatePesertaPuasDenganPelayananDanFasilitasTrainingDetail($itemDetail);
        } elseif ($route === 'dorong inovasi pelayanan') {
            return $this->calculateDorongInovasiPelayananDetail($itemDetail);
        } elseif ($route === 'penanganan komplain peserta') {
            return $this->calculatePenangananKomplainPersetaDetail($itemDetail);
        } elseif ($route === 'report persiapan kelas') {
            return $this->calculateReportPersiapanKelasDetail($itemDetail, $personId);
        }

        // --- Finance ---
        elseif ($route === 'outstanding') {
            return $this->calculateOutstandingDetail($itemDetail);
        } elseif ($route === 'inisiatif efisiensi keuangan') {
            return $this->calculateInisiatifEfisiensiKeuanganDetail($itemDetail);
        } elseif ($route === 'mengurangi manual work dan error') {
            return $this->calculateMengurangiManualWorkDanErrorDetail($itemDetail);
        } elseif ($route === 'laporan analisis keuangan') {
            return $this->calculateLaporanAnalisisKeuanganDetail($itemDetail);
        } elseif ($route === 'penyelesaian tagihan perusahaan') {
            return $this->calculatePenyelesaianTagihanPerusahaanDetail($itemDetail, $personId);
        }

        // --- HRD ---
        elseif ($route === 'pelaksanaan kegiatan karyawan') {
            return $this->calculatePelaksanaanKegiatanKaryawanDetail($itemDetail);
        } elseif ($route === 'pengeluaran biaya karyawan') {
            return $this->calculatePengeluaranBiayaKaryawanDetail($itemDetail);
        } elseif ($route === 'administrasi karyawan') {
            return $this->calculateAdministrasiKaryawanDetail($itemDetail, $personId);
        }

        // --- Driver ---
        elseif ($route === 'perbaikan kendaraan') {
            return $this->calculatePerbaikanKendaraanDetail($itemDetail, $personId);
        } elseif ($route === 'kontrol pengeluaran transportasi') {
            return $this->calculateKontrolPengeluaranTransportasiDetail($itemDetail, $personId);
        } elseif ($route === 'report kondisi kendaraan') {
            return $this->calculateReportKondisiKendaraanDetail($itemDetail, $personId);
        } elseif ($route === 'feedback kenyamanan berkendara') {
            return $this->calculateFeedbackKenyamananBerkendaraDetail($itemDetail, $personId);
        }

        // --- OB ---
        elseif ($route === 'feedback kebersihan dan kenyamanan') {
            return $this->calculateFeedbackKebersihanDanKenyamananDetail($itemDetail);
        } elseif ($route === 'penyelesaian tugas harian') {
            return $this->calculatePenyelesaianTugasHarianDetail($itemDetail, $personId);
        }

        // --- ITSM ---
        elseif ($route === 'kepuasan client ITSM') {
            return $this->calculateProgressKepuasanClientITSMDetail($itemDetail);
        } elseif ($route === 'inovation adaption rate') {
            return $this->calculateInovationAdaptionRateDetail($itemDetail, $personId);
        } elseif ($route === 'availability sistem internal kritis') {
            return $this->calculateAvailabilitySistemInternalKritisDetail($itemDetail);
        } elseif ($route === 'meningkatkan kepuasan dan loyalitas peserta/client') {
            return $this->calculateMeningkatkanKepuasanDanLoyalitasPesertaDetail($itemDetail);
        }

        // --- Programmer ---
        elseif ($route === 'ketepatan waktu penyelesaian fitur') {
            return $this->calculateProgressKetepatanWaktuPenyelesaianFiturDetail($itemDetail, $personId);
        } elseif ($route === 'mengukur kualitas aplikasi agar minim bug') {
            return $this->calculateMengukurKualitasAplikasiAgarMinimBugDetail($itemDetail, $personId);
        }

        // --- Tim Digital ---
        elseif ($route === 'konsistensi campaign digital') {
            return $this->calculateKonsistensiCampaignDigitalDetail($itemDetail);
        } elseif ($route === 'efektifitas diital marketing') {
            return $this->calculateEfektifitasDiitalMarketingDetail($itemDetail, $personId);
        }

        // --- TS ---
        elseif ($route === 'keberhasilan support memenuhi sla') {
            return $this->calculateTingkatKeberhasilanSupportMemenuhiSLADetail($itemDetail, $personId);
        } elseif ($route === 'kualitas layanan exam') {
            return $this->calculateKualitasLayananExamDetail($itemDetail, $personId);
        }

        // --- Education (Instruktur) ---
        elseif ($route === 'kepuasan peserta pelatihan') {
            return $this->calculateKepuasanPesertaPelatihanDetail($itemDetail, $personId);
        } elseif ($route === 'upseling lanjutan materi') {
            return $this->calculateUpselingLanjutanMateriDetail($itemDetail, $personId);
        } elseif ($route === 'sertifikasi kompetensi internal') {
            return $this->calculateSertifikasiKompetensiInternalDetail($itemDetail, $personId);
        } elseif ($route === 'pelatihan kompetensi eksternal') {
            return $this->calculatePelatihanKompetensiEksternalDetail($itemDetail, $personId);
        }

        // --- Education Manager ---
        elseif ($route === 'pengembangan kurikulum pelatihan') {
            return $this->calculatePengembanganKurikulumPelatihanDetail($itemDetail);
        } elseif ($route === 'peningkatan knowledge sharing') {
            return $this->calculatePeningkatanKnowledgeSharingDetail($itemDetail);
        }

        //Sales & Marketing
        // SPV Sales
        elseif ($itemDetail->asistant_route === 'meningkatkan revenue perusahaan') {
            return $this->calculateMeningkatkanRevenuePerusahaanDetail($itemDetail);
        }

        // ADM Sales
        elseif($itemDetail->asistant_route === 'laporan mom') {
            return $this->calculateLaporanMOMDetail($itemDetail);
        }

        // ADM Holding
        elseif($itemDetail->asistant_route === 'ketepatan waktu po') {
            return $this->calculateKetepatanWaktuPoDetail($itemDetail);
        }

        return null;
    }

    private function calculateTenggatWaktu($detail)
    {
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
        return $tenggat_waktu;
    }

    public function getChartStatistics(Request $request)
    {
        $user = auth()->user();
        $userJabatan = $user ? trim($user->jabatan) : null;

        $allowedJabatans = null;

        if ($userJabatan) {
            $jLower = strtolower($userJabatan);

            if (in_array($jLower, ['gm', 'hrd', 'direktur utama', 'direktur'])) {
                $allowedJabatans = null;
            } elseif ($jLower === 'koordinator itsm') {
                $allowedJabatans = ['Programmer', 'Tim Digital', 'Technical Support', 'Koordinator ITSM'];
            } elseif ($jLower === 'education manager') {
                $allowedJabatans = ['Instruktur', 'Education Manager'];
            } elseif ($jLower === 'spv sales') {
                $allowedJabatans = ['SPV Sales', 'Sales'];
            } else {
                $allowedJabatans = [$userJabatan];
            }
        }

        $requestJabatan = $request->jabatan ? trim($request->jabatan) : null;
        $finalJabatanFilter = null;

        if ($allowedJabatans === null) {
            $finalJabatanFilter = $requestJabatan ? [$requestJabatan] : null;
        } else {
            if ($requestJabatan) {
                $isPermitted = false;
                foreach ($allowedJabatans as $allowed) {
                    if (strtolower($allowed) === strtolower($requestJabatan)) {
                        $isPermitted = true;
                        break;
                    }
                }

                if ($isPermitted) {
                    $finalJabatanFilter = [$requestJabatan];
                } else {
                    $finalJabatanFilter = $allowedJabatans;
                }
            } else {
                $finalJabatanFilter = $allowedJabatans;
            }
        }

        $tahunFilter = $request->tahun ?? date('Y');
        $idTargetFilter = $request->id_target ?? null;

        $query = targetKPI::with(['karyawan', 'detailTargetKPI.detailPersonKPI.karyawan']);

        if ($idTargetFilter) {
            $query->where('id', $idTargetFilter);
        }

        $query->whereYear('created_at', $tahunFilter);

        if ($finalJabatanFilter !== null && !empty($finalJabatanFilter)) {
            $query->whereHas('detailTargetKPI', function ($q) use ($finalJabatanFilter) {
                if (count($finalJabatanFilter) > 1) {
                    $q->whereIn('jabatan', $finalJabatanFilter);
                } else {
                    $q->where('jabatan', $finalJabatanFilter[0]);
                }
            });
        }

        $targets = $query->get();

        $allTargetData = [];
        $monthlyAggregates = [];
        $jabatanAggregates = [];
        $jabatanMonthlyAggregates = [];

        $stats = [
            'total_targets' => 0,
            'completed_targets' => 0,
            'achieved_targets' => 0,
            'in_progress_targets' => 0,
        ];

        foreach ($targets as $target) {
            $detail = $target->detailTargetKPI->first();

            if (!$detail || !$detail->nilai_target || (float) $detail->nilai_target <= 0) {
                continue;
            }

            if ($finalJabatanFilter !== null) {
                $isDetailAllowed = false;
                foreach ($finalJabatanFilter as $allowed) {
                    if (strtolower($detail->jabatan) === strtolower($allowed)) {
                        $isDetailAllowed = true;
                        break;
                    }
                }
                if (!$isDetailAllowed) {
                    continue;
                }
            }

            $calculationData = $this->getCalculationByRoute($target, null);
            if (!$calculationData || !isset($calculationData['progress'])) {
                continue;
            }

            $progress = (float) $calculationData['progress'];
            $nilaiTarget = (float) $detail->nilai_target;
            $jabatan = $detail->jabatan ?? 'Unknown';
            $monthlyData = $calculationData['monthly_data'] ?? [];

            $stats['total_targets']++;

            if ($progress >= 100) {
                $stats['completed_targets']++;
            }

            if ($progress >= $nilaiTarget && $nilaiTarget > 0) {
                $stats['achieved_targets']++;
            } else {
                $stats['in_progress_targets']++;
            }

            $allTargetData[] = [
                'id' => $target->id,
                'judul' => $target->judul,
                'jabatan' => $jabatan,
                'progress' => $progress,
                'target' => $nilaiTarget,
                'gap' => $calculationData['gap'] ?? 0,
                'asistant_route' => $target->asistant_route,
            ];

            if (!isset($jabatanAggregates[$jabatan])) {
                $jabatanAggregates[$jabatan] = [];
            }
            $jabatanAggregates[$jabatan][] = $progress;

            foreach ($monthlyData as $monthKey => $avgScore) {
                // Filter bulan jika ada request bulan
                if ($request->bulan) {
                    $monthPart = (int) explode('-', $monthKey)[1];
                    if ($monthPart !== (int) $request->bulan) {
                        continue;
                    }
                }

                if (!isset($monthlyAggregates[$monthKey])) {
                    $monthlyAggregates[$monthKey] = [];
                }
                $monthlyAggregates[$monthKey][] = $avgScore;

                if (!isset($jabatanMonthlyAggregates[$jabatan])) {
                    $jabatanMonthlyAggregates[$jabatan] = [];
                }
                if (!isset($jabatanMonthlyAggregates[$jabatan][$monthKey])) {
                    $jabatanMonthlyAggregates[$jabatan][$monthKey] = [];
                }
                $jabatanMonthlyAggregates[$jabatan][$monthKey][] = $avgScore;
            }
        }

        $monthlyChart = [];
        foreach ($monthlyAggregates as $month => $scores) {
            if (!empty($scores)) {
                $monthlyChart[$month] = round(array_sum($scores) / count($scores), 1);
            }
        }
        ksort($monthlyChart);

        $jabatanChart = [];
        foreach ($jabatanAggregates as $jabatan => $scores) {
            if (!empty($scores)) {
                $jabatanChart[$jabatan] = round(array_sum($scores) / count($scores), 1);
            }
        }

        $jabatanMonthlyChart = [];
        foreach ($jabatanMonthlyAggregates as $jabatan => $months) {
            foreach ($months as $month => $scores) {
                if (!empty($scores)) {
                    if (!isset($jabatanMonthlyChart[$jabatan])) {
                        $jabatanMonthlyChart[$jabatan] = [];
                    }
                    $jabatanMonthlyChart[$jabatan][$month] = round(array_sum($scores) / count($scores), 1);
                }
            }
        }

        $allProgressValues = [];
        foreach ($jabatanAggregates as $scores) {
            $allProgressValues = array_merge($allProgressValues, $scores);
        }
        $overallAverage = !empty($allProgressValues) ? round(array_sum($allProgressValues) / count($allProgressValues), 1) : 0;

        $yearlyMonthlyAverage = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthKey = "{$tahunFilter}-" . str_pad($m, 2, '0', STR_PAD_LEFT);
            $yearlyMonthlyAverage[$monthKey] = $monthlyChart[$monthKey] ?? 0;
        }

        $responseData = [
            'filters' => [
                'jabatan' => $requestJabatan,
                'bulan' => $request->bulan,
                'tahun' => (int) $tahunFilter,
                'user_scope' => $userJabatan,
            ],
            'summary' => [
                'overall_average' => $overallAverage,
                'total_targets' => $stats['total_targets'],
                'completed_targets' => $stats['completed_targets'],
                'achieved_targets' => $stats['achieved_targets'],
                'in_progress_targets' => $stats['in_progress_targets'],
                'completion_rate' => $stats['total_targets'] > 0 ? round(($stats['completed_targets'] / $stats['total_targets']) * 100, 1) : 0,
                'achievement_rate' => $stats['total_targets'] > 0 ? round(($stats['achieved_targets'] / $stats['total_targets']) * 100, 1) : 0,
            ],
            'charts' => [
                'monthly_trend' => $yearlyMonthlyAverage,
                'by_jabatan' => $jabatanChart,
                'jabatan_monthly' => $jabatanMonthlyChart,
            ],
            'targets_detail' => $allTargetData,
        ];

        return response()->json($responseData);
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

        $firstDetail = optional($details->first());
        $tahun = (int) $firstDetail->detail_jangka;
        $nilaiTarget = (float) $firstDetail->nilai_target;

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

        $sales = RKM::where('status', '0')
            ->whereYear('tanggal_awal', $tahun)
            ->select(DB::raw('tanggal_awal, SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total'))
            ->groupBy('tanggal_awal')
            ->get();

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
            $monthlyData[$month] = array_sum($totals); 
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $gapRaw = $progress - $nilaiTarget;
        
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = 0;
        $below = 0;

        if ($progress >= $nilaiTarget) {
            $above = $progress - $nilaiTarget; 
            $below = 0;
        } else {
            $above = 0;
            $below = $nilaiTarget - $progress;
        }

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $above,
                'below' => $below
            ],
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

        if ($labaKotor <= 0) {
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

            if ($manualValue > 0 && $labaKotor >= $manualValue) {
                $progress = ($manualValue / $labaKotor) * 100;
            }
        }

        $progress = round($progress, 1);

        if ($progress < $nilaiTarget) {
            $gapRaw = $progress - $nilaiTarget;
            $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        } else {
            $gap = 0;
        }

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
                $rasio = ($manualValue / $labaKotor) * 100;

                $batas = $nilaiTarget;

                $progress = ($batas / $rasio) * 100;
            }
        }

        $progress = round($progress, 1);

        if ($progress < $nilaiTarget) {
            $gapRaw = $progress - $nilaiTarget;
            $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        } else {
            $gap = 0;
        }

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

    private function calculatePerformaKPIDepartemenDetail($itemDetail, $personId)
    {
        $allTargets = targetKPI::with(['detailTargetKPI'])
            ->whereYear('created_at', now()->year)
            ->get();

        $targetsByDivisi = [];

        foreach ($allTargets as $target) {
            $details = $target->detailTargetKPI;
            if (!$details || $details->isEmpty()) {
                continue;
            }

            $divisions = $details->pluck('divisi')->unique()->filter();

            foreach ($divisions as $divisi) {
                if (!isset($targetsByDivisi[$divisi])) {
                    $targetsByDivisi[$divisi] = [];
                }
                $targetsByDivisi[$divisi][] = $target;
            }
        }

        $divisionAverages = [];
        $targetValues = [];

        foreach ($targetsByDivisi as $divisi => $items) {
            $progresses = [];

            foreach ($items as $item) {
                if ($item->asistant_route === 'performa KPI departemen') {
                    continue;
                }

                $detail = $item->detailTargetKPI->first();
                if ($detail && !is_null($detail->nilai_target)) {
                    $targetValues[] = (float) $detail->nilai_target;
                }

                $progress = null;

                // Office / GM
                if ($item->asistant_route === 'Kepuasan Pelanggan') {
                    $progress = $this->calculateProgressKepuasanPelanggan($item, $personId);
                } elseif ($item->asistant_route === 'Pemasukan Kotor') {
                    $progress = $this->calculatePemasukanKotor($item, $personId);
                } elseif ($item->asistant_route == 'pemasukan bersih') {
                    $progress = $this->calculatePemasukanBersih($item, $personId);
                } elseif ($item->asistant_route === 'rasio biaya operasional terhadap revenue') {
                    $progress = $this->calculateRasioBiayaOperasionalTerhadapRevenue($item, $personId);
                }
                // CS
                elseif ($item->asistant_route === 'peserta puas dengan pelayanan dan fasilitas training') {
                    $progress = $this->calculatePesertaPuasDenganPelayananDanFasilitasTraining($item, $personId);
                } elseif ($item->asistant_route === 'dorong inovasi pelayanan') {
                    $progress = $this->calculateDorongInovasiPelayanan($item, $personId);
                } elseif ($item->asistant_route === 'penanganan komplain peserta') {
                    $progress = $this->calculatePenangananKomplainPerseta($item, $personId);
                }
                // Finance
                elseif ($target->asistant_route === 'outstanding') {
                    $progress = $this->calculateOutstanding($target, $personId);
                } elseif ($target->asistant_route === 'inisiatif efisiensi keuangan') {
                    $progress = $this->calculateInisiatifEfisiensiKeuangan($target, $personId);
                } elseif ($target->asistant_route === 'mengurangi manual work dan error') {
                    $progress = $this->calculateMengurangiManualWorkDanError($target, $personId);
                } elseif ($target->asistant_route === 'laporan analisis keuangan') {
                    $progress = $this->calculateLaporanAnalisisKeuangan($target, $personId);
                } elseif ($target->asistant_route === 'pencairan biaya operasional') {
                    $progress = $this->calculatePencairanBiayaOperasional($target, $personId);
                } elseif ($target->asistant_route === 'penyelesaian tagihan perusahaan') {
                    $progress = $this->calculatePenyelesaianTagihanPerusahaan($target, $personId);
                }
                // HRD
                elseif ($item->asistant_route === 'pelaksanaan kegiatan karyawan') {
                    $progress = $this->calculatePelaksanaanKegiatanKaryawan($item, $personId);
                } elseif ($item->asistant_route === 'pengeluaran biaya karyawan') {
                    $progress = $this->calculatePengeluaranBiayaKaryawan($item, $personId);
                }
                // Driver
                elseif ($target->asistant_route === 'perbaikan kendaraan') {
                    $progress = $this->calculatePerbaikanKendaraan($target, $personId);
                } elseif ($target->asistant_route === 'kontrol pengeluaran transportasi') {
                    $progress = $this->calculateKontrolPengeluaranTransportasi($target, $personId);
                } elseif ($target->asistant_route === 'report kondisi kendaraan') {
                    $progress = $this->calculateReportKondisiKendaraan($target, $personId);
                } elseif ($target->asistant_route === 'feedback kenyamanan berkendara') {
                    $progress = $this->calculateFeedbackKenyamananBerkendara($target, $personId);
                }
                // OB
                elseif ($item->asistant_route === 'feedback kebersihan dan kenyamanan') {
                    $progress = $this->calculateFeedbackKebersihanDanKenyamanan($item, $personId);
                }
                // ITSM
                elseif ($target->asistant_route === 'kepuasan client ITSM') {
                    $progress = $this->calculateProgressKepuasanClientITSM($target, $personId);
                } elseif ($target->asistant_route === 'inovation adaption rate') {
                    $progress = $this->calculateInovationAdaptionRate($target, $personId);
                }
                // Koordinator ITSM
                elseif ($item->asistant_route === 'availability sistem internal kritis') {
                    $progress = $this->calculateAvailabilitySistemInternalKritis($item, $personId);
                } elseif ($item->asistant_route === 'meningkatkan kepuasan dan loyalitas peserta/client') {
                    $progress = $this->calculateMeningkatkanKepuasanDanLoyalitasPeserta($item, $personId);
                }
                // Programmer
                elseif ($item->asistant_route === 'ketepatan waktu penyelesaian fitur') {
                    $progress = $this->calculateProgressKetepatanWaktuPenyelesaianFitur($item, $personId);
                } elseif ($item->asistant_route === 'mengukur kualitas aplikasi agar minim bug') {
                    $progress = $this->calculateMengukurKualitasAplikasiAgarMinimBug($item, $personId);
                }
                // Tim Digital
                elseif ($item->asistant_route === 'konsistensi campaign digital') {
                    $progress = $this->calculateKonsistensiCampaignDigital($item, $personId);
                } elseif ($item->asistant_route === 'efektifitas diital marketing') {
                    $progress = $this->calculateEfektifitasDiitalMarketing($item, $personId);
                }
                // TS
                elseif ($item->asistant_route === 'keberhasilan support memenuhi sla') {
                    $progress = $this->calculateTingkatKeberhasilanSupportMemenuhiSLA($item, $personId);
                } elseif ($item->asistant_route === 'kualitas layanan exam') {
                    $progress = $this->calculateKualitasLayananExam($item, $personId);
                }
                // Education - Instruktur
                elseif ($item->asistant_route === 'kepuasan peserta pelatihan') {
                    $progress = $this->calculateKepuasanPesertaPelatihan($item, $personId);
                } elseif ($item->asistant_route === 'upseling lanjutan materi') {
                    $progress = $this->calculateUpselingLanjutanMateri($item, $personId);
                } elseif ($item->asistant_route === 'sertifikasi kompetensi internal') {
                    $progress = $this->calculateSertifikasiKompetensiInternal($item, $personId);
                } elseif ($item->asistant_route === 'pelatihan kompetensi eksternal') {
                    $progress = $this->calculatePelatihanKompetensiEksternal($item, $personId);
                }
                // Education Manager
                elseif ($item->asistant_route === 'pengembangan kurikulum pelatihan') {
                    $progress = $this->calculatePengembanganKurikulumPelatihan($item, $personId);
                } elseif ($item->asistant_route === 'peningkatan knowledge sharing') {
                    $progress = $this->calculatePeningkatanKnowledgeSharing($item, $personId);
                }
                //Sales & Marketing
                elseif ($target->asistant_route === 'target penjualan tahunan') {
                    $progress = $this->calculateTargetPenjualanTahunan($target, $personId);
                }

                //Sales & Marketing
                // SPV Sales
                elseif ($target->asistant_route === 'meningkatkan revenue perusahaan') {
                    $data = $this->calculateMeningkatkanRevenuePerusahaan($target, $personId);
                }

                elseif($target->asistant_route === 'laporan mom') {
                    $data = $this->calculateLaporanMOM($target);
                }


                if ($progress !== null && is_numeric($progress)) {
                    $progresses[] = $progress;
                }
            }

            if (!empty($progresses)) {
                $divisionAvg = array_sum($progresses) / count($progresses);
                $divisionAverages[] = $divisionAvg;
            }
        }

        if (!empty($divisionAverages)) {
            $finalProgress = array_sum($divisionAverages) / count($divisionAverages);
            $progress = round($finalProgress, 1);
        } else {
            $progress = 0;
        }

        $averageTarget = !empty($targetValues) ? array_sum($targetValues) / count($targetValues) : 100;

        $gapRaw = $progress - $averageTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        if ($gap === '-0') {
            $gap = '0';
        }

        $above = round(max(0, $progress), 1);
        $below = round(max(0, 100 - $progress), 1);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
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

        $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

        if ($feedbacks->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $allScores = [];
        $scoreDatePairs = [];

        foreach ($feedbacks as $fb) {

            $values = [
                $fb->F1,$fb->F2,$fb->F3,$fb->F4,$fb->F5,
                $fb->P1,$fb->P2,$fb->P3,$fb->P4,$fb->P5,
                $fb->P1,$fb->P2 // mengikuti function kedua (yang kamu bilang benar)
            ];

            $cleanValues = [];

            foreach ($values as $v) {
                $cleanValues[] = is_numeric($v) ? (float)$v : 0;
            }

            $avg = array_sum($cleanValues) / 12;
            $avg = min(4, max(1, $avg));

            $allScores[] = $avg;

            $scoreDatePairs[] = [
                'score' => $avg,
                'date' => $fb->created_at->format('Y-m-d')
            ];
        }

        $totalResponden = count($allScores);
        $respondenPuas = 0;

        foreach ($allScores as $score) {
            if ($score >= 3.5) {
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

        foreach ($monthlyData as $month => $vals) {
            $monthlyAverages[$month] = round(array_sum($vals) / count($vals), 1);
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
            }
        }

        $progress = round($progress, 1);
        $gapRaw = $progress - $nilaiTarget;
        if ($progress > $nilaiTarget) {
            $gap = 0;
        } else {
            $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');
        }
        
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

    private function calculatePenangananKomplainPersetaDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
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

        $komplainData = KomplainPeserta::whereBetween('created_at', [$start, $end])->get();

        $totalData = $komplainData->count();

        if ($totalData === 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $dataTepatWaktu = 0;
        $dataTidakTepatWaktu = 0;
        $dailyValues = [];

        foreach ($komplainData as $data) {
            $createdDate = Carbon::parse($data->created_at);
            $dateKey = $createdDate->format('Y-m-d');

            $isTepatWaktu = 0;

            if ($data->tanggal_selesai) {
                $finishedDate = Carbon::parse($data->tanggal_selesai);

                if ($createdDate->format('Y-m-d') === $finishedDate->format('Y-m-d')) {
                    $dataTepatWaktu++;
                    $isTepatWaktu = 1;
                } else {
                    $dataTidakTepatWaktu++;
                }
            } else {
                $dataTidakTepatWaktu++;
            }

            if (!isset($dailyValues[$dateKey])) {
                $dailyValues[$dateKey] = [];
            }
            $dailyValues[$dateKey][] = $isTepatWaktu * 100;
        }

        $presentase = ($dataTepatWaktu / $totalData) * 100;
        $progress = round($presentase, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $dataTepatWaktu;
        $below = $dataTidakTepatWaktu;

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

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

    private function calculateReportPersiapanKelasDetail($itemDetail, $personId)
    {
        $details = $itemDetail->detailTargetKPI;
        $detail = $details->first();

        $nilaiTarget = (float) optional($detail)->nilai_target;
        $tahun = (int) optional($detail)->detail_jangka ?? now()->year;

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

        $totalRkm = RKM::whereYear('tanggal_awal', $tahun)->count();

        $checklistItems = ChecklistKeperluan::whereYear('created_at', $tahun)->where('materi', 1)->where('kelas', 1)->where('cb', 1)->where('maksi', 1)->where('keperluan_kelas', 1)->select('created_at')->get();

        $totalTuntas = $checklistItems->count();

        if ($totalRkm > 0) {
            $progress = ($totalTuntas / $totalRkm) * 100;
        } else {
            $progress = 0;
        }

        $dailyBreakdownPerMonth = [];
        $monthlyTotals = [];

        foreach ($checklistItems as $row) {
            $date = Carbon::parse($row->created_at);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');

            $value = 1;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }

            if (!isset($dailyBreakdownPerMonth[$monthKey][$dateKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dateKey] = 0;
            }
            $dailyBreakdownPerMonth[$monthKey][$dateKey] += $value;

            if (!isset($monthlyTotals[$monthKey])) {
                $monthlyTotals[$monthKey] = 0;
            }
            $monthlyTotals[$monthKey] += $value;
        }

        $monthlyData = $monthlyTotals;

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $pieChart = [
            'above' => $totalTuntas,
            'below' => max(0, $totalRkm - $totalTuntas),
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

    private function calculateMengurangiManualWorkDanErrorDetail($itemDetail)
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

    private function calculateLaporanAnalisisKeuanganDetail($itemDetail)
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

    private function calculatePenyelesaianTagihanPerusahaanDetail($itemDetail, $personId)
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

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
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

        $dataTagihan = trackingTagihanPerusahaan::whereBetween('tanggal_perkiraan_mulai', [$start, $end])->get();

        $totalTagihan = $dataTagihan->count();

        $tagihanSelesai = $dataTagihan->filter(function ($row) {
            return $row->status === 'selesai' && $row->tracking === 'Selesai';
        })->count();

        $progress = $totalTagihan > 0 ? round(($tagihanSelesai / $totalTagihan) * 100, 1) : 0;

        $nilaiTarget = (float) $detail->nilai_target;
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $tagihanSelesai;
        $below = $totalTagihan - $tagihanSelesai;

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        foreach ($dataTagihan as $tagihan) {

            $date = Carbon::parse($tagihan->tanggal_perkiraan_mulai);

            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $isSelesai = ($tagihan->status === 'selesai' && $tagihan->tracking === 'Selesai') ? 1 : 0;

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [
                    'total' => 0,
                    'selesai' => 0
                ];
            }

            $monthlyData[$monthKey]['total']++;
            $monthlyData[$monthKey]['selesai'] += $isSelesai;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }

            if (!isset($dailyBreakdownPerMonth[$monthKey][$dayKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dayKey] = [
                    'total' => 0,
                    'selesai' => 0
                ];
            }

            $dailyBreakdownPerMonth[$monthKey][$dayKey]['total']++;
            $dailyBreakdownPerMonth[$monthKey][$dayKey]['selesai'] += $isSelesai;
        }

        $monthlyAverages = [];

        foreach ($monthlyData as $month => $data) {
            $monthlyAverages[$month] = $data['total'] > 0
                ? round(($data['selesai'] / $data['total']) * 100, 1)
                : 0;
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $above,
                'below' => $below
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
        ];
    }


    //HRD
    private function calculatePelaksanaanKegiatanKaryawanDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->manual_value)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $startOfYear = Carbon::create($tahun, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($tahun, 12, 31)->endOfDay();

        $kegiatans = Kegiatan::whereBetween('created_at', [$startOfYear, $endOfYear])->get();

        $totalKegiatan = $kegiatans->count();
        $totalKehadiranValid = 0;

        $dailyAverages = [];
        $aboveCount = 0;
        $belowCount = 0;

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

            $tanggalKey = $tanggalKegiatan->format('Y-m-d');
            $dailyAverages[$tanggalKey] = round($persentase, 1);

            if ($persentase >= 80) {
                $totalKehadiranValid++;
                $aboveCount++;
            } else {
                $belowCount++;
            }
        }

        if ($totalKegiatan == 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $progress = ($totalKehadiranValid / $totalKegiatan) * 100;
        $progress = round($progress, 1);

        $nilaiTarget = $detail->nilai_target ?? 0;
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
            'pie_chart' => ['above' => $aboveCount, 'below' => $belowCount],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    private function calculatePengeluaranBiayaKaryawanDetail($itemDetail)
    {
        $defaultResponse = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
            'dataManual' => [
                'gaji' => 0,
                'bpjs' => 0,
                'rekrutmen' => 0,
                'manual_document' => null,
            ],
        ];

        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->detail_jangka)) {
            return $defaultResponse;
        }

        $tahun = (int) $detail->detail_jangka;
        $nilaiTarget = (int) $detail->nilai_target;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return $defaultResponse;
        }

        $startOfYear = Carbon::create($tahun, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($tahun, 12, 31)->endOfDay();

        $parts = explode(',', $detail->manual_value ?? '');

        $gaji = (float) ($parts[0] ?? 0);
        $bpjsManual = (float) ($parts[1] ?? 0);
        $rekrutmenManual = (float) ($parts[2] ?? 0);

        $defaultResponse['dataManual'] = [
            'gaji' => $gaji,
            'bpjs' => $bpjsManual,
            'rekrutmen' => $rekrutmenManual,
            'manual_document' => $detail->manual_document ?? null,
        ];

        $bpjsIds = JenisTunjangan::whereIn('nama_tunjangan', [
            'BPJS Tenaga Kerja',
            'BPJS Kesehatan'
        ])->pluck('id');

        $bpjsBudget = TunjanganKaryawan::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->whereIn('jenis_tunjangan', $bpjsIds)
            ->sum('total');

        $rekrutmenBudget = Kegiatan::whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('tipe', 'rekrutment')
            ->sum('realisasi');

        $kegiatanBudget = 0;
        $kegiatanRealisasi = 0;

        $kegiatans = Kegiatan::with('pengajuan_barang.detail')
            ->whereBetween('created_at', [$startOfYear, $endOfYear])
            ->where('tipe', 'kegiatan')
            ->get();

        foreach ($kegiatans as $kegiatan) {

            $kegiatanRealisasi += (float) $kegiatan->realisasi;

            if ($kegiatan->pengajuan_barang) {
                foreach ($kegiatan->pengajuan_barang as $pengajuan) {

                    if ($pengajuan->detail) {
                        foreach ($pengajuan->detail as $d) {

                            $qty = (float) ($d->qty ?? 0);
                            $harga = (float) ($d->harga ?? 0);

                            $kegiatanBudget += $qty * $harga;
                        }
                    }
                }
            }
        }

        $score = 0;

        if ($gaji > 0) {
            $score++;
        }

        if ($bpjsManual > 0 && $bpjsManual <= $bpjsBudget) {
            $score++;
        }

        if ($rekrutmenManual > 0 && $rekrutmenManual <= $rekrutmenBudget) {
            $score++;
        }

        if ($kegiatanRealisasi > 0 && $kegiatanRealisasi <= $kegiatanBudget) {
            $score++;
        }

        $progress = round(($score / 4) * 100, 1);
        $gap = 0;
        if ($progress <= $nilaiTarget) {
            $gap = $progress - $nilaiTarget;
        } else {
            $gap = 0;
        }
        

        return [
            'progress' => $progress,
            'gap' => $gap,
            'dataManual' => $defaultResponse['dataManual'],
            'pie_chart' => [
                'above' => $score,
                'below' => 4 - $score
            ],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
        ];
    }

    private function calculateAdministrasiKaryawanDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail)) {
            Log::warning("detailTargetKPI tidak ditemukan untuk item ID: {$itemDetail->id}");
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            Log::warning("Tahun tidak valid: {$tahun} untuk item ID: {$itemDetail->id}");
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $bulanTuntas = 0;
        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $adaTunjangan = TunjanganKaryawan::where('tahun', $tahun)->where('bulan', $bulan)->whereDay('created_at', '<=', 10)->exists();

            $monthKey = sprintf('%04d-%02d', $tahun, $bulan);

            $nilaiBulan = $adaTunjangan ? 100 : 0;

            if ($adaTunjangan) {
                $bulanTuntas++;
            }

            $monthlyData[$monthKey] = $nilaiBulan;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dayKey = sprintf('%04d-%02d-10', $tahun, $bulan);
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $nilaiBulan;
        }

        $progressTunjangan = ($bulanTuntas / 12) * 100;
        $progress = round($progressTunjangan, 1);

        $above = $bulanTuntas;
        $below = 12 - $bulanTuntas;

        $nilaiTarget = $itemDetail->detailTargetKPI->pluck('nilai_target')->first() ?? 0;
        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    //Driver
    private function calculatePerbaikanKendaraanDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
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

        if ($personId !== null) {
            $allRepairs = PerbaikanKendaraan::whereBetween('created_at', [$start, $end])
                ->where('id_user', $personId)
                ->get();
        } else {
            $allRepairs = PerbaikanKendaraan::whereBetween('created_at', [$start, $end])->get();
        }

        $totalData = $allRepairs->count();

        if ($totalData == 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $dataDiperbaiki = $allRepairs->where('status', 'Selesai')->count();
        $dataBelumDiperbaiki = $totalData - $dataDiperbaiki;

        $presentase = ($dataDiperbaiki / $totalData) * 100;
        $progress = round($presentase, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $dataDiperbaiki;
        $below = $dataBelumDiperbaiki;

        $dailyValues = [];

        foreach ($allRepairs as $repair) {
            $tanggal = Carbon::parse($repair->created_at);
            $dateKey = $tanggal->format('Y-m-d');

            $nilaiItem = $repair->status === 'Selesai' ? 100 : 0;

            if (!isset($dailyValues[$dateKey])) {
                $dailyValues[$dateKey] = [];
            }
            $dailyValues[$dateKey][] = $nilaiItem;
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

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

    private function calculateKontrolPengeluaranTransportasiDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
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

        if ($personId !== null) {
            $DataPickup = pickupDriver::whereBetween('created_at', [$start, $end])
                ->whereNotNull('budget')
                ->where('id_karyawan', $personId)
                ->get();
        } else {
            $DataPickup = pickupDriver::whereBetween('created_at', [$start, $end])
                ->whereNotNull('budget')
                ->get();
        }

        $totalData = $DataPickup->count();

        if ($totalData === 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $countAman = 0;
        $dailyValues = [];

        foreach ($DataPickup as $data) {
            $totalBiaya = BiayaTransportasiDriver::where('id_pickup_driver', $data->id)->sum('harga');

            $isAman = $totalBiaya <= $data->budget ? 1 : 0;
            if ($isAman) {
                $countAman++;
            }

            $tanggal = Carbon::parse($data->created_at);
            $dateKey = $tanggal->format('Y-m-d');

            if (!isset($dailyValues[$dateKey])) {
                $dailyValues[$dateKey] = [];
            }
            $dailyValues[$dateKey][] = $isAman * 100;
        }

        $presentase = ($countAman / $totalData) * 100;
        $progress = round($presentase, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $countAman;
        $below = $totalData - $countAman;

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

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

    private function calculateReportKondisiKendaraanDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
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

        $startPeriode = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endPeriode = Carbon::createFromDate($tahun, 12, 31)->endOfDay();
        $hariIni = now()->startOfDay();

        if ($hariIni > $endPeriode) {
            $hariIni = $endPeriode;
        }

        if ($personId !== null) {
            $firstReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startPeriode, $hariIni])
                ->where('user_id', $personId)
                ->whereNotNull('tanggal_pemeriksaan')
                ->orderBy('tanggal_pemeriksaan', 'asc')
                ->first();
        } else {
            $firstReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$startPeriode, $hariIni])
                ->whereNotNull('tanggal_pemeriksaan')
                ->orderBy('tanggal_pemeriksaan', 'asc')
                ->first();
        }

        if (!$firstReport) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $startMinggu = Carbon::parse($firstReport->tanggal_pemeriksaan)->startOfWeek(Carbon::MONDAY);

        $today = Carbon::now();
        $dayOfWeek = $today->dayOfWeek;

        if ($dayOfWeek < 6) {
            $checkUntil = $today->copy()->subWeek()->endOfWeek(Carbon::SUNDAY);
        } else {
            $checkUntil = $today->endOfDay();
        }

        if ($checkUntil > $endPeriode) {
            $checkUntil = $endPeriode;
        }

        $totalMinggu = ceil($startMinggu->diffInDays($checkUntil) / 7);

        if ($totalMinggu < 1) {
            $totalMinggu = 1;
        }

        $jumlahReportTepat = 0;
        $jumlahReportTidakTepat = 0;
        $weeklyData = [];

        for ($i = 0; $i < $totalMinggu; $i++) {
            $weekStart = $startMinggu->copy()->addWeeks($i)->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            if ($weekEnd > $checkUntil) {
                $weekEnd = $checkUntil;
            }

            if ($personId !== null) {
                $hasReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$weekStart, $weekEnd])
                    ->where('user_id', $personId)
                    ->whereNotNull('tanggal_pemeriksaan')
                    ->exists();
            } else {
                $hasReport = KondisiKendaraan::whereBetween('tanggal_pemeriksaan', [$weekStart, $weekEnd])
                    ->whereNotNull('tanggal_pemeriksaan')
                    ->exists();
            }

            if ($hasReport) {
                $jumlahReportTepat++;
                $weekValue = 100;
            } else {
                $jumlahReportTidakTepat++;
                $weekValue = 0;
            }

            $weeklyData[] = [
                'start' => $weekStart->copy(),
                'end' => $weekEnd->copy(),
                'value' => $weekValue,
            ];
        }

        $presentase = ($jumlahReportTepat / $totalMinggu) * 100;
        $progress = round($presentase, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $jumlahReportTepat;
        $below = $jumlahReportTidakTepat;

        $dailyValues = [];

        foreach ($weeklyData as $week) {
            $currentDate = $week['start']->copy();

            while ($currentDate <= $week['end']) {
                $dateKey = $currentDate->format('Y-m-d');

                if (!isset($dailyValues[$dateKey])) {
                    $dailyValues[$dateKey] = [];
                }
                $dailyValues[$dateKey][] = $week['value'];

                $currentDate->addDay();
            }
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

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

     private function calculateFeedbackKenyamananBerkendaraDetail($itemDetail)
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
            $p8 = is_numeric($fb->p8) ? (float) $fb->p8 : 0;

            $avg = ($p8) / 1;
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

    //OB
    private function calculateFeedbackKebersihanDanKenyamananDetail($itemDetail)
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

    private function calculatePenyelesaianTugasHarianDetail($itemDetail, $personId = null)
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

        $query = KontrolTugas::whereYear('created_at', $tahun);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $tugas = $query->get();

        if ($tugas->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $jumlahTugas = $tugas->count();
        $jumlahTugasSelesai = $tugas->where('status', '1')->count();

        $progress = ($jumlahTugasSelesai / $jumlahTugas) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        foreach ($tugas as $t) {
            $date = $t->created_at;
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $score = $t->status == 1 ? 100 : 0;

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
        foreach ($monthlyData as $month => $vals) {
            $monthlyAverages[$month] = round(array_sum($vals) / count($vals), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $jumlahTugasSelesai,
                'below' => $jumlahTugas - $jumlahTugasSelesai,
            ],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
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

        $allScores = [];
        $scoreDatePairs = [];

        // === SurveyKepuasan saja ===
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
            if ($skor >= 3.0) {
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

        if ($tahun < 2000 || $tahun > now()->year + 5) {
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

        $logs = activityLog::whereBetween('status', ['100', '599'])
            ->whereBetween('checked_at', [$start, $end])
            ->get();

        if ($logs->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $totalChecks = $logs->count();
        $upChecks = $logs->where('is_up', 1)->count();

        $progress = ($upChecks / $totalChecks) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        foreach ($logs as $log) {
            $date = Carbon::parse($log->checked_at);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $value = $log->is_up ? 100 : 0;

            if (!isset($monthlyData[$monthKey])) {
                $monthlyData[$monthKey] = [];
            }

            $monthlyData[$monthKey][] = $value;

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }

            $dailyBreakdownPerMonth[$monthKey][$dayKey][] = $value;
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
                'above' => $upChecks,
                'below' => $totalChecks - $upChecks,
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
                'weekly_data' => [],
                'daily_breakdown_per_week' => [],
            ];
        }

        $tahun = (int) $details->first()->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 1) {
            $tahun = now()->year;
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $contentSchedules = ContentSchedule::whereBetween('upload_date', [$start, $end])
            ->whereNotNull('upload_date')
            ->get();

        if ($contentSchedules->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'weekly_data' => [],
                'daily_breakdown_per_week' => [],
            ];
        }

        $weeklyCounts = [];
        $dailyBreakdownPerWeek = [];

        foreach ($contentSchedules as $schedule) {

            $date = Carbon::parse($schedule->upload_date);

            $weekKey = $date->format('o-\WW');
            $dayKey = $date->format('Y-m-d');

            // Hitung konten per minggu
            $weeklyCounts[$weekKey] = ($weeklyCounts[$weekKey] ?? 0) + 1;

            // Breakdown harian per minggu
            if (!isset($dailyBreakdownPerWeek[$weekKey])) {
                $dailyBreakdownPerWeek[$weekKey] = [];
            }

            $dailyBreakdownPerWeek[$weekKey][$dayKey] =
                ($dailyBreakdownPerWeek[$weekKey][$dayKey] ?? 0) + 1;
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

        $progress = $totalWeeksWithData === 0
            ? 0
            : round(($compliantWeeks / $totalWeeksWithData) * 100, 1);

        $nilaiTarget = $details->pluck('nilai_target')->first() ?? 0;

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $compliantWeeks;
        $below = $totalWeeksWithData - $compliantWeeks;

        ksort($weeklyCounts);
        ksort($dailyBreakdownPerWeek);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $above,
                'below' => $below
            ],
            'monthly_data' => $weeklyCounts,
            'daily_breakdown_per_month' => $dailyBreakdownPerWeek,
        ];
    }

    private function calculateEfektifitasDiitalMarketingDetail($itemDetail, $personId)
    {
        $details = $itemDetail->detailTargetKPI;
        $detail = $details->first();

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
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

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 1) {
            if ($tahun < 2000 || $tahun > now()->year + 1) {
                $tahun = now()->year;
            }
        }

        $start = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $end = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $dataColaborator = Colaborator::whereBetween('created_at', [$start, $end])->get();

        $totalData = $dataColaborator->count();

        if ($totalData == 0) {
            return [
                'progress' => 0,
                'gap' => rtrim(rtrim(sprintf('%.1f', 0 - $nilaiTarget), '0'), '.'),
                'pie_chart' => ['above' => 0, 'below' => 4],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $totalQuarters = 4;
        $quartersWith = [];

        foreach ($dataColaborator as $colab) {
            $month = $colab->created_at->month;
            $quarter = ceil($month / 3);
            $quartersWith[$quarter] = true;
        }

        $filledQuartersCount = count($quartersWith);
        $konsistensiPersen = ($filledQuartersCount / $totalQuarters) * 100;
        $progress = round($konsistensiPersen, 1);

        $gapRaw = $progress - 100;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $filledQuartersCount;
        $below = $totalQuarters - $filledQuartersCount;

        $dailyValues = [];

        foreach ($dataColaborator as $colab) {
            $tanggal = Carbon::parse($colab->created_at);
            $dateKey = $tanggal->format('Y-m-d');

            $nilaiItem = 1;

            if (!isset($dailyValues[$dateKey])) {
                $dailyValues[$dateKey] = [];
            }
            $dailyValues[$dateKey][] = $nilaiItem;
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

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
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)->where('id_karyawan', $personId)->pluck('id_karyawan')->unique()->toArray();

            if (!empty($idKaryawans)) {
                $namaLengkapList = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->toArray();

                $picNames = array_map(function ($nama) {
                    return explode(' ', trim($nama))[0] ?? '';
                }, $namaLengkapList);
            }
        } else {
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)->pluck('id_karyawan')->unique()->toArray();

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
                if (strlen($ticket->tanggal_selesai) > 10) {
                    // sudah datetime
                    $endAt = Carbon::parse($ticket->tanggal_selesai, 'Asia/Jakarta');
                } else {
                    // hanya date
                    $endAt = Carbon::parse($ticket->tanggal_selesai . ' ' . $ticket->jam_selesai, 'Asia/Jakarta');
                }
                $durasiJam = $this->hitungJamKerja($startAt, $endAt);

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
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)->where('id_karyawan', $personId)->pluck('id_karyawan')->unique()->toArray();

            if (!empty($idKaryawans)) {
                $namaLengkapList = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->toArray();

                // Ekstrak nama depan
                $picNames = array_map(function ($nama) {
                    return explode(' ', trim($nama))[0] ?? '';
                }, $namaLengkapList);
            }
        } else {
            // 2. Jika personId NULL: Ambil SEMUA karyawan dari detailPersonKPI (BUKAN dari jabatan)
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)->pluck('id_karyawan')->unique()->toArray();

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

        $picJabatan = karyawan::whereIn('jabatan', $targetJabatanList)->pluck('jabatan')->unique()->map(fn($n) => ucwords(strtolower($n)))->toArray();

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
            if (strlen($ticket->tanggal_selesai) > 10) {
                // sudah datetime
                $endAt = Carbon::parse($ticket->tanggal_selesai, 'Asia/Jakarta');
            } else {
                // hanya date
                $endAt = Carbon::parse($ticket->tanggal_selesai . ' ' . $ticket->jam_selesai, 'Asia/Jakarta');
            }
            $actualHours = $this->hitungJamKerja($startAt, $endAt);

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

        $allScores = [];
        $scoreDatePairs = [];

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
            if ($skor >= 3.0) {
                $respondenPuas++;
            }
        }

        $progress = ($respondenPuas / $totalResponden) * 100;
        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        // === Breakdown ===
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

    private function calculateInovationAdaptionRateDetail($item, $personId)
    {
        $detail = $item->detailTargetKPI->first();

        if (!$detail || !is_numeric($detail->nilai_target)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) ($detail->detail_jangka ?? now()->year);

        if ($tahun < 2000 || $tahun > now()->year + 5) {
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

        $ideInovasi = IdeInovasi::whereBetween('created_at', [$start, $end])->get();

        if ($ideInovasi->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $dailyResults = [];

        foreach ($ideInovasi as $ide) {

            $tanggal = $ide->created_at->format('Y-m-d');

            // aturan bisnis tetap: ada ide = 100
            $dailyResults[$tanggal][] = 100;
        }

        $dailyAverages = [];

        foreach ($dailyResults as $tanggal => $values) {
            $dailyAverages[$tanggal] = array_sum($values) / count($values);
        }

        $totalDays = count($dailyAverages);
        $above = $totalDays; // karena jika ada ide = 100
        $below = 0;

        $progress = $totalDays > 0 ? 100 : 0;

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        if ($progress > $nilaiTarget) {
            $gap = 0;
        } else {
            $gap = $progress - $nilaiTarget;
        }

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        foreach ($dailyAverages as $dateStr => $avg) {

            $date = Carbon::parse($dateStr);
            $monthKey = $date->format('Y-m');
            $dayKey = $date->format('Y-m-d');

            $monthlyData[$monthKey][] = $avg;
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $avg;
        }

        $monthlyAverages = [];

        foreach ($monthlyData as $month => $values) {
            $monthlyAverages[$month] = round(array_sum($values) / count($values), 1);
        }

        ksort($monthlyAverages);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => [
                'above' => $above,
                'below' => $below
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
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)->where('id_karyawan', $personId)->pluck('id_karyawan')->unique()->toArray();

            if (!empty($idKaryawans)) {
                $namaLengkapList = karyawan::whereIn('id', $idKaryawans)->pluck('nama_lengkap')->toArray();

                $picNames = array_map(function ($nama) {
                    return explode(' ', trim($nama))[0] ?? '';
                }, $namaLengkapList);
            }
        } else {
            $idKaryawans = detailPersonKPI::where('detailTargetKey', $firstDetail->id)->pluck('id_karyawan')->unique()->toArray();

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

    private function calculateKualitasLayananExamDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
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

        $queryKPI = PenilaianExam::selectRaw('id_rkm, AVG(nilai_emote) as nilai')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('id_rkm')
            ->groupBy('id_rkm');

        $dataKPI = $queryKPI->get();

        $totalPenilaian = $dataKPI->count();

        if ($totalPenilaian == 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $qualifiedPenilaian = $dataKPI
            ->filter(function ($item) {
                return $item->nilai >= 3.5;
            })
            ->count();

        $presentase = ($qualifiedPenilaian / $totalPenilaian) * 100;
        $progress = round($presentase, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $qualifiedPenilaian;
        $below = $totalPenilaian - $qualifiedPenilaian;

        $queryTimeSeries = PenilaianExam::select('created_at', 'nilai_emote')
            ->whereBetween('created_at', [$start, $end])
            ->whereNotNull('id_rkm');

        $allExams = $queryTimeSeries->get();

        $dailyValues = [];

        foreach ($allExams as $exam) {
            $tanggal = Carbon::parse($exam->created_at);
            $dateKey = $tanggal->format('Y-m-d');

            $nilaiItem = $exam->nilai_emote >= 3.5 ? 100 : 0;

            if (!isset($dailyValues[$dateKey])) {
                $dailyValues[$dateKey] = [];
            }
            $dailyValues[$dateKey][] = $nilaiItem;
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

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

    //Education
    //Instruktur
    private function calculateKepuasanPesertaPelatihanDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        // Validasi Awal
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

        // Validasi Range Tahun
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

        if ($personId !== null) {
            $kodeKaryawan = karyawan::where('id', $personId)->first();

            if ($kodeKaryawan) {
                $rkmList = RKM::where('instruktur_key', $kodeKaryawan->kode_karyawan)->orWhere('instruktur_key2', $kodeKaryawan->kode_karyawan)->orWhere('asisten_key', $kodeKaryawan->kode_karyawan)->get();

                if (!$rkmList->isEmpty()) {
                    $rkmIds = $rkmList->pluck('id_rkm')->filter()->toArray();

                    $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])
                        ->whereIn('id_rkm', $rkmIds)
                        ->get();

                    foreach ($feedbacks as $fb) {
                        $rkm = $rkmList->firstWhere('id_rkm', $fb->id_rkm);

                        if (!$rkm) {
                            continue;
                        }

                        $avg = 0;

                        if ($rkm->instruktur_key == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float) ($fb->I1 ?? 0), (float) ($fb->I2 ?? 0), (float) ($fb->I3 ?? 0), (float) ($fb->I4 ?? 0), (float) ($fb->I5 ?? 0), (float) ($fb->I6 ?? 0), (float) ($fb->I7 ?? 0), (float) ($fb->I8 ?? 0)];
                            $avg = array_sum($scores) / 8;
                        } elseif ($rkm->instruktur_key2 == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float) ($fb->I1b ?? 0), (float) ($fb->I2b ?? 0), (float) ($fb->I3b ?? 0), (float) ($fb->I4b ?? 0), (float) ($fb->I5b ?? 0), (float) ($fb->I6b ?? 0), (float) ($fb->I7b ?? 0), (float) ($fb->I8b ?? 0)];
                            $avg = array_sum($scores) / 8;
                        } elseif ($rkm->asisten_key == $kodeKaryawan->kode_karyawan) {
                            $scores = [(float) ($fb->I1as ?? 0), (float) ($fb->I2as ?? 0), (float) ($fb->I3as ?? 0), (float) ($fb->I4as ?? 0), (float) ($fb->I5as ?? 0), (float) ($fb->I6as ?? 0), (float) ($fb->I7as ?? 0), (float) ($fb->I8as ?? 0)];
                            $avg = array_sum($scores) / 8;
                        }

                        // Clamp average antara 1 dan 4
                        $avg = min(4, max(1, $avg));

                        $allScores[] = $avg;
                        $scoreDatePairs[] = [
                            'score' => $avg,
                            'date' => $fb->created_at->format('Y-m-d'),
                        ];
                    }
                }
            }
        } else {
            // --- LOGIKA ORIGINAL (TANPA PERSON ID) ---
            $feedbacks = Nilaifeedback::whereBetween('created_at', [$start, $end])->get();

            foreach ($feedbacks as $fb) {
                // Base scores I1-I8
                $i1 = (float) ($fb->I1 ?? 0);
                $i2 = (float) ($fb->I2 ?? 0);
                $i3 = (float) ($fb->I3 ?? 0);
                $i4 = (float) ($fb->I4 ?? 0);
                $i5 = (float) ($fb->I5 ?? 0);
                $i6 = (float) ($fb->I6 ?? 0);
                $i7 = (float) ($fb->I7 ?? 0);
                $i8 = (float) ($fb->I8 ?? 0);
                $sumBase = $i1 + $i2 + $i3 + $i4 + $i5 + $i6 + $i7 + $i8;

                $i1b = (float) ($fb->I1b ?? 0);
                $i2b = (float) ($fb->I2b ?? 0);
                $i3b = (float) ($fb->I3b ?? 0);
                $i4b = (float) ($fb->I4b ?? 0);
                $i5b = (float) ($fb->I5b ?? 0);
                $i6b = (float) ($fb->I6b ?? 0);
                $i7b = (float) ($fb->I7b ?? 0);
                $i8b = (float) ($fb->I8b ?? 0);
                $sumB = $i1b + $i2b + $i3b + $i4b + $i5b + $i6b + $i7b + $i8b;

                if ($sumB > 0) {
                    $totalSum = $sumBase + $sumB;
                    $totalItem = 16;
                } else {
                    $totalSum = $sumBase;
                    $totalItem = 8;
                }

                if ($totalItem > 0) {
                    $avg = $totalSum / $totalItem;
                    $avg = min(4, max(1, $avg));

                    $allScores[] = $avg;
                    $scoreDatePairs[] = [
                        'score' => $avg,
                        'date' => $fb->created_at->format('Y-m-d'),
                    ];
                }
            }
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

    private function calculateUpselingLanjutanMateriDetail($itemDetail, $personId): array
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (!$detail) {
            Log::warning("detailTargetKPI tidak ditemukan untuk item ID: {$itemDetail->id}");
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
            Log::warning("Target atau Tahun tidak valid: {$nilaiTarget}, {$tahun} untuk item ID: {$itemDetail->id}");
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

        if ($personId !== null) {
            $kodeKaryawan = karyawan::where('id', $personId)->first();

            if (!$kodeKaryawan) {
                return [
                    'progress' => 0,
                    'gap' => 0,
                    'pie_chart' => ['above' => 0, 'below' => 0],
                    'monthly_data' => [],
                    'daily_breakdown_per_month' => [],
                ];
            }

            $rkmQuery = RKM::whereBetween('created_at', [$start, $end])
                ->where('instruktur_key', $kodeKaryawan->kode_karyawan)
                ->where('tanggal_akhir', '<', now());
        } else {
            $rkmQuery = RKM::whereBetween('created_at', [$start, $end])->where('tanggal_akhir', '<', now());
        }

        $rkms = $rkmQuery->get(['id', 'created_at']);

        if ($rkms->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $rkmIds = $rkms->pluck('id');

        $rekomendasiRkmIds = RekomendasiLanjutan::whereIn('id_rkm', $rkmIds)->pluck('id_rkm');

        $hasRekomendasiMap = $rekomendasiRkmIds->flip();

        $totalData = $rkms->count();
        $totalRekomendasi = 0;

        $dailyData = [];
        $monthlyDataRaw = [];

        foreach ($rkms as $rkm) {
            $hasRekom = $hasRekomendasiMap->has($rkm->id);
            if ($hasRekom) {
                $totalRekomendasi++;
            }

            $dateObj = Carbon::parse($rkm->created_at);
            $dayKey = $dateObj->format('Y-m-d');
            $monthKey = $dateObj->format('Y-m');

            if (!isset($dailyData[$dayKey])) {
                $dailyData[$dayKey] = ['total' => 0, 'rekom' => 0];
            }
            $dailyData[$dayKey]['total']++;
            if ($hasRekom) {
                $dailyData[$dayKey]['rekom']++;
            }

            if (!isset($monthlyDataRaw[$monthKey])) {
                $monthlyDataRaw[$monthKey] = ['total' => 0, 'rekom' => 0];
            }
            $monthlyDataRaw[$monthKey]['total']++;
            if ($hasRekom) {
                $monthlyDataRaw[$monthKey]['rekom']++;
            }
        }

        $progress = $totalData > 0 ? round(($totalRekomendasi / $totalData) * 100, 1) : 0;

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $totalRekomendasi;
        $below = $totalData - $totalRekomendasi;

        $monthlyAverages = [];
        foreach ($monthlyDataRaw as $month => $data) {
            $rate = $data['total'] > 0 ? ($data['rekom'] / $data['total']) * 100 : 0;
            $monthlyAverages[$month] = round($rate, 1);
        }
        ksort($monthlyAverages);

        $dailyBreakdownPerMonth = [];
        $dailyValuesForMonthlyAvg = [];

        foreach ($dailyData as $dayKey => $data) {
            $dateObj = Carbon::parse($dayKey);
            $monthKey = $dateObj->format('Y-m');

            $rate = $data['total'] > 0 ? ($data['rekom'] / $data['total']) * 100 : 0;
            $roundedRate = round($rate, 1);

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dayKey] = $roundedRate;
        }
        ksort($dailyBreakdownPerMonth);

        foreach ($dailyBreakdownPerMonth as $month => $days) {
            ksort($dailyBreakdownPerMonth[$month]);
        }

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyAverages,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    private function calculateSertifikasiKompetensiInternalDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        $emptyResponse = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
        ];

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $startYear = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endYear = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = detailPersonKPI::where('detailTargetKey', $detail->id);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $detailPersons = $query->get();
        $totalData = $detailPersons->count();

        if ($totalData === 0) {
            return $emptyResponse;
        }

        $countAchieved = 0;
        $dailyValues = [];

        foreach ($detailPersons as $personItem) {
            $validSertifikasis = Sertifikasi::where('user_id', $personItem->id_karyawan)
                ->where('tanggal_berlaku_dari', '<=', $endYear)
                ->where(function ($q) use ($startYear) {
                    $q->where('tanggal_berlaku_sampai', '>=', $startYear)->orWhereNull('tanggal_berlaku_sampai');
                })
                ->get();

            $validSertifikasi = $validSertifikasis->count();

            if ($personId !== null) {
                $countAchieved += $validSertifikasi;

                foreach ($validSertifikasis as $cert) {
                    $tanggal = Carbon::parse($cert->tanggal_berlaku_dari);
                    if ($tanggal < $startYear) {
                        $tanggal = $startYear;
                    }

                    if ($tanggal >= $startYear && $tanggal <= $endYear) {
                        $dateKey = $tanggal->format('Y-m-d');
                        $nilaiItem = 1;

                        if (!isset($dailyValues[$dateKey])) {
                            $dailyValues[$dateKey] = [];
                        }
                        $dailyValues[$dateKey][] = $nilaiItem;
                    }
                }
            } else {
                if ($validSertifikasi > 0) {
                    $countAchieved += 1;

                    if ($validSertifikasis->isNotEmpty()) {
                        $firstCert = $validSertifikasis->sortBy('tanggal_berlaku_dari')->first();
                        $tanggal = Carbon::parse($firstCert->tanggal_berlaku_dari);
                        if ($tanggal < $startYear) {
                            $tanggal = $startYear;
                        }
                        if ($tanggal >= $startYear && $tanggal <= $endYear) {
                            $dateKey = $tanggal->format('Y-m-d');
                            $nilaiItem = 1;

                            if (!isset($dailyValues[$dateKey])) {
                                $dailyValues[$dateKey] = [];
                            }
                            $dailyValues[$dateKey][] = $nilaiItem;
                        }
                    }
                }
            }
        }

        if ($personId !== null) {
            $progress = min(100, $countAchieved * 100);
        } else {
            $progress = ($countAchieved / $totalData) * 100;
        }
        $progress = round($progress, 1);

        $gapRaw = $progress - 100;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        if ($personId !== null) {
            $above = $countAchieved;
            $below = 0;
        } else {
            $above = $countAchieved;
            $below = $totalData - $countAchieved;
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

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

    private function calculatePelatihanKompetensiEksternalDetail($itemDetail, $personId)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        $emptyResponse = [
            'progress' => 0,
            'gap' => 0,
            'pie_chart' => ['above' => 0, 'below' => 0],
            'monthly_data' => [],
            'daily_breakdown_per_month' => [],
        ];

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
            return $emptyResponse;
        }

        $nilaiTarget = (float) $detail->nilai_target;
        $tahun = (int) $detail->detail_jangka;

        if ($nilaiTarget <= 0 || $tahun < 2000 || $tahun > now()->year + 5) {
            return $emptyResponse;
        }

        $startYear = Carbon::createFromDate($tahun, 1, 1)->startOfDay();
        $endYear = Carbon::createFromDate($tahun, 12, 31)->endOfDay();

        $query = detailPersonKPI::where('detailTargetKey', $detail->id);

        if ($personId !== null) {
            $query->where('id_karyawan', $personId);
        }

        $detailPersons = $query->get();
        $totalData = $detailPersons->count();

        if ($totalData === 0) {
            return $emptyResponse;
        }

        $countAchieved = 0;
        $dailyValues = [];

        foreach ($detailPersons as $personItem) {
            $validSertifikasis = Pelatihan::where('user_id', $personItem->id_karyawan)
                ->whereYear('tanggal_selesai', [$startYear, $endYear])
                ->get();

            $validSertifikasi = $validSertifikasis->count();

            if ($personId !== null) {
                $countAchieved += $validSertifikasi;

                foreach ($validSertifikasis as $cert) {
                    $tanggal = Carbon::parse($cert->tanggal_berlaku_dari);
                    if ($tanggal < $startYear) {
                        $tanggal = $startYear;
                    }

                    if ($tanggal >= $startYear && $tanggal <= $endYear) {
                        $dateKey = $tanggal->format('Y-m-d');
                        $nilaiItem = 1;

                        if (!isset($dailyValues[$dateKey])) {
                            $dailyValues[$dateKey] = [];
                        }
                        $dailyValues[$dateKey][] = $nilaiItem;
                    }
                }
            } else {
                if ($validSertifikasi > 0) {
                    $countAchieved += 1;

                    if ($validSertifikasis->isNotEmpty()) {
                        $firstCert = $validSertifikasis->sortBy('tanggal_berlaku_dari')->first();
                        $tanggal = Carbon::parse($firstCert->tanggal_berlaku_dari);
                        if ($tanggal < $startYear) {
                            $tanggal = $startYear;
                        }
                        if ($tanggal >= $startYear && $tanggal <= $endYear) {
                            $dateKey = $tanggal->format('Y-m-d');
                            $nilaiItem = 1;

                            if (!isset($dailyValues[$dateKey])) {
                                $dailyValues[$dateKey] = [];
                            }
                            $dailyValues[$dateKey][] = $nilaiItem;
                        }
                    }
                }
            }
        }

        if ($personId !== null) {
            $progress = min(100, $countAchieved * 100);
        } else {
            $progress = ($countAchieved / $totalData) * 100;
        }
        $progress = round($progress, 1);

        $gapRaw = $progress - 100;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        if ($personId !== null) {
            $above = $countAchieved;
            $below = 0;
        } else {
            $above = $countAchieved;
            $below = $totalData - $countAchieved;
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

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

    //Education Manager
    private function calculatePengembanganKurikulumPelatihanDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->detail_jangka)) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $nilaiTarget = isset($detail->nilai_target) ? (float) $detail->nilai_target : 0;
        $tahun = (int) $detail->detail_jangka;

        if ($tahun < 2000 || $tahun > now()->year + 5) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $dataMateri = Materi::whereYear('created_at', $tahun)->get();

        $bulanYangAdaMateriList = $dataMateri
            ->pluck('created_at')
            ->map(function ($date) {
                return Carbon::parse($date)->month;
            })
            ->unique()
            ->values()
            ->toArray();

        $bulanYangAdaMateri = count($bulanYangAdaMateriList);
        $totalBulanDalamTahun = 12;

        if ($totalBulanDalamTahun == 0) {
            return [
                'progress' => 0,
                'gap' => 0,
                'pie_chart' => ['above' => 0, 'below' => 0],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $presentase = ($bulanYangAdaMateri / $totalBulanDalamTahun) * 100;
        $progress = round($presentase, 1);

        $gapRaw = $progress - 100;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $bulanYangAdaMateri;
        $below = $totalBulanDalamTahun - $bulanYangAdaMateri;

        $monthlyData = [];
        $dailyBreakdownPerMonth = [];

        for ($m = 1; $m <= 12; $m++) {
            $monthKey = "{$tahun}-" . str_pad($m, 2, '0', STR_PAD_LEFT);

            $hasMateri = in_array($m, $bulanYangAdaMateriList);
            $monthValue = $hasMateri ? 1.0 : 0.0;

            $monthlyData[$monthKey] = $monthValue;

            $dailyBreakdownPerMonth[$monthKey] = [];
        }

        ksort($monthlyData);
        ksort($dailyBreakdownPerMonth);

        return [
            'progress' => $progress,
            'gap' => $gap,
            'pie_chart' => ['above' => $above, 'below' => $below],
            'monthly_data' => $monthlyData,
            'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
        ];
    }

    private function calculatePeningkatanKnowledgeSharingDetail($itemDetail)
    {
        $detail = $itemDetail->detailTargetKPI->first();

        if (is_null($detail) || is_null($detail->nilai_target) || is_null($detail->detail_jangka)) {
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

        $dataMateri = ActivityInstruktur::whereYear('activity_date', $tahun)->where('activity_type', 'Sharing Knowledge')->get();

        if ($dataMateri->isEmpty()) {
            return [
                'progress' => 0,
                'gap' => rtrim(rtrim(sprintf('%.1f', 0 - $nilaiTarget), '0'), '.'),
                'pie_chart' => ['above' => 0, 'below' => Carbon::create($tahun, 1, 1)->weeksInYear],
                'monthly_data' => [],
                'daily_breakdown_per_month' => [],
            ];
        }

        $totalMingguDalamTahun = Carbon::create($tahun, 1, 1)->weeksInYear;

        $mingguYangSudahJalan = [];

        foreach ($dataMateri as $activity) {
            $nomorMinggu = Carbon::parse($activity->activity_date)->week;
            $mingguYangSudahJalan[$nomorMinggu] = true;
        }

        $jumlahMingguTerisi = count($mingguYangSudahJalan);

        if ($totalMingguDalamTahun == 0) {
            $progress = 0.0;
        } else {
            $progress = ($jumlahMingguTerisi / $totalMingguDalamTahun) * 100;
        }

        if ($progress > 100) {
            $progress = 100;
        }

        $progress = round($progress, 1);

        $gapRaw = $progress - $nilaiTarget;
        $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

        $above = $jumlahMingguTerisi;
        $below = $totalMingguDalamTahun - $jumlahMingguTerisi;
        if ($below < 0) {
            $below = 0;
        }

        $dailyValues = [];

        foreach ($dataMateri as $activity) {
            $tanggal = Carbon::parse($activity->activity_date);
            $dateKey = $tanggal->format('Y-m-d');

            $nilaiItem = 1;

            if (!isset($dailyValues[$dateKey])) {
                $dailyValues[$dateKey] = [];
            }
            $dailyValues[$dateKey][] = $nilaiItem;
        }

        $dailyAverages = [];
        foreach ($dailyValues as $dateStr => $values) {
            $dailyAverages[$dateStr] = round(array_sum($values) / count($values), 1);
        }

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

    //Sales & Marketing
    //Sales
    private function calculateTargetPenjualanTahunanDetail($itemDetail, $personId)
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

    $kodeKaryawan = null;
    if ($personId !== null) {
        $kodeKaryawan = Karyawan::where('id', $personId)->value('kode_karyawan');
    }

    $query = RKM::where('status', '0')
        ->whereYear('tanggal_awal', $tahun);

    if ($kodeKaryawan) {
        $query->where('sales_key', $kodeKaryawan);
    }

    $sales = $query->select(DB::raw('tanggal_awal, SUM(CAST(harga_jual AS UNSIGNED) * CAST(pax AS UNSIGNED)) as total'))
        ->groupBy('tanggal_awal')
        ->get();

    $totalSales = 0;
    $dailyBreakdownPerMonth = [];
    $monthlyDataTemp = [];

    foreach ($sales as $row) {
        $date = Carbon::parse($row->tanggal_awal);
        $dateKey = $date->format('Y-m-d');
        $monthKey = $date->format('Y-m');

        $total = (float) $row->total;
        $totalSales += $total;

        if (!isset($dailyBreakdownPerMonth[$monthKey])) {
            $dailyBreakdownPerMonth[$monthKey] = [];
        }
        $dailyBreakdownPerMonth[$monthKey][$dateKey] = round($total, 1);

        if (!isset($monthlyDataTemp[$monthKey])) {
            $monthlyDataTemp[$monthKey] = 0;
        }
        $monthlyDataTemp[$monthKey] += $total;
    }

    $monthlyData = [];
    foreach ($monthlyDataTemp as $month => $total) {
        $monthlyData[$month] = round($total, 1);
    }

    ksort($monthlyData);
    ksort($dailyBreakdownPerMonth);

    $progress = round($totalSales, 1);
    $gapRaw = $totalSales - $nilaiTarget;
    $gap = rtrim(rtrim(sprintf('%.1f', $gapRaw), '0'), '.');

    $above = $totalSales >= $nilaiTarget ? 1 : 0;
    $below = 1 - $above;

    return [
        'progress' => $progress,
        'gap' => $gap,
        'dataManual' => [
            'manual_document' => $detail->manual_document,
        ],
        'pie_chart' => ['above' => $above, 'below' => $below],
        'monthly_data' => $monthlyData,
        'daily_breakdown_per_month' => $dailyBreakdownPerMonth,
    ];
}

    // SPV Sales
    private function calculateMeningkatkanRevenuePerusahaanDetail($itemDetail)
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

        $peluangs = Peluang::with('rkm.perhitunganNetSales')->whereYear('created_at', $tahun)->get();

        $progress = 0;
        $dailyBreakdownPerMonth = [];

        foreach ($peluangs as $p) {
            $kotor = $p->harga * $p->pax;

            $totalBiaya = 0;
            $perhitungan = $p->rkm ? $p->rkm->perhitunganNetSales : null;
            if ($perhitungan) {
                foreach ($p->rkm->perhitunganNetSales as $perhitungan) {
                    $totalBiaya += $perhitungan->transportasi
                        + $perhitungan->akomodasi_peserta
                        + $perhitungan->akomodasi_tim
                        + $perhitungan->fresh_money
                        + $perhitungan->entertaint
                        + $perhitungan->souvenir
                        + $perhitungan->cashback
                        + $perhitungan->sewa_laptop;
                }
            }

            $bersih = $kotor - $totalBiaya;

            $progress += $bersih;

            $date = Carbon::parse($p->created_at);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            if (!isset($dailyBreakdownPerMonth[$monthKey][$dateKey])) {
                $dailyBreakdownPerMonth[$monthKey][$dateKey] = 0;
            }
            $dailyBreakdownPerMonth[$monthKey][$dateKey] += $bersih;
        }

        $monthlyData = [];
        foreach ($dailyBreakdownPerMonth as $month => $days) {
            $dailySums = array_values($days);
            $monthlyData[$month] = count($dailySums) > 0
                ? round(array_sum($dailySums) / count($dailySums), 1)
                : 0;
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

    private function calculateLaporanMOMDetail($itemDetail)
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

        $laporans = LaporanHarianSales::whereYear('created_at', $tahun)
            ->select(DB::raw('DATE(created_at) as tanggal, COUNT(*) as total'))
            ->groupBy('tanggal')
            ->get();

        $progress = 0;
        $totalProgress = 0;
        $dailyBreakdownPerMonth = [];
        $monthlyDataTemp = [];

        foreach ($laporans as $row) {
            $date = Carbon::parse($row->tanggal);
            $dateKey = $date->format('Y-m-d');
            $monthKey = $date->format('Y-m');

            $total = (float) $row->total;
            $totalProgress += $total;

            $progress = ($totalProgress / $totalProgress) * 100;    

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

    private function calculateKetepatanWaktuPoDetail($itemDetail)
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

        $pos = NomorModul::with('moduls')->whereYear('created_at', $tahun)->get();

        $totalPercent = 0;
        $count = 0;
        $dailyPercents = [];

        foreach ($pos as $po) {
            if (!$po->uploaded || !$po->moduls) {
                continue;
            }

            $uploaded = \Carbon\Carbon::parse($po->uploaded)->startOfDay();

            foreach ($po->moduls as $modul) {
                if (!$modul->awal_training) {
                    continue;
                }

                $awalTraining = \Carbon\Carbon::parse($modul->awal_training)->startOfDay();

                $daysBefore = $awalTraining->diffInDays($uploaded);

                if ($daysBefore >= 7) {
                    $percent = 100;
                } elseif ($daysBefore > 0) {
                    $percent = ($daysBefore * 100) / 7;
                } else {
                    $percent = 0;
                }

                $totalPercent += $percent;
                $count++;

                $dateKey = $uploaded->format('Y-m-d');
                if (!isset($dailyPercents[$dateKey])) {
                    $dailyPercents[$dateKey] = [];
                }
                $dailyPercents[$dateKey][] = $percent;
            }
        }

        $progress = ($count === 0) ? 0 : round($totalPercent / $count, 1);

        $dailyBreakdownPerMonth = [];
        $monthlyDataTemp = [];

        foreach ($dailyPercents as $dateKey => $percentsList) {
            $dailyAvg = (count($percentsList) > 0)
                ? round(array_sum($percentsList) / count($percentsList), 1)
                : 0;

            $dateObj = \Carbon\Carbon::parse($dateKey);
            $monthKey = $dateObj->format('Y-m');

            if (!isset($dailyBreakdownPerMonth[$monthKey])) {
                $dailyBreakdownPerMonth[$monthKey] = [];
            }
            $dailyBreakdownPerMonth[$monthKey][$dateKey] = $dailyAvg;

            if (!isset($monthlyDataTemp[$monthKey])) {
                $monthlyDataTemp[$monthKey] = [];
            }
            $monthlyDataTemp[$monthKey][] = $dailyAvg;
        }

        $monthlyData = [];
        foreach ($monthlyDataTemp as $month => $dailyAvgs) {
            $monthlyData[$month] = round(array_sum($dailyAvgs) / count($dailyAvgs), 1);
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

    //Overview KPI
    public function personalIndex()
    {
        return view('KPIdata.TargetSubDivisi.overviewKaryawan');
    }

    public function getDataOverviewPersonal(Request $request)
    {
        try {
            if (!Auth()->user()->karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data karyawan tidak ditemukan',
                ], 404);
            }

            $karyawanId = Auth()->user()->id;
            $tahunFilter = $request->tahun ?? now()->year;

            $calculateProgress = function($item, $personId) {
                $route = strtolower($item->asistant_route);
                
                // Target Office - GM
                if ($route === 'kepuasan pelanggan') {
                    return $this->calculateProgressKepuasanPelanggan($item, $personId);
                } elseif ($route === 'pemasukan kotor') {
                    return $this->calculatePemasukanKotor($item, $personId);
                } elseif ($route === 'pemasukan bersih') {
                    return $this->calculatePemasukanBersih($item, $personId);
                } elseif ($route === 'rasio biaya operasional terhadap revenue') {
                    return $this->calculateRasioBiayaOperasionalTerhadapRevenue($item, $personId);
                } elseif ($route === 'performa kpi departemen') {
                    return $this->calculatePerformaKPIDepartemen($item, $personId);
                
                // CS
                } elseif ($route === 'peserta puas dengan pelayanan dan fasilitas training') {
                    return $this->calculatePesertaPuasDenganPelayananDanFasilitasTraining($item, $personId);
                } elseif ($route === 'dorong inovasi pelayanan') {
                    return $this->calculateDorongInovasiPelayanan($item, $personId);
                } elseif ($route === 'penanganan komplain peserta') {
                    return $this->calculatePenangananKomplainPerseta($item, $personId);
                } else if ($route === 'report persiapan kelas') {
                    return $this->calculateReportPersiapanKelas($item, $personId);
                }
                
                // Finance
                elseif ($route === 'inisiatif efesiensi keuangan') {
                    return $this->calculateInisiatifEfisiensiKeuangan($item, $personId);
                } elseif ($route === 'outstanding') {
                    return $this->calculateOutstanding($item, $personId);
                } elseif ($route === 'mengurangi manual work dan error') {
                    return $this->calculateMengurangiManualWorkDanError($item, $personId);
                } elseif ($route === 'laporan analisis keuangan') {
                    return $this->calculateLaporanAnalisisKeuangan($item, $personId);
                
                // HRD
                } elseif ($route === 'pelaksanaan kegiatan karyawan') {
                    return $this->calculatePelaksanaanKegiatanKaryawan($item, $personId);
                } elseif ($route === 'pengeluaran biaya karyawan') {
                    return $this->calculatePengeluaranBiayaKaryawan($item, $personId);
                } elseif ($route === 'administrasi karyawan') {
                    return $this->calculateAdministrasiKaryawan($item, $personId);
                
                // Driver
                } elseif ($route === 'perbaikan kendaraan') {
                    return $this->calculatePerbaikanKendaraan($item, $personId);
                } elseif ($route === 'kontrol pengeluaran transportasi') {
                    return $this->calculateKontrolPengeluaranTransportasi($item, $personId);
                } elseif ($route === 'report kondisi kendaraan') {
                    return $this->calculateReportKondisiKendaraan($item, $personId);
                
                // OB
                } elseif ($route === 'feedback kebersihan dan kenyamanan') {
                    return $this->calculateFeedbackKebersihanDanKenyamanan($item, $personId);
                } elseif ($route === 'penyelesaian tugas harian') {
                    return $this->calculatePenyelesaianTugasHarian($item, $personId);
                    
                // Target ITSM
                } elseif ($route === 'kepuasan client itsm') {
                    return $this->calculateProgressKepuasanClientITSM($item, $personId);
                } elseif ($route === 'availability sistem internal kritis') {
                    return $this->calculateAvailabilitySistemInternalKritis($item, $personId);
                } elseif ($route === 'meningkatkan kepuasan dan loyalitas peserta/client') {
                    return $this->calculateMeningkatkanKepuasanDanLoyalitasPeserta($item, $personId);
                } elseif ($route === 'ketepatan waktu penyelesaian fitur') {
                    return $this->calculateProgressKetepatanWaktuPenyelesaianFitur($item, $personId);
                } elseif ($route === 'mengukur kualitas aplikasi agar minim bug') {
                    return $this->calculateMengukurKualitasAplikasiAgarMinimBug($item, $personId);
                } elseif ($route === 'konsistensi campaign digital') {
                    return $this->calculateKonsistensiCampaignDigital($item, $personId);
                } elseif ($route === 'efektifitas diital marketing') {
                    return $this->calculateEfektifitasDiitalMarketing($item, $personId);
                } elseif ($route === 'keberhasilan support memenuhi sla') {
                    return $this->calculateTingkatKeberhasilanSupportMemenuhiSLA($item, $personId);
                } elseif ($route === 'kualitas layanan exam') {
                    return $this->calculateKualitasLayananExam($item, $personId);
                
                // Education
                } elseif ($route === 'kepuasan peserta pelatihan') {
                    return $this->calculateKepuasanPesertaPelatihan($item, $personId);
                } elseif ($route === 'upseling lanjutan materi') {
                    return $this->calculateUpselingLanjutanMateri($item, $personId);
                } elseif ($route === 'sertifikasi kompetensi internal') {
                    return $this->calculateSertifikasiKompetensiInternal($item, $personId);
                } elseif ($route === 'pelatihan kompetensi eksternal') {
                    return $this->calculatePelatihanKompetensiEksternal($item, $personId);
                
                // Education Manager
                } elseif ($route === 'pengembangan kurikulum pelatihan') {
                    return $this->calculatePengembanganKurikulumPelatihan($item, $personId);
                } elseif ($route === 'peningkatan knowledge sharing') {
                    return $this->calculatePeningkatanKnowledgeSharing($item, $personId);
                }
                
                return null;
            };

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
            $distribusiStatus = ['Selesai' => 0, 'Aktif' => 0, 'Belum Mulai' => 0];

            foreach ($allTargets as $target) {
                $detail = $target->detailTargetKPI->first();
                if (!$detail) continue;

                $progress = $calculateProgress($target, $karyawanId);
                $nilaiTarget = $detail->nilai_target;
                $tipeTarget = $detail->tipe_target;

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
            $rataRataProgress = count($progressValuesNonZero) > 0 
                ? round(array_sum($progressValuesNonZero) / count($progressValuesNonZero), 2) 
                : 0;

            // --- STATISTIK PER TARGET (Inline) ---
            $statistikPerTarget = [];
            $detailPersonsStats = detailPersonKPI::whereHas('detailTargetKPI.targetKPI', function ($q) use ($tahunFilter) {
                    $q->whereYear('created_at', $tahunFilter);
                })
                ->where('id_karyawan', $karyawanId)
                ->with(['detailTargetKPI.targetKPI'])
                ->get();

            foreach ($detailPersonsStats as $dp) {
                $target = $dp->detailTargetKPI->targetKPI ?? null;
                if (!$target) continue;
                $detail = $target->detailTargetKPI->first();
                if (!$detail) continue;

                $progress = $calculateProgress($target, $karyawanId);
                $nilaiTarget = $detail->nilai_target;
                $tipeTarget = $detail->tipe_target;

                $statistikPerTarget[] = [
                    'judul' => $target->judul,
                    'periode' => $detail->jangka_target . ' ' . $detail->detail_jangka,
                    'tipe_target' => $tipeTarget,
                    'target' => $nilaiTarget,
                    'progress' => $progress ?? 0,
                    'status' => $progress >= $nilaiTarget ? 'Selesai' : ($progress > 0 ? 'Aktif' : 'Belum Mulai'),
                ];
            }

            $daftarTargetPribadi = [];
            $detailPersonsList = detailPersonKPI::whereHas('detailTargetKPI.targetKPI', function ($q) use ($tahunFilter) {
                    $q->whereYear('created_at', $tahunFilter);
                })
                ->where('id_karyawan', $karyawanId)
                ->with(['detailTargetKPI.targetKPI', 'detailTargetKPI'])
                ->get();

                foreach ($detailPersonsList as $dp) {
                    $item = $dp->detailTargetKPI->targetKPI ?? null;
                    if (!$item) continue;

                    $detail = $item->detailTargetKPI->first();
                    if (!$detail) continue;

                    $progress = $calculateProgress($item, $karyawanId);
                    $nilaiTarget = $detail->nilai_target;
                    $tipeTarget = $detail->tipe_target;

                    $status = $progress >= $nilaiTarget ? 'Selesai' : 'Aktif';

                    if ($progress === null) {
                        $progressDisplay = '-';
                    } elseif ($tipeTarget === 'rupiah') {
                        $progressDisplay = 'Rp ' . number_format($progress, 0, ',', '.');
                    } elseif ($tipeTarget === 'persen') {
                        $progressDisplay = round($progress, 2) . '%';
                    } else {
                        $progressDisplay = number_format($progress, 0, ',', '.');
                    }

                    $daftarTargetPribadi[] = [
                        'id' => $item->id,
                        'judul' => $item->judul,
                        'periode' => $detail->jangka_target . ' ' . $detail->detail_jangka,
                        'tipe_target' => $tipeTarget,
                        'target' => $nilaiTarget,
                        'progress' => round($progress),
                        'progress_display' => $progressDisplay,
                        'status' => $status,
                        'status_badge' => $status === 'Selesai' ? 'bg-success' : 'bg-warning text-dark',
                        'deskripsi' => $detail->deskripsi ?? '-',
                        'created_at' => $item->created_at->format('d M Y'),
                    ];
                }

            // --- INFO KARYAWAN ---
            $karyawan = karyawan::where('id', $karyawanId)->first();

            return response()->json([
                'success' => true,
                'user_info' => [
                    'nama' => $karyawan->nama_lengkap ?? '-',
                    'jabatan' => $karyawan->jabatan ?? '-',
                    'divisi' => $karyawan->divisi ?? '-',
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
            $item = $dp->detailTargetKPI->targetKPI ?? null;
            if (!$item) {
                continue;
            }

            $detail = $item->detailTargetKPI->first();
            if (!$detail) {
                continue;
            }

            $progress = $this->calculateProgress($item, $karyawanId);
            $nilaiTarget = $detail->nilai_target;
            $status = $progress >= $nilaiTarget ? 'Selesai' : 'Aktif';

            $targets[] = [
                'id' => $item->id,
                'judul' => $item->judul,
                'periode' => $detail->jangka_target . ' ' . $detail->detail_jangka,
                'target' => $nilaiTarget,
                'progress' => round($progress ?? 0, 2),
                'progress_display' => $progress !== null ? round($progress, 2) . '%' : '-',
                'status' => $status,
                'status_badge' => $status === 'Selesai' ? 'bg-success' : 'bg-warning text-dark',
                'deskripsi' => $detail->deskripsi ?? '-',
                'created_at' => $item->created_at->format('d M Y'),
            ];
        }

        return $targets;
    }

    private function calculateProgress($item, $personId)
    {
        switch (strtolower($item->asistant_route)) {
            //Target Office
            //GM
            case 'Kepuasan Pelanggan':
                return $this->calculateProgressKepuasanPelanggan($item, $personId);
            case 'Pemasukan Kotor':
                return $this->calculatePemasukanKotor($item, $personId);
            case 'pemasukan bersih':
                return $this->calculatePemasukanBersih($item, $personId);
            case 'rasio biaya operasional terhadap revenue':
                return $this->calculateRasioBiayaOperasionalTerhadapRevenue($item, $personId);
            case 'performa KPI departemen':
                return $this->calculatePerformaKPIDepartemen($item, $personId);
            //CS
            case 'peserta puas dengan pelayanan dan fasilitas training':
                return $this->calculatePesertaPuasDenganPelayananDanFasilitasTraining($item, $personId);
            case 'dorong inovasi pelayanan':
                return $this->calculateDorongInovasiPelayanan($item, $personId);
            case 'penanganan komplain perseta':
                return $this->calculatePenangananKomplainPerseta($item, $personId);
            case 'report persiapan kelas':
                return $this->calculateReportPersiapanKelas($item, $personId);
            //Finance
            case 'inisiatif efesiensi keuangan':
                return $this->calculateInisiatifEfisiensiKeuangan($item, $personId);
            case 'outstanding':
                return $this->calculateOutstanding($item, $personId);
            case 'mengurangi manual work dan error':
                return $this->calculateMengurangiManualWorkDanError($item, $personId);
            case 'laporan analisis keuangan':
                return $this->calculateLaporanAnalisisKeuangan($item, $personId);
            case 'penyelesaian tagihan perusahaan':
                return $this->calculatePenyelesaianTagihanPerusahaan($item, $personId);
            //HRD
            case 'pelaksanaan kegiatan karyawan':
                return $this->calculatePelaksanaanKegiatanKaryawan($item, $personId);
            case 'pengeluaran biaya karyawan':
                return $this->calculatePengeluaranBiayaKaryawan($item, $personId);
            //Driver
            case 'perbaikan kendaraan':
                return $this->calculatePerbaikanKendaraan($item, $personId);
            case 'kontrol pengeluaran transportasi':
                return $this->calculateKontrolPengeluaranTransportasi($item, $personId);
            case 'report kondisi kendaraan':
                return $this->calculateReportKondisiKendaraan($item, $personId);
            case 'feedback kenyamanan berkendara':
                return $this->calculateFeedbackKenyamananBerkendara($item, $personId);

            //OB
            case 'feedback kebersihan dan kenyamanan':
                return $this->calculateFeedbackKebersihanDanKenyamanan($item, $personId);

            //Target ITSM
            //All kecuali Koordinator ITSM
            case 'kepuasan client itsm':
                return $this->calculateProgressKepuasanClientITSM($item, $personId);
            case 'inovation adaption rate':
                return $this->calculateInovationAdaptionRate($item, $personId);
            //Koordinator ITSM
            case 'availability sistem internal kritis':
                return $this->calculateAvailabilitySistemInternalKritis($item, $personId);
            case 'meningkatkan kepuasan dan loyalitas peserta/client':
                return $this->calculateMeningkatkanKepuasanDanLoyalitasPeserta($item, $personId);
            //Programmer
            case 'ketepatan waktu penyelesaian fitur':
                return $this->calculateProgressKetepatanWaktuPenyelesaianFitur($item, $personId);
            case 'mengukur kualitas aplikasi agar minim bug':
                return $this->calculateMengukurKualitasAplikasiAgarMinimBug($item, $personId);
            //Tim Digital
            case 'konsistensi campaign digital':
                return $this->calculateKonsistensiCampaignDigital($item, $personId);
            case 'efektifitas diital marketing':
                return $this->calculateEfektifitasDiitalMarketing($item, $personId);
            //TS
            case 'keberhasilan support memenuhi sla':
                return $this->calculateTingkatKeberhasilanSupportMemenuhiSLA($item, $personId);
            case 'kualitas layanan exam':
                return $this->calculateKualitasLayananExam($item, $personId);

            //Education
            //Instruktur
            case 'kepuasan peserta pelatihan':
                return $this->calculateKepuasanPesertaPelatihan($item, $personId);
            case 'upseling lanjutan materi':
                return $this->calculateUpselingLanjutanMateri($item, $personId);
            case 'sertifikasi kompetensi internal':
                return $this->calculateSertifikasiKompetensiInternal($item, $personId);
            case 'pelatihan kompetensi eksternal':
                return $this->calculatePelatihanKompetensiEksternal($item, $personId);

            //Education Manager
            case 'pengembangan kurikulum pelatihan':
                return $this->calculatePengembanganKurikulumPelatihan($item, $personId);
            case 'peningkatan knowledge sharing':
                return $this->calculatePeningkatanKnowledgeSharing($item, $personId);

            //Sales & Marketing
            //Sales
            case 'target penjualan tahunan':
                return $this->calculateTargetPenjualanTahunan($item, $personId);
            // Sales & Marketing
            // SPV Sales
            case 'meningkatkan revenue perusahaan':
                return $this->calculateMeningkatkanRevenuePerusahaan($item, $personId);

            // Adm Sales
            case 'laporan mom':
                return $this->calculateLaporanMOM($item);

            // Adm Holding
            case 'ketepatan waktu po':
                return $this->calculateKetepatanWaktuPo($item);

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

        if ($jabatan === 'GM' || $jabatan === 'HRD') {
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

    private function calculateWorkingDays()
    {
        $year = now()->year;

        $start = Carbon::createFromDate($year, 1, 1);
        $end = Carbon::createFromDate($year, 12, 31);

        $holidays = [
            "$year-01-01", // New Year's Day - Tahun Baru Masehi
            "$year-01-16", // Ascension of the Prophet Muhammad (tanggal Hijriyah, bisa berubah)
            "$year-02-16", // Chinese New Year Joint Holiday (tanggal Imlek, bisa berubah)
            "$year-02-17", // Chinese New Year's Day (tanggal Imlek, bisa berubah)
            "$year-02-19", // Ramadan Start (tanggal Hijriyah, bisa berubah)
            "$year-03-18", // Nyepi Joint Holiday (Bali, Hindu New Year, bisa berubah)
            "$year-03-19", // Nyepi (Bali, Hindu New Year, bisa berubah)
            "$year-03-20", // Idul Fitri Joint Holiday (tanggal Hijriyah, bisa berubah)
            "$year-03-21", // Idul Fitri (tanggal Hijriyah, bisa berubah)
            "$year-03-22", // Idul Fitri Holiday (tanggal Hijriyah, bisa berubah)
            "$year-03-23", // Idul Fitri Joint Holiday (tanggal Hijriyah, bisa berubah)
            "$year-03-24", // Idul Fitri Joint Holiday (tanggal Hijriyah, bisa berubah)
            "$year-04-03", // Good Friday
            "$year-04-05", // Easter Sunday
            "$year-05-01", // International Labor Day
            "$year-05-14", // Ascension Day of Jesus Christ
            "$year-05-15", // Joint Holiday after Ascension Day
            "$year-05-27", // Idul Adha (tanggal Hijriyah, bisa berubah)
            "$year-05-28", // Joint Holiday for Idul Adha (tanggal Hijriyah, bisa berubah)
            "$year-05-31", // Waisak Day (Buddha's Anniversary)
            "$year-06-01", // Pancasila Day
            "$year-06-16", // Muharram / Islamic New Year (tanggal Hijriyah, bisa berubah)
            "$year-08-17", // Indonesian Independence Day
            "$year-08-25", // Maulid Nabi Muhammad (tanggal Hijriyah, bisa berubah)
            "$year-12-24", // Christmas Eve Joint Holiday
            "$year-12-25", // Christmas Day
            "$year-12-31", // New Year's Eve
        ];

        $period = CarbonPeriod::create($start, $end);

        $workingDays = 0;

        foreach ($period as $date) {
            if (!in_array($date->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]) 
                && !in_array($date->toDateString(), $holidays)) {
                $workingDays++;
            }
        }

        return $workingDays;
    }

    private function hitungJamKerja($startAt, $endAt)
    {
        $start = $startAt->copy();
        $end = $endAt->copy();

        $workStart = 8;
        $workEnd = 17;

        $totalMinutes = 0;

        while ($start->lt($end)) {

            // jika weekend skip
            if ($start->isWeekend()) {
                $start->addDay()->startOfDay();
                continue;
            }

            $dayWorkStart = $start->copy()->setHour($workStart)->setMinute(0)->setSecond(0);
            $dayWorkEnd = $start->copy()->setHour($workEnd)->setMinute(0)->setSecond(0);

            $rangeStart = $start->greaterThan($dayWorkStart) ? $start : $dayWorkStart;
            $rangeEnd = $end->lessThan($dayWorkEnd) ? $end : $dayWorkEnd;

            if ($rangeStart->lt($rangeEnd)) {
                $totalMinutes += $rangeStart->diffInMinutes($rangeEnd);
            }

            $start->addDay()->startOfDay();
        }

        return $totalMinutes / 60;
    }
}
