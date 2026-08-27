<?php

namespace App\Services\KPI\Dashboard;

use App\Models\karyawan;
use App\Models\nilaiKPI;
use App\Models\targetKPI;
use App\Traits\KPIFormatTrait;
use App\Traits\KPIResolverTrait;
use Illuminate\Support\Facades\Log;

class PerformanceDashboardService
{
    use KPIResolverTrait, KPIFormatTrait;

    public function getPerformanceData(array $filters)
    {
        $tahun = $filters['tahun'] ?? now()->year;
        $divisi = $filters['divisi'] ?? null;
        $jabatan = $filters['jabatan'] ?? null;
        $search = $filters['search'] ?? null;

        $query = karyawan::where('status_aktif', '1')
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi');

        if ($divisi) $query->where('divisi', $divisi);
        if ($jabatan) $query->where('jabatan', $jabatan);
        if ($search) $query->where('nama_lengkap', 'LIKE', "%{$search}%");

        $karyawans = $query->orderBy('nama_lengkap')->get();

        $users = [];
        foreach ($karyawans as $emp) {
            $kpiDetails = $this->getKPIDetails($emp->id, $tahun);
            $kpiScore = $this->calculatePerformanceScore($emp, $tahun);

            $assessment360Details = $this->getAssessment360Details($emp->id, $tahun);
            $score360 = $this->calculateThreeSixtyScore($emp->id, $tahun);

            $users[] = [
                'id' => $emp->id,
                'nama' => $emp->nama_lengkap,
                'jabatan' => $emp->jabatan,
                'divisi' => $emp->divisi,
                'kpi' => [
                    'score' => round($kpiScore, 1),
                    'grade' => $this->getGradeLabel($kpiScore),
                    'details' => $kpiDetails
                ],
                'assessment_360' => [
                    'score' => round($score360, 1),
                    'grade' => $this->getGradeLabel($score360),
                    'details' => $assessment360Details
                ]
            ];
        }

        return [
            'success' => true,
            'total' => count($users),
            'users' => $users
        ];
    }

    private function getKPIDetails($karyawanId, $tahun)
    {
        $targets = targetKPI::with(['detailTargetKPI.detailPersonKPI', 'detailTargetKPI.dataTarget'])
            ->whereYear('created_at', $tahun)
            ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($karyawanId) {
                $q->where('id_karyawan', $karyawanId);
            })
            ->get();

        $generalRoutes = [
            'pemasukan kotor', 'pemasukan bersih', 'target penjualan project tahunan', 'target penjualan tahunan',
            'rasio biaya operasional terhadap revenue', 'meningkatkan revenue perusahaan', 'customer acquisition cost',
            'biaya akuisisi perclient', 'performa kpi departemen', 'kepuasan pelanggan', 'peserta puas dengan pelayanan dan fasilitas training',
            'penanganan komplain perseta', 'penanganan komplain peserta', 'outstanding', 'laporan analisis keuangan',
            'pencairan biaya operasional', 'penyelesaian tagihan perusahaan', 'akurasi pencatatan masuk', 'pelaksanaan kegiatan karyawan',
            'pengeluaran biaya karyawan', 'perbaikan kendaraan', 'kontrol pengeluaran transportasi', 'report kondisi kendaraan',
            'feedback kenyamanan berkendaran', 'feedback kebersihan dan kenyamanan', 'kepuasan client itsm', 'inovation adaption rate',
            'availability sistem internal kritis', 'meningkatkan kepuasan dan loyalitas peserta/client', 'persentase gap kompetensi tim terhadap standar skill',
            'ketepatan waktu penyelesaian fitur', 'mengukur kualitas aplikasi agar minim bug', 'konsistensi campaign digital',
            'efektifitas digital marketing', 'keberhasilan support memenuhi sla', 'kualitas layanan exam', 'kepuasan peserta pelatihan',
            'upseling lanjutan materi', 'presentase kinerja instruktur', 'pengembangan kurikulum pelatihan', 'peningkatan knowledge sharing',
            'peningkatan kontribusi pelatihan', 'evaluasi kinerja instruktur', 'evaluasi kinerja sales', 'laporan mom',
            'akurasi kelengkapan data penjualan', 'ketepatan waktu po', 'kualitas dokumentasi support dan proctor', 'pendapatan penjualan project',
        ];

        $details = [];
        foreach ($targets as $target) {
            foreach ($target->detailTargetKPI as $detail) {
                $assignedPersons = $detail->detailPersonKPI->where('id_karyawan', $karyawanId);
                if ($assignedPersons->isEmpty()) continue;

                $targetForCalc = $this->prepareTargetForCalculation($target, $detail);
                $asistantRoute = strtolower($detail->dataTarget?->asistant_route ?? '');
                $personIdForCalc = in_array($asistantRoute, $generalRoutes) ? null : $karyawanId;

                $rawProgress = $this->resolveProgress($targetForCalc, $personIdForCalc);
                $nilaiTarget = $detail->dataTarget?->nilai_target ?? $detail->nilai_target;
                $tipeTarget  = $detail->tipe_target;

                $percent = 0;
                if ($rawProgress > 0 && $nilaiTarget > 0) {
                    if ($tipeTarget === 'rupiah' || $tipeTarget === 'angka') {
                        $percent = ($rawProgress / $nilaiTarget) * 100;
                    } else {
                        $percent = $rawProgress;
                    }
                }
                $percent = max(0, min(100, round($percent, 2)));

                $details[] = [
                    'judul'            => $target->judul,
                    'asistant_route'   => $detail->dataTarget?->asistant_route ?? '-',
                    'tipe_target'      => $tipeTarget,
                    'nilai_target'     => $nilaiTarget,
                    'progress'         => $percent,
                    'progress_display' => $this->formatProgressDisplay($rawProgress, $tipeTarget),
                    'status'           => $percent >= 100 ? 'Selesai' : ($percent > 0 ? 'Aktif' : 'Belum Mulai')
                ];
            }
        }
        return $details;
    }

    private function prepareTargetForCalculation($target, $currentDetail)
    {
        $targetClone = clone $target;
        $details = $target->detailTargetKPI->values()->all();
        $currentIndex = -1;

        foreach ($details as $index => $detail) {
            if ($detail->id === $currentDetail->id) {
                $currentIndex = $index;
                break;
            }
        }

        if ($currentIndex > 0) {
            $temp = $details[0];
            $details[0] = $details[$currentIndex];
            $details[$currentIndex] = $temp;
        }

        $targetClone->setRelation('detailTargetKPI', collect($details));
        return $targetClone;
    }

    private function formatProgressDisplay($rawProgress, $tipeTarget)
    {
        if ($rawProgress <= 0) return '-';
        if ($tipeTarget === 'rupiah') return 'Rp ' . number_format((float)$rawProgress, 0, ',', '.');
        elseif ($tipeTarget === 'persen') return round($rawProgress, 2) . '%';
        return number_format((float)$rawProgress, 0, ',', '.');
    }

    private function getAssessment360Details($karyawanId, $tahun)
    {
        $persentaseJenis = [
            'General Manager' => 35,
            'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
            'Rekan Kerja (Satu Divisi)' => 20,
            'Pekerja (Beda Divisi)' => 10,
            'Self Apprisial' => 5,
        ];

        $allNilaiKPI = nilaiKPI::where('id_evaluated', $karyawanId)->whereYear('created_at', $tahun)->get();

        $details = [];
        foreach ($persentaseJenis as $jenis => $bobot) {
            $nilaiForJenis = $allNilaiKPI->where('jenis_penilaian', $jenis)->pluck('nilai')->filter(fn($n) => is_numeric($n) && $n > 0);

            if ($nilaiForJenis->isNotEmpty()) {
                $avgNilai = $nilaiForJenis->avg();
                $score = ($avgNilai * $bobot) / 100;
                $details[] = [
                    'jenis_penilaian' => $jenis,
                    'bobot' => $bobot,
                    'rata_rata_nilai' => round($avgNilai, 1),
                    'score' => round($score, 1),
                    'jumlah_evaluator' => $nilaiForJenis->count()
                ];
            }
        }
        return $details;
    }

    private function calculatePerformanceScore($employee, $tahun)
    {
        $karyawanIds = [$employee->id];
        $targets = targetKPI::with(['detailTargetKPI.dataTarget'])
            ->whereYear('created_at', $tahun)
            ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($karyawanIds) {
                $q->whereIn('id_karyawan', $karyawanIds);
            })->get();

        if ($targets->isEmpty()) return 0;

        $generalRoutes = [
            'pemasukan kotor', 'pemasukan bersih', 'target penjualan project tahunan', 'target penjualan tahunan',
            'rasio biaya operasional terhadap revenue', 'meningkatkan revenue perusahaan', 'customer acquisition cost',
            'biaya akuisisi perclient', 'performa kpi departemen', 'kepuasan pelanggan', 'peserta puas dengan pelayanan dan fasilitas training',
            'penanganan komplain perseta', 'penanganan komplain peserta', 'outstanding', 'laporan analisis keuangan',
            'pencairan biaya operasional', 'penyelesaian tagihan perusahaan', 'akurasi pencatatan masuk', 'pelaksanaan kegiatan karyawan',
            'pengeluaran biaya karyawan', 'perbaikan kendaraan', 'kontrol pengeluaran transportasi', 'report kondisi kendaraan',
            'feedback kenyamanan berkendaran', 'feedback kebersihan dan kenyamanan', 'kepuasan client itsm', 'inovation adaption rate',
            'availability sistem internal kritis', 'meningkatkan kepuasan dan loyalitas peserta/client', 'persentase gap kompetensi tim terhadap standar skill',
            'ketepatan waktu penyelesaian fitur', 'mengukur kualitas aplikasi agar minim bug', 'konsistensi campaign digital',
            'efektifitas digital marketing', 'keberhasilan support memenuhi sla', 'kualitas layanan exam', 'kepuasan peserta pelatihan',
            'upseling lanjutan materi', 'presentase kinerja instruktur', 'pengembangan kurikulum pelatihan', 'peningkatan knowledge sharing',
            'peningkatan kontribusi pelatihan', 'evaluasi kinerja instruktur', 'evaluasi kinerja sales', 'laporan mom',
            'akurasi kelengkapan data penjualan', 'ketepatan waktu po', 'kualitas dokumentasi support dan proctor',
        ];

        $allProgressValues = [];
        $processedTargets = [];

        foreach ($targets as $target) {
            foreach ($target->detailTargetKPI as $detail) {
                $assignedIds = $detail->detailPersonKPI->whereIn('id_karyawan', $karyawanIds)->pluck('id_karyawan')->unique()->toArray();
                if (empty($assignedIds)) continue;

                foreach ($assignedIds as $personId) {
                    $targetKey = $target->id . '_' . $detail->id . '_' . $personId;
                    if (isset($processedTargets[$targetKey])) continue;
                    $processedTargets[$targetKey] = true;

                    $targetForCalc = $this->prepareTargetForCalculation($target, $detail);
                    $asistantRoute = strtolower($detail->dataTarget?->asistant_route ?? '');
                    $personIdForCalc = in_array($asistantRoute, $generalRoutes) ? null : $personId;

                    // Menggunakan trait resolveProgress
                    $rawProgress = (float) $this->resolveProgress($targetForCalc, $personIdForCalc);
                    $nilaiTarget = (float) ($detail->dataTarget?->nilai_target ?? $detail->nilai_target);
                    $tipeTarget = $detail->tipe_target;

                    $percent = 0;
                    if ($rawProgress > 0 && $nilaiTarget > 0) {
                        if ($tipeTarget === 'rupiah' || $tipeTarget === 'angka') {
                            $percent = ($rawProgress / $nilaiTarget) * 100;
                        } else {
                            $percent = $rawProgress;
                        }
                    }

                    $percent = max(0, min(100, round($percent, 2)));
                    $allProgressValues[] = $percent;
                }
            }
        }

        if (empty($allProgressValues)) return 0;
        return round(array_sum($allProgressValues) / count($allProgressValues), 2);
    }

    private function calculateThreeSixtyScore($employeeId, $tahun)
    {
        $persentaseJenis = [
            'General Manager' => 35,
            'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
            'Rekan Kerja (Satu Divisi)' => 20,
            'Pekerja (Beda Divisi)' => 10,
            'Self Apprisial' => 5,
        ];

        $allNilaiKPI = nilaiKPI::where('id_evaluated', $employeeId)->whereYear('created_at', $tahun)->get();
        if ($allNilaiKPI->isEmpty()) return 0;

        $jenisTotalRaw = [];
        foreach ($persentaseJenis as $jenis => $bobot) {
            $nilaiForJenis = $allNilaiKPI->where('jenis_penilaian', $jenis)->pluck('nilai')->filter(fn($n) => is_numeric($n) && $n > 0);
            if ($nilaiForJenis->isNotEmpty()) {
                $avgNilai = $nilaiForJenis->avg();
                $jenisTotalRaw[$jenis] = ($avgNilai * $bobot) / 100;
            }
        }

        return empty($jenisTotalRaw) ? 0 : round(array_sum($jenisTotalRaw), 2);
    }
}
