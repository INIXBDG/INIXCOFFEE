<?php

namespace App\Services\KPI\Dashboard;

use App\Models\karyawan;
use App\Models\targetKPI;
use App\Models\DetailTargetKPI;
use App\Models\nilaiKPI;
use App\Traits\KPIResolverTrait;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class OverviewDashboardService
{
    use KPIResolverTrait; // Membawa getCalculationByRoute dan resolveProgress

    public function getProgressDashboardData($currentUser, $idUser, $typeGet)
    {
        $currentYear = now()->year;
        $targetEmployeeId = filled($idUser) && filled($typeGet) ? $idUser : $currentUser->id;

        $karyawan = karyawan::find($targetEmployeeId);
        if (!$karyawan) {
            return ['error' => 'Karyawan tidak ditemukan', 'code' => 404];
        }

        $persentaseJenis = [
            'General Manager' => 35,
            'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
            'Rekan Kerja (Satu Divisi)' => 20,
            'Pekerja (Beda Divisi)' => 10,
            'Self Apprisial' => 5,
        ];

        $calculatePenilaianScore = function ($collectionNilaiKPI) use ($persentaseJenis) {
            $jenisTotalRaw = [];
            foreach ($persentaseJenis as $jenis => $bobot) {
                $nilaiForJenis = $collectionNilaiKPI->where('jenis_penilaian', $jenis)
                    ->pluck('nilai')->filter(fn($n) => is_numeric($n));
                if ($nilaiForJenis->isNotEmpty()) {
                    $jenisTotalRaw[$jenis] = ($nilaiForJenis->avg() * $bobot) / 100;
                }
            }
            return empty($jenisTotalRaw) ? 0 : round(array_sum($jenisTotalRaw), 2);
        };

        $calculateEmployeeAverageKPI = function ($empId, $yr) {
            $listKPI = targetKPI::with(['detailTargetKPI'])
                ->whereYear('created_at', $yr)
                ->whereHas('detailTargetKPI.detailPersonKPI', fn($q) => $q->where('id_karyawan', $empId))
                ->get();

            $allProgressValues = [];
            foreach ($listKPI as $item) {
                $detail = $item->detailTargetKPI->first();
                if (!$detail) continue;

                $result = $this->getCalculationByRoute($item, $empId);
                if (!isset($result['progress'])) continue;

                $progress = (float)$result['progress'];
                if ($detail->tipe_target === 'rupiah' && $detail->nilai_target > 0) {
                    $progress = ($progress / $detail->nilai_target) * 100;
                } elseif ($detail->tipe_target === 'angka' && $detail->nilai_target > 0) {
                    $progress = ($progress / $detail->nilai_target) * 100;
                }
                $allProgressValues[] = round(min($progress, 100), 2);
            }
            return count($allProgressValues) > 0 ? round(array_sum($allProgressValues) / count($allProgressValues), 2) : 0;
        };

        $listKPI = targetKPI::with(['detailTargetKPI'])
            ->whereYear('created_at', $currentYear)
            ->whereHas('detailTargetKPI.detailPersonKPI', fn($q) => $q->where('id_karyawan', $targetEmployeeId))
            ->get();

        $allNilaiKPI = nilaiKPI::where('id_evaluated', $targetEmployeeId)
            ->whereYear('created_at', $currentYear)->get();

        $allProgressValues = [];
        $monthly_progress = [];
        $daily_progress_per_month = [];

        foreach ($listKPI as $item) {
            $detail = $item->detailTargetKPI->first();
            if (!$detail) continue;

            $result = $this->getCalculationByRoute($item, $targetEmployeeId);
            if (!isset($result['progress'])) continue;

            $progress = (float)$result['progress'];
            if ($detail->tipe_target === 'rupiah' && $detail->nilai_target > 0) {
                $progress = ($progress / $detail->nilai_target) * 100;
            } elseif ($detail->tipe_target === 'angka' && $detail->nilai_target > 0) {
                $progress = ($progress / $detail->nilai_target) * 100;
            }

            $progressRounded = round(min($progress, 100), 2);
            $allProgressValues[] = $progressRounded;

            $monthKey = $item->created_at->format('Y-m');
            $dayKey = $item->created_at->format('Y-m-d');

            $monthly_progress[$monthKey][] = $progressRounded;
            $daily_progress_per_month[$monthKey][$dayKey][] = $progressRounded;
        }

        $avgTargetYearly = count($allProgressValues) > 0 ? round(array_sum($allProgressValues) / count($allProgressValues), 2) : 0;
        $avgPenilaianYearly = $calculatePenilaianScore($allNilaiKPI);

        if ($avgTargetYearly == 0 && $avgPenilaianYearly == 0) {
            $nilaiKpiAnda = 0; $titleGetData = 'Tidak ada data';
        } elseif ($avgTargetYearly == 0) {
            $nilaiKpiAnda = round($avgPenilaianYearly * 0.4, 2); $titleGetData = 'Dari Penilaian';
        } elseif ($avgPenilaianYearly == 0) {
            $nilaiKpiAnda = $avgTargetYearly; $titleGetData = 'Dari Target KPI';
        } else {
            $nilaiKpiAnda = round($avgTargetYearly * 0.6 + $avgPenilaianYearly * 0.4, 2); $titleGetData = 'Gabungan Target KPI & Penilaian';
        }

        $kpiPerbulan = [];
        $now = now();
        for ($i = 3; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $key = $date->format('Y-m');
            $nilai = isset($monthly_progress[$key]) && count($monthly_progress[$key]) > 0
                ? round(array_sum($monthly_progress[$key]) / count($monthly_progress[$key]), 2) : 0;
            $kpiPerbulan[] = ['bulan' => $date->locale('id')->isoFormat('MMMM YYYY'), 'nilai' => $nilai];
        }

        $personalDashboard = [
            'nilai_kpi_anda' => $nilaiKpiAnda,
            'progress_kpi_perbulan' => $kpiPerbulan,
            'performance' => 0, 'performance_title' => 'Stabil',
            'deadline' => "{$currentYear}-12-31 23:59:59", 'countdown' => '',
            'titleGet_data' => $titleGetData,
            'daily_progress_per_month' => $daily_progress_per_month,
            'monthly_progress' => $monthly_progress,
        ];

        // Hitung Tim Divisi
        $divisionTeamData = [];
        if ($currentUser && !empty($currentUser->divisi)) {
            $teamMembers = karyawan::where('divisi', $currentUser->divisi)->where('status_aktif', '1')
                ->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')
                ->whereNot('jabatan', 'Pilih Jabatan')->whereNotNull('nip')->whereNot('divisi', 'Direksi')->get();

            foreach ($teamMembers as $member) {
                $avgTargetTeam = $calculateEmployeeAverageKPI($member->id, $currentYear);
                $allNilaiKPITeam = nilaiKPI::where('id_evaluated', $member->id)->whereYear('created_at', $currentYear)->get();
                $avgPenilaianTeam = $calculatePenilaianScore($allNilaiKPITeam);

                if ($avgTargetTeam == 0 && $avgPenilaianTeam == 0) $nilaiKpiTeam = 0;
                elseif ($avgTargetTeam == 0) $nilaiKpiTeam = round($avgPenilaianTeam * 0.4, 2);
                elseif ($avgPenilaianTeam == 0) $nilaiKpiTeam = $avgTargetTeam;
                else $nilaiKpiTeam = round($avgTargetTeam * 0.6 + $avgPenilaianTeam * 0.4, 2);

                $divisionTeamData[] = [
                    'nama_karyawan' => $member->nama_lengkap, 'jabatan' => $member->jabatan,
                    'nilaitargetkpi' => $nilaiKpiTeam, 'performance' => 0,
                    'performance_title' => 'Stabil', 'nilai_performance' => 0,
                ];
            }
        }

        // Hitung Kinerja Semua Divisi
        $divisionKpiData = [];
        $divisions = karyawan::whereNotNull('divisi')->whereNotIn('divisi', ['', 'Pilih Divisi', 'Direksi'])
            ->distinct()->pluck('divisi');

        foreach ($divisions as $divisi) {
            $employees = karyawan::where('divisi', $divisi)->where('status_aktif', '1')->whereNot('jabatan', 'Outsource')
                ->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')
                ->whereNotNull('nip')->whereNot('divisi', 'Direksi')->pluck('id');

            if ($employees->isEmpty()) continue;

            $allDivisionProgress = [];
            foreach ($employees as $empId) {
                $listKPIDiv = targetKPI::with(['detailTargetKPI'])->whereYear('created_at', $currentYear)
                    ->whereHas('detailTargetKPI.detailPersonKPI', fn($q) => $q->where('id_karyawan', $empId))->get();

                foreach ($listKPIDiv as $item) {
                    $detail = $item->detailTargetKPI->first();
                    if (!$detail) continue;

                    $result = $this->getCalculationByRoute($item, $empId);
                    if (!isset($result['progress'])) continue;

                    $progress = (float)$result['progress'];
                    if ($detail->tipe_target === 'rupiah' && $detail->nilai_target > 0) $progress = ($progress / $detail->nilai_target) * 100;
                    elseif ($detail->tipe_target === 'angka' && $detail->nilai_target > 0) $progress = ($progress / $detail->nilai_target) * 100;

                    $allDivisionProgress[] = round(min($progress, 100), 2);
                }
            }

            $avgKpiValue = count($allDivisionProgress) > 0 ? round(array_sum($allDivisionProgress) / count($allDivisionProgress), 2) : 0;
            $divisionKpiData[] = [
                'divisi' => $divisi, 'nilai_kpi' => $avgKpiValue,
                'performance' => 0, 'performance_title' => 'Stabil', 'tahun' => $currentYear,
            ];
        }

        return [
            'output_1' => $personalDashboard,
            'output_2' => $divisionTeamData,
            'output_3' => $divisionKpiData,
        ];
    }

    public function getChartStatisticsData($userJabatan, $requestJabatan, $tahunFilter, $idTargetFilter, $bulanFilter)
    {
        $allowedJabatans = null;
        if ($userJabatan) {
            $jLower = strtolower($userJabatan);
            if (in_array($jLower, ['gm', 'hrd', 'direktur utama', 'direktur'])) $allowedJabatans = null;
            elseif ($jLower === 'koordinator itsm') $allowedJabatans = ['Programmer', 'Tim Digital', 'Technical Support', 'Koordinator ITSM'];
            elseif ($jLower === 'education manager') $allowedJabatans = ['Instruktur', 'Education Manager'];
            elseif ($jLower === 'spv sales') $allowedJabatans = ['SPV Sales', 'Sales'];
            else $allowedJabatans = [$userJabatan];
        }

        $finalJabatanFilter = null;
        if ($allowedJabatans === null) {
            $finalJabatanFilter = $requestJabatan ? [$requestJabatan] : null;
        } else {
            if ($requestJabatan) {
                $isPermitted = false;
                foreach ($allowedJabatans as $allowed) {
                    if (strtolower($allowed) === strtolower($requestJabatan)) { $isPermitted = true; break; }
                }
                $finalJabatanFilter = $isPermitted ? [$requestJabatan] : $allowedJabatans;
            } else {
                $finalJabatanFilter = $allowedJabatans;
            }
        }

        $query = targetKPI::with(['karyawan', 'detailTargetKPI.detailPersonKPI.karyawan']);
        if ($idTargetFilter) $query->where('id', $idTargetFilter);
        $query->whereYear('created_at', $tahunFilter);

        if ($finalJabatanFilter !== null && !empty($finalJabatanFilter)) {
            $query->whereHas('detailTargetKPI', function ($q) use ($finalJabatanFilter) {
                count($finalJabatanFilter) > 1 ? $q->whereIn('jabatan', $finalJabatanFilter) : $q->where('jabatan', $finalJabatanFilter[0]);
            });
        }

        $targets = $query->get();
        $allTargetData = []; $monthlyAggregates = []; $jabatanAggregates = []; $jabatanMonthlyAggregates = [];
        $stats = ['total_targets' => 0, 'completed_targets' => 0, 'achieved_targets' => 0, 'in_progress_targets' => 0];

        foreach ($targets as $target) {
            $detail = $target->detailTargetKPI->first();
            if (!$detail || !$detail->nilai_target || (float) $detail->nilai_target <= 0) continue;

            if ($finalJabatanFilter !== null) {
                $isDetailAllowed = false;
                foreach ($finalJabatanFilter as $allowed) {
                    if (strtolower($detail->jabatan) === strtolower($allowed)) { $isDetailAllowed = true; break; }
                }
                if (!$isDetailAllowed) continue;
            }

            $calculationData = $this->getCalculationByRoute($target, null);
            if (!$calculationData || !isset($calculationData['progress'])) continue;

            $progress = (float) $calculationData['progress'];
            $nilaiTarget = (float) $detail->nilai_target;
            $jabatan = $detail->jabatan ?? 'Unknown';
            $monthlyData = $calculationData['monthly_data'] ?? [];

            $stats['total_targets']++;
            if ($progress >= 100) $stats['completed_targets']++;
            if ($progress >= $nilaiTarget && $nilaiTarget > 0) $stats['achieved_targets']++;
            else $stats['in_progress_targets']++;

            $allTargetData[] = [
                'id' => $target->id, 'judul' => $target->judul, 'jabatan' => $jabatan,
                'progress' => $progress, 'target' => $nilaiTarget, 'gap' => $calculationData['gap'] ?? 0,
                'asistant_route' => $target->asistant_route,
            ];

            $jabatanAggregates[$jabatan][] = $progress;

            foreach ($monthlyData as $monthKey => $avgScore) {
                if ($bulanFilter) {
                    $monthPart = (int) explode('-', $monthKey)[1];
                    if ($monthPart !== (int) $bulanFilter) continue;
                }
                $monthlyAggregates[$monthKey][] = $avgScore;
                $jabatanMonthlyAggregates[$jabatan][$monthKey][] = $avgScore;
            }
        }

        $monthlyChart = [];
        foreach ($monthlyAggregates as $month => $scores) if (!empty($scores)) $monthlyChart[$month] = round(array_sum($scores) / count($scores), 1);
        ksort($monthlyChart);

        $jabatanChart = [];
        foreach ($jabatanAggregates as $jabatan => $scores) if (!empty($scores)) $jabatanChart[$jabatan] = round(array_sum($scores) / count($scores), 1);

        $jabatanMonthlyChart = [];
        foreach ($jabatanMonthlyAggregates as $jabatan => $months) {
            foreach ($months as $month => $scores) {
                if (!empty($scores)) $jabatanMonthlyChart[$jabatan][$month] = round(array_sum($scores) / count($scores), 1);
            }
        }

        $allProgressValues = [];
        foreach ($jabatanAggregates as $scores) $allProgressValues = array_merge($allProgressValues, $scores);
        $overallAverage = !empty($allProgressValues) ? round(array_sum($allProgressValues) / count($allProgressValues), 1) : 0;

        $yearlyMonthlyAverage = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthKey = "{$tahunFilter}-" . str_pad($m, 2, '0', STR_PAD_LEFT);
            $yearlyMonthlyAverage[$monthKey] = $monthlyChart[$monthKey] ?? 0;
        }

        return [
            'filters' => [
                'jabatan' => $requestJabatan, 'bulan' => $bulanFilter, 'tahun' => (int) $tahunFilter, 'user_scope' => $userJabatan,
            ],
            'summary' => [
                'overall_average' => $overallAverage, 'total_targets' => $stats['total_targets'],
                'completed_targets' => $stats['completed_targets'], 'achieved_targets' => $stats['achieved_targets'],
                'in_progress_targets' => $stats['in_progress_targets'],
                'completion_rate' => $stats['total_targets'] > 0 ? round(($stats['completed_targets'] / $stats['total_targets']) * 100, 1) : 0,
                'achievement_rate' => $stats['total_targets'] > 0 ? round(($stats['achieved_targets'] / $stats['total_targets']) * 100, 1) : 0,
            ],
            'charts' => [
                'monthly_trend' => $yearlyMonthlyAverage, 'by_jabatan' => $jabatanChart, 'jabatan_monthly' => $jabatanMonthlyChart,
            ],
            'targets_detail' => $allTargetData,
        ];
    }

    public function getPersonalOverviewData($karyawanId, $tahunFilter)
    {
        $karyawan = karyawan::find($karyawanId);
        if (!$karyawan) return ['error' => 'Data karyawan tidak ditemukan', 'code' => 404];

        $personId = $karyawanId;

        $allTargets = targetKPI::with(['karyawan', 'detailTargetKPI.detailPersonKPI.karyawan', 'detailTargetKPI.dataTarget'])
            ->whereYear('created_at', $tahunFilter)
            ->whereHas('detailTargetKPI.detailPersonKPI', fn($q) => $q->where('id_karyawan', $personId))
            ->get();

        $processedTargets = collect();
        $currentYear = now()->year;

        foreach ($allTargets as $target) {
            foreach ($target->detailTargetKPI as $detail) {
                if ($detail->detailPersonKPI->where('id_karyawan', $personId)->isEmpty()) continue;

                $nilaiTarget = $detail->dataTarget?->nilai_target ?? $detail->nilai_target;
                $tipeTarget  = $detail->tipe_target;

                $progress = $this->resolveProgress($target, $personId);
                if ($progress === null) continue;

                $percent = $nilaiTarget > 0 ? round(($progress / $nilaiTarget) * 100, 2) : 0;
                $percent = max(0, min(100, $percent));

                if ($tahunFilter < $currentYear) $status = $percent >= 100 ? 'Selesai' : 'Gagal';
                elseif ($tahunFilter == $currentYear) $status = 'Sedang Berjalan';
                else $status = 'Belum Mulai';

                $statusBadge = match ($status) { 'Selesai' => 'bg-success', 'Gagal' => 'bg-dark', 'Sedang Berjalan' => 'bg-primary', default => 'bg-secondary' };
                $progressDisplay = match (true) {
                    $tipeTarget === 'rupiah' => 'Rp ' . number_format($progress, 0, ',', '.'),
                    $tipeTarget === 'persen' => round($progress, 2) . '%',
                    default => number_format($progress, 0, ',', '.'),
                };

                $processedTargets->push([
                    'id' => $target->id, 'judul' => $target->judul, 'asistant_route' => $detail->dataTarget->asistant_route,
                    'periode' => $detail->jangka_target . ' ' . $detail->detail_jangka, 'tipe_target' => $tipeTarget,
                    'target' => $nilaiTarget, 'progress' => round($progress), 'progress_display' => $progressDisplay,
                    'progress_percent' => $percent, 'status' => $status, 'status_badge' => $statusBadge,
                    'deskripsi' => $detail->deskripsi ?? '-', 'manual_value' => $detail->manual_value,
                    'created_at' => $target->created_at->format('d M Y'),
                ]);
            }
        }

        $progressPercentages = $processedTargets->pluck('progress_percent')->filter(fn($v) => $v !== null);
        $rataRataProgress = $progressPercentages->isNotEmpty() ? round($progressPercentages->sum() / $progressPercentages->count(), 2) : 0;

        return [
            'success' => true,
            'user_info' => ['nama' => $karyawan->nama_lengkap ?? '-', 'jabatan' => $karyawan->jabatan ?? '-', 'divisi' => $karyawan->divisi ?? '-'],
            'total_target' => $processedTargets->count(),
            'rata_rata_progress' => $rataRataProgress,
            'kpi_aktif' => $processedTargets->where('status', 'Sedang Berjalan')->count(),
            'kpi_selesai' => $processedTargets->where('status', 'Selesai')->count(),
            'statistik_per_target' => $processedTargets->map(fn($t) => [
                'judul' => $t['judul'], 'periode' => $t['periode'], 'tipe_target' => $t['tipe_target'],
                'target' => $t['target'], 'progress' => $t['progress'], 'status' => $t['status'],
            ])->values(),
            'distribusi_status' => [
                'Selesai' => $processedTargets->where('status', 'Selesai')->count(),
                'Gagal' => $processedTargets->where('status', 'Gagal')->count(),
                'Sedang Berjalan' => $processedTargets->where('status', 'Sedang Berjalan')->count(),
                'Belum Mulai' => $processedTargets->where('status', 'Belum Mulai')->count(),
            ],
            'daftar_target_pribadi' => $processedTargets->values(),
            'tahun' => $tahunFilter,
        ];
    }

    public function getDepartmentOverviewData($divisiFilter, $tahunFilter)
    {
        $currentYear = now()->year;
        $karyawanDiDivisi = karyawan::where('divisi', $divisiFilter)->where('status_aktif', '1')
            ->whereNot('jabatan', 'Outsource')->where('kode_karyawan', 'NOT LIKE', 'OL%')->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')->whereNot('divisi', 'Direksi')->get();

        $karyawanIds = $karyawanDiDivisi->pluck('id')->toArray();
        $personIds = $karyawanIds;

        $allTargets = DetailTargetKPI::with(['targetKPI', 'dataTarget', 'detailPersonKPI.karyawan'])
            ->whereYear('created_at', $tahunFilter)
            ->whereHas('detailPersonKPI.karyawan', fn($q) => $q->whereIn('id', $personIds))
            ->get();

        $daftarTargetKPI = []; 
        $employeeProgressMap = []; 
        $employeeTargetStatusMap = []; 
        $employeeTargetsMap = [];
        $processedTargets = [];
        $distribusi = ['Sangat Baik' => 0, 'Baik' => 0, 'Cukup' => 0, 'Kurang' => 0, 'Sangat Kurang' => 0];

        foreach ($allTargets as $detail) {
            $target = $detail->targetKPI;
            if (!$target) continue;

            $nilaiTarget = $detail->dataTarget?->nilai_target ?? $detail->nilai_target;
            $tipeTarget = $detail->tipe_target;
            $assignedPersons = $detail->detailPersonKPI->whereIn('id_karyawan', $personIds)->groupBy('id_karyawan');
            if ($assignedPersons->isEmpty()) continue;

            $targetProgressPercentages = collect();
            foreach ($assignedPersons as $personId => $assignments) {
                $uniqueKey = $detail->id . '_' . $personId;
                if (isset($processedTargets[$uniqueKey])) continue;
                $processedTargets[$uniqueKey] = true;

                if (!isset($employeeProgressMap[$personId])) {
                    $employeeProgressMap[$personId] = [];
                    $employeeTargetStatusMap[$personId] = ['Sedang Berjalan' => 0, 'Selesai' => 0, 'Gagal' => 0, 'Belum Mulai' => 0];
                }

                $rawProgress = $this->resolveProgress($target, $personId);
                if ($rawProgress === null) continue;

                $percent = $nilaiTarget > 0 ? round(($rawProgress / $nilaiTarget) * 100, 2) : 0.00;
                $percent = max(0.00, min(100.00, $percent));

                $progressDisplay = match (true) {
                    $tipeTarget === 'rupiah' => 'Rp ' . number_format($rawProgress, 2, ',', '.'),
                    $tipeTarget === 'persen' => number_format($rawProgress, 2, ',', '.') . '%',
                    default => number_format($rawProgress, 2, ',', '.'),
                };

                if ($tahunFilter < $currentYear) $statusTarget = $percent >= 100 ? 'Selesai' : 'Gagal';
                elseif ($tahunFilter == $currentYear) $statusTarget = 'Sedang Berjalan';
                else $statusTarget = 'Belum Mulai';

                $employeeTargetStatusMap[$personId][$statusTarget]++;
                $statusBadge = match ($statusTarget) { 
                    'Selesai' => 'bg-success', 
                    'Gagal' => 'bg-dark', 
                    'Sedang Berjalan' => 'bg-primary', 
                    default => 'bg-secondary' 
                };

                if (!isset($employeeTargetsMap[$personId])) $employeeTargetsMap[$personId] = [];
                $employeeProgressMap[$personId][] = $percent;
                
                $employeeTargetsMap[$personId][] = [
                    'judul' => $target->judul, 
                    'periode' => $detail->jangka_target . ' ' . $detail->detail_jangka,
                    'tipe_target' => $tipeTarget, 
                    'target' => $nilaiTarget, 
                    'progress' => round($rawProgress, 2),
                    'progress_display' => $progressDisplay, 
                    'progress_percent' => $percent, 
                    'status' => $statusTarget,
                    'status_badge' => $statusBadge,
                ];

                $targetProgressPercentages->push($percent);
            }

            $avgTarget = $targetProgressPercentages->isNotEmpty() ? round($targetProgressPercentages->avg(), 2) : 0.00;

            if ($tahunFilter < $currentYear) $status = $avgTarget >= 100 ? 'Selesai' : 'Gagal';
            elseif ($tahunFilter == $currentYear) $status = 'Sedang Berjalan';
            else $status = 'Belum Mulai';

            if ($avgTarget > 0) {
                if ($avgTarget >= 100) $distribusi['Sangat Baik']++;
                elseif ($avgTarget >= 80) $distribusi['Baik']++;
                elseif ($avgTarget >= 70) $distribusi['Cukup']++;
                elseif ($avgTarget >= 60) $distribusi['Kurang']++;
                else $distribusi['Sangat Kurang']++;
            }

            $daftarTargetKPI[] = [
                'judul' => $target->judul, 
                'periode' => $detail->jangka_target . ' ' . $detail->detail_jangka,
                'target' => $nilaiTarget, 
                'progress' => $avgTarget, 
                'status' => $status,
            ];
        }

        $avgPerEmployee = [];
        foreach ($employeeProgressMap as $personId => $progressList) {
            if (!empty($progressList)) {
                $avgPerEmployee[$personId] = round(array_sum($progressList) / count($progressList), 2);
            }
        }
        
        $rataRataProgress = !empty($avgPerEmployee) ? round(array_sum($avgPerEmployee) / count($avgPerEmployee), 2) : 0.00;

        $karyawanDepartemen = $karyawanDiDivisi->map(function ($karyawan) use ($employeeProgressMap, $employeeTargetStatusMap, $employeeTargetsMap) {
            $personId = $karyawan->id;
            $progressList = collect($employeeTargetsMap[$personId] ?? [])->pluck('progress_percent');
            $statusData = $employeeTargetStatusMap[$personId] ?? ['Sedang Berjalan' => 0, 'Selesai' => 0, 'Gagal' => 0, 'Belum Mulai' => 0];
            
            $rataRataProgressKaryawan = $progressList->isNotEmpty() ? round($progressList->sum() / $progressList->count(), 2) : 0.00;

            return [
                'id_karyawan' => $karyawan->id, 
                'nama' => $karyawan->nama_lengkap, 
                'jabatan' => $karyawan->jabatan,
                'total_target_sedang_berjalan' => $statusData['Sedang Berjalan'], 
                'total_target_selesai' => $statusData['Selesai'],
                'total_target_gagal' => $statusData['Gagal'], 
                'total_target_belum_mulai' => $statusData['Belum Mulai'],
                'jumlah_target' => count($progressList), 
                'rata_rata_progress' => $rataRataProgressKaryawan,
                'daftar_target_pribadi' => $employeeTargetsMap[$personId] ?? [],
            ];
        })->values();

        return [
            'total_target' => count($daftarTargetKPI),
            'rata_rata_progress' => $rataRataProgress,
            'kpi_aktif' => collect($daftarTargetKPI)->where('status', 'Sedang Berjalan')->count(),
            'kpi_selesai' => collect($daftarTargetKPI)->where('status', 'Selesai')->count(),
            'kpi_gagal' => collect($daftarTargetKPI)->where('status', 'Gagal')->count(),
            'karyawan_departemen' => $karyawanDepartemen,
            'statistik_karyawan' => $this->getEmployeeStatistics($personIds, $employeeProgressMap, $employeeTargetStatusMap, $employeeTargetsMap),
            'distribusi_nilai' => $distribusi,
            'daftar_target_kpi' => collect($daftarTargetKPI)->unique('judul')->values()
        ];
    }

    private function getEmployeeStatistics($karyawanIds, $employeeProgressMap, $employeeTargetStatusMap, $employeeTargetsMap = [])
    {
        return karyawan::whereIn('id', $karyawanIds)->get()->map(function ($karyawan) use ($employeeProgressMap, $employeeTargetStatusMap, $employeeTargetsMap) {
            $progressList = $employeeProgressMap[$karyawan->id] ?? [];
            $statusData = $employeeTargetStatusMap[$karyawan->id] ?? ['Sedang Berjalan' => 0, 'Selesai' => 0, 'Gagal' => 0, 'Belum Mulai' => 0];
            
            $rataRataProgress = !empty($progressList) ? round(array_sum($progressList) / count($progressList), 2) : 0.00;

            return [
                'nama' => explode(' ', $karyawan->nama_lengkap)[0], 
                'jabatan' => $karyawan->jabatan, 
                'total_target' => count($progressList),
                'target_sedang_berjalan' => $statusData['Sedang Berjalan'], 
                'target_selesai' => $statusData['Selesai'],
                'target_gagal' => $statusData['Gagal'], 
                'target_belum_mulai' => $statusData['Belum Mulai'],
                'rata_rata_progress' => $rataRataProgress, 
                'daftar_target_pribadi' => $employeeTargetsMap[$karyawan->id] ?? [],
            ];
        })->values();
    }
}
