<?php

namespace App\Services\KPI\Dashboard;

use App\Models\karyawan;
use App\Models\targetKPI;
use App\Models\DetailTargetKPI;
use App\Models\nilaiKPI;
use App\Services\KPI\Jabatan\GMKPIService;
use App\Services\KPI\Jabatan\ProjectAdminKPIService;
use App\Services\KPI\Jabatan\SPVSalesKPIService;
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
                $nilaiForJenis = $collectionNilaiKPI->where('jenis_penilaian', $jenis)->pluck('nilai')->filter(fn($n) => is_numeric($n));
                if ($nilaiForJenis->isNotEmpty()) {
                    $jenisTotalRaw[$jenis] = ($nilaiForJenis->avg() * $bobot) / 100;
                }
            }
            return empty($jenisTotalRaw) ? 0 : round(array_sum($jenisTotalRaw), 1);
        };

        $targetKPIs = targetKPI::with(['detailTargetKPI.dataTarget', 'detailTargetKPI.detailPersonKPI', 'karyawan'])
            ->whereYear('created_at', $currentYear)
            ->whereHas('detailTargetKPI.detailPersonKPI', fn($q) => $q->where('id_karyawan', $targetEmployeeId))
            ->get();

        $allNilaiKPI = nilaiKPI::where('id_evaluated', $targetEmployeeId)->whereYear('created_at', $currentYear)->get();

        $allProgressValues = [];
        $monthly_progress = [];
        $daily_progress_per_month = [];

        $kpiHealth = ['on_track' => 0, 'at_risk' => 0, 'behind' => 0];
        $trends = ['up' => 0, 'down' => 0, 'stable' => 0];
        $consistency = ['stable' => 0, 'fluctuating' => 0];
        $insights = [];
        $topPerformers = [];
        $lowestPerformers = [];
        $categoryAggregate = [];
        $kpiCards = [];

        foreach ($targetKPIs as $target) {
            $detail = $target->detailTargetKPI->first(
                fn($d) => $d->detailPersonKPI->contains('id_karyawan', $targetEmployeeId)
            ) ?? $target->detailTargetKPI->first();

            if (!$detail) {
                continue;
            }

            $resolved = $this->resolveProgressByRoute($target, $detail, $targetEmployeeId);
            $calculation = $this->getCalculationByRoute($target, $targetEmployeeId);

            $progress = $resolved['progress'];
            $allProgressValues[] = $progress;

            $tipeTarget = $detail->tipe_target ?? ($detail->dataTarget?->tipe_target ?? 'persen');
            $nilaiTarget = (float) ($detail->nilai_target ?? ($detail->dataTarget?->nilai_target ?? 0));

            if (!empty($calculation['monthly_progress'])) {
                foreach ($calculation['monthly_progress'] as $month => $val) {
                    $monthly_progress[$month][] = $this->normalizeToPercent($val, $tipeTarget, $nilaiTarget);
                }
            }
            if (!empty($calculation['daily_progress_per_month'])) {
                foreach ($calculation['daily_progress_per_month'] as $month => $days) {
                    foreach ($days as $day => $val) {
                        $daily_progress_per_month[$month][$day][] = $this->normalizeToPercent($val, $tipeTarget, $nilaiTarget);
                    }
                }
            }

            if (isset($resolved['target_status'], $kpiHealth[$resolved['target_status']])) {
                $kpiHealth[$resolved['target_status']]++;
            }
            if (isset($resolved['trend'], $trends[$resolved['trend']])) {
                $trends[$resolved['trend']]++;
            }
            if (isset($calculation['consistency'], $consistency[$calculation['consistency']])) {
                $consistency[$calculation['consistency']]++;
            }

            if (!empty($resolved['insight'])) {
                $insights[] = ['kpi_title' => $target->judul, 'insight' => $resolved['insight']];
            }
            if (!empty($calculation['top_performer']['label'])) {
                $topPerformers[] = [
                    'kpi_title' => $target->judul,
                    'label' => $calculation['top_performer']['label'],
                    'value' => $calculation['top_performer']['value'],
                ];
            }
            if (!empty($calculation['lowest_performer']['label'])) {
                $lowestPerformers[] = [
                    'kpi_title' => $target->judul,
                    'label' => $calculation['lowest_performer']['label'],
                    'value' => $calculation['lowest_performer']['value'],
                ];
            }

            if (!empty($calculation['category_scores'])) {
                foreach ($calculation['category_scores'] as $cat => $val) {
                    if ($val <= 0) {
                        continue;
                    }
                    $categoryAggregate[$cat]['total'] = ($categoryAggregate[$cat]['total'] ?? 0) + $val;
                    $categoryAggregate[$cat]['count'] = ($categoryAggregate[$cat]['count'] ?? 0) + 1;
                }
            }

            if (!empty($resolved['monthly_progress'])) {
                $mp = [];
                foreach ($resolved['monthly_progress'] as $month => $val) {
                    $mp[$month] = $this->normalizeToPercent($val, $tipeTarget, $nilaiTarget);
                }
                ksort($mp);
                $kpiCards[] = [
                    'kpi_title' => $target->judul,
                    'progress' => $progress,
                    'status' => $resolved['target_status'] ?? 'behind',
                    'trend' => $resolved['trend'] ?? 'stable',
                    'trend_value' => $calculation['trend_value'] ?? 0,
                    'sparkline' => array_slice($mp, -6, 6, true),
                ];
            }
        }

        $avgMonthlyProgress = [];
        foreach ($monthly_progress as $month => $vals) {
            $avgMonthlyProgress[$month] = round(array_sum($vals) / count($vals), 1);
        }
        $avgDailyProgress = [];
        foreach ($daily_progress_per_month as $month => $days) {
            foreach ($days as $day => $vals) {
                $avgDailyProgress[$month][$day] = round(array_sum($vals) / count($vals), 1);
            }
        }

        $avgTargetYearly = count($allProgressValues) > 0 ? round(array_sum($allProgressValues) / count($allProgressValues), 1) : 0;
        $avgPenilaianYearly = $calculatePenilaianScore($allNilaiKPI);

        if ($avgTargetYearly == 0 && $avgPenilaianYearly == 0) {
            $nilaiKpiAnda = 0;
            $titleGetData = 'Tidak ada data';
        } elseif ($avgTargetYearly == 0) {
            $nilaiKpiAnda = round($avgPenilaianYearly * 0.4, 2);
            $titleGetData = 'Dari Penilaian';
        } elseif ($avgPenilaianYearly == 0) {
            $nilaiKpiAnda = $avgTargetYearly;
            $titleGetData = 'Dari Target KPI';
        } else {
            $nilaiKpiAnda = round($avgTargetYearly * 0.6 + $avgPenilaianYearly * 0.4, 2);
            $titleGetData = 'Gabungan Target KPI & Penilaian';
        }

        $kpiPerbulan = [];
        $now = now();
        for ($i = 3; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $key = $date->format('Y-m');
            $kpiPerbulan[] = [
                'bulan' => $date->locale('id')->isoFormat('MMMM YYYY'),
                'nilai' => $avgMonthlyProgress[$key] ?? 0,
            ];
        }

        $personalDashboard = [
            'nilai_kpi_anda' => $nilaiKpiAnda,
            'rata_rata_target_kpi' => $avgTargetYearly,
            'progress_kpi_perbulan' => $kpiPerbulan,
            'performance' => 0,
            'performance_title' => 'Stabil',
            'deadline' => "{$currentYear}-12-31 23:59:59",
            'countdown' => '',
            'titleGet_data' => $titleGetData,
            'daily_progress_per_month' => $avgDailyProgress,
            'monthly_progress' => $avgMonthlyProgress,
        ];

        $categoryRadar = [];
        foreach ($categoryAggregate as $cat => $agg) {
            $categoryRadar[] = ['category' => $cat, 'value' => round($agg['total'] / $agg['count'], 1)];
        }

        $kpiInsights = [
            'summary_cards' => [
                'total_kpi_tracked' => count($targetKPIs),
                'avg_progress' => $avgTargetYearly,
                'on_track_percentage' => count($targetKPIs) > 0 ? round(($kpiHealth['on_track'] / count($targetKPIs)) * 100, 1) : 0,
            ],
            'health_donut' => $kpiHealth,
            'trend_summary' => $trends,
            'consistency' => $consistency,
            'category_radar' => $categoryRadar,
            'kpi_cards' => array_slice($kpiCards, 0, 6),
            'insights_feed' => array_slice($insights, 0, 5),
            'leaderboard' => [
                'top' => array_slice($topPerformers, 0, 3),
                'lowest' => array_slice($lowestPerformers, 0, 3),
            ],
            'total_kpi_tracked' => count($targetKPIs),
        ];

        $penilaianBreakdown = [];
        foreach ($persentaseJenis as $jenis => $bobot) {
            $nilaiForJenis = $allNilaiKPI->where('jenis_penilaian', $jenis)->pluck('nilai')->filter(fn($n) => is_numeric($n));
            $avg = $nilaiForJenis->isNotEmpty() ? round($nilaiForJenis->avg(), 1) : 0;
            $penilaianBreakdown[] = [
                'jenis' => $jenis,
                'bobot' => $bobot,
                'rata_rata_nilai' => $avg,
                'kontribusi' => round(($avg * $bobot) / 100, 2),
            ];
        }

        $strengthThreshold = 80;
        $strengths = array_values(array_filter($penilaianBreakdown, fn($p) => $p['rata_rata_nilai'] >= $strengthThreshold));
        $growthAreas = array_values(array_filter($penilaianBreakdown, fn($p) => $p['rata_rata_nilai'] > 0 && $p['rata_rata_nilai'] < $strengthThreshold));

        $assessment360Data = [
            'total_score' => $avgPenilaianYearly,
            'radar_chart' => array_map(fn($p) => ['axis' => $p['jenis'], 'value' => $p['rata_rata_nilai']], $penilaianBreakdown),
            'breakdown_bars' => $penilaianBreakdown,
            'strengths' => $strengths,
            'growth_areas' => $growthAreas,
            'total_feedback_received' => $allNilaiKPI->count(),
        ];

        $result = [
            'output_1' => $personalDashboard,
            'output_2' => $kpiInsights,
            'output_3' => $assessment360Data,
        ];

        $roleWithCompanyView = ['GM', 'HRD', 'Direktur Utama', 'Direktur'];
        if (in_array($karyawan->jabatan, $roleWithCompanyView)) {
            $result['output_4'] = $this->getCompanyProgressOverview($currentYear);
        }

        return $result;
    }

    protected function normalizeToPercent($rawValue, $tipeTarget, $nilaiTarget): float
    {
        $raw = (float) $rawValue;
        $target = (float) $nilaiTarget;

        if (in_array($tipeTarget, ['rupiah', 'angka']) && $target > 0) {
            $raw = ($raw / $target) * 100;
        }

        return max(0, min(100, $raw));
    }

    protected function getRouteCalculators(): array
    {
        return [
            'pemasukan kotor' => fn($t, $p) => app(GMKPIService::class)->calculatePemasukanKotor($t, $p),
            'target penjualan project tahunan' => fn($t, $p) => app(GMKPIService::class)->calculateTargetPenjualanProjectTahunan($t, $p),
            'meningkatkan revenue perusahaan' => fn($t, $p) => app(SPVSalesKPIService::class)->calculateMeningkatkanRevenuePerusahaan($t, $p),
            'pendapatan penjualan project' => fn($t, $p) => app(ProjectAdminKPIService::class)->calculatePendapatanPenjualanProject($t, $p),
        ];
    }

    protected function resolveProgressByRoute($target, $detail, $personId): array
    {
        $route = strtolower($detail->dataTarget?->asistant_route ?? '');
        $tipeTarget = $detail->tipe_target ?? ($detail->dataTarget?->tipe_target ?? 'persen');
        $nilaiTarget = (float) ($detail->nilai_target ?? ($detail->dataTarget?->nilai_target ?? 0));

        $calculator = $this->getRouteCalculators()[$route] ?? null;

        if ($calculator) {
            $rawValue = (float) $calculator($target, $personId);
            $progress = $this->normalizeToPercent($rawValue, $tipeTarget, $nilaiTarget);

            return [
                'progress' => $progress,
                'raw_value' => $rawValue,
                'target_value' => $nilaiTarget,
                'target_status' => $progress >= 100 ? 'on_track' : ($progress >= 70 ? 'at_risk' : 'behind'),
                'trend' => 'stable',
                'monthly_progress' => [],
                'insight' => null,
            ];
        }

        $calc = $this->getCalculationByRoute($target, $personId);
        $rawValue = (float) ($calc['progress'] ?? 0);
        $progress = $this->normalizeToPercent($rawValue, $tipeTarget, $nilaiTarget);

        return [
            'progress' => $progress,
            'raw_value' => $rawValue,
            'target_value' => $nilaiTarget,
            'target_status' => $calc['target_status'] ?? 'behind',
            'trend' => $calc['trend'] ?? 'stable',
            'monthly_progress' => $calc['monthly_progress'] ?? [],
            'insight' => $calc['insight'] ?? null,
            'prediction' => $calc['prediction'] ?? null,
        ];
    }

    protected function getCompanyProgressOverview($currentYear)
    {
        $allTargets = targetKPI::with(['karyawan', 'detailTargetKPI.dataTarget', 'detailTargetKPI.detailPersonKPI.karyawan'])
            ->whereYear('created_at', $currentYear)
            ->get();

        $buckets = [];

        foreach ($allTargets as $target) {
            $personsByDivisi = [];
            foreach ($target->detailTargetKPI as $detail) {
                foreach ($detail->detailPersonKPI as $person) {
                    $k = $person->karyawan;
                    if (!$k || !$k->divisi) {
                        continue;
                    }
                    $personsByDivisi[$k->divisi][$k->id] = ['karyawan' => $k, 'detail' => $detail];
                }
            }

            foreach ($personsByDivisi as $divisi => $assignments) {
                if (!isset($buckets[$divisi])) {
                    $buckets[$divisi] = [
                        'kpi_ids' => [],
                        'employee_ids' => [],
                        'progresses' => [],
                        'predictions' => [],
                        'health' => ['on_track' => 0, 'at_risk' => 0, 'behind' => 0],
                        'total_achieved' => 0,
                        'total_target' => 0,
                    ];
                }

                $bucket = &$buckets[$divisi];
                $bucket['kpi_ids'][$target->id] = true;

                foreach ($assignments as $a) {
                    $k = $a['karyawan'];
                    $detail = $a['detail'];

                    $bucket['employee_ids'][$k->id] = true;

                    $resolved = $this->resolveProgressByRoute($target, $detail, $k->id);

                    $bucket['progresses'][] = $resolved['progress'];
                    $bucket['predictions'][] = $resolved['prediction'] ?? $resolved['progress'];
                    $bucket['total_achieved'] += $resolved['raw_value'];
                    $bucket['total_target'] += $resolved['target_value'];

                    $status = $resolved['target_status'];
                    if (isset($bucket['health'][$status])) {
                        $bucket['health'][$status]++;
                    }
                }
                unset($bucket);
            }
        }

        $overview = [];
        foreach ($buckets as $divisi => $b) {
            $overview[] = [
                'divisi' => $divisi,
                'total_kpi' => count($b['kpi_ids']),
                'total_karyawan' => count($b['employee_ids']),
                'avg_progress' => count($b['progresses']) ? round(array_sum($b['progresses']) / count($b['progresses']), 1) : 0,
                'avg_prediction' => count($b['predictions']) ? round(array_sum($b['predictions']) / count($b['predictions']), 1) : 0,
                'health' => $b['health'],
                'total_achieved' => $b['total_achieved'],
                'total_target' => $b['total_target'],
                'total_achieved_display' => number_format($b['total_achieved'], 0, ',', '.'),
                'total_target_display' => number_format($b['total_target'], 0, ',', '.'),
            ];
        }
        usort($overview, fn($a, $b) => $b['avg_progress'] <=> $a['avg_progress']);

        $monthlyRaw = nilaiKPI::whereYear('created_at', $currentYear)->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as bulan, ROUND(AVG(nilai), 1) as rata_rata')->groupBy('bulan')->orderBy('bulan')->pluck('rata_rata', 'bulan')->toArray();

        return [
            'overview' => $overview,
            'company_trend' => [
                'historical' => $monthlyRaw,
                'forecast' => $this->forecastNextMonths($monthlyRaw, 2),
            ],
        ];
    }

    public function getDivisiDrilldownData($divisi, $currentYear)
    {
        $targets = targetKPI::with(['karyawan', 'detailTargetKPI.dataTarget', 'detailTargetKPI.detailPersonKPI.karyawan'])
            ->whereYear('created_at', $currentYear)
            ->whereHas('detailTargetKPI.detailPersonKPI.karyawan', fn($q) => $q->where('divisi', $divisi))
            ->get();

        if ($targets->isEmpty()) {
            return ['divisi' => $divisi, 'monthly_progress' => [], 'team' => [], 'insights' => []];
        }

        $monthlyProgress = [];
        $teamMap = [];
        $insights = [];

        foreach ($targets as $target) {
            $insightAdded = false;

            foreach ($target->detailTargetKPI as $detail) {
                foreach ($detail->detailPersonKPI as $person) {
                    $k = $person->karyawan;
                    if (!$k || $k->divisi !== $divisi) {
                        continue;
                    }

                    $empId = $k->id;
                    if (!isset($teamMap[$empId])) {
                        $teamMap[$empId] = [
                            'nama_karyawan' => $k->nama_lengkap ?? '-',
                            'jabatan' => $k->jabatan ?? '-',
                            'progresses' => [],
                            'trend_up' => 0,
                            'trend_down' => 0,
                            'trend_stable' => 0,
                            'total_achieved' => 0,
                            'total_target' => 0,
                        ];
                    }

                    $resolved = $this->resolveProgressByRoute($target, $detail, $empId);

                    $teamMap[$empId]['progresses'][] = $resolved['progress'];
                    $teamMap[$empId]['total_achieved'] += $resolved['raw_value'];
                    $teamMap[$empId]['total_target'] += $resolved['target_value'];

                    $trendKey = 'trend_' . $resolved['trend'];
                    if (isset($teamMap[$empId][$trendKey])) {
                        $teamMap[$empId][$trendKey]++;
                    }

                    if (!empty($resolved['monthly_progress'])) {
                        foreach ($resolved['monthly_progress'] as $month => $val) {
                            $monthlyProgress[$month][] = $this->normalizeToPercent($val, $detail->tipe_target, $resolved['target_value']);
                        }
                    }

                    if (!$insightAdded && !empty($resolved['insight'])) {
                        $insights[] = ['kpi_title' => $target->judul, 'insight' => $resolved['insight']];
                        $insightAdded = true;
                    }
                }
            }
        }

        $avgMonthly = [];
        foreach ($monthlyProgress as $month => $vals) {
            $avgMonthly[$month] = round(array_sum($vals) / count($vals), 1);
        }
        ksort($avgMonthly);

        $team = [];
        foreach ($teamMap as $emp) {
            $avgProgress = count($emp['progresses']) ? round(array_sum($emp['progresses']) / count($emp['progresses']), 1) : 0;

            $dominantTrend = 'stable';
            if ($emp['trend_up'] > $emp['trend_down'] && $emp['trend_up'] > $emp['trend_stable']) {
                $dominantTrend = 'up';
            } elseif ($emp['trend_down'] > $emp['trend_up'] && $emp['trend_down'] > $emp['trend_stable']) {
                $dominantTrend = 'down';
            }

            $team[] = [
                'nama_karyawan' => $emp['nama_karyawan'],
                'jabatan' => $emp['jabatan'],
                'progress' => $avgProgress,
                'trend' => $dominantTrend,
                'total_achieved' => $emp['total_achieved'],
                'total_target' => $emp['total_target'],
                'total_achieved_display' => number_format($emp['total_achieved'], 0, ',', '.'),
                'total_target_display' => number_format($emp['total_target'], 0, ',', '.'),
            ];
        }
        usort($team, fn($a, $b) => $b['progress'] <=> $a['progress']);

        return [
            'divisi' => $divisi,
            'monthly_progress' => $avgMonthly,
            'team' => $team,
            'insights' => array_slice($insights, 0, 5),
        ];
    }

    protected function forecastNextMonths(array $monthlySeries, int $monthsAhead = 2): array
    {
        $values = array_values($monthlySeries);
        $n = count($values);
        if ($n < 2) {
            return [];
        }

        $sumX = 0;
        $sumY = 0;
        $sumXY = 0;
        $sumXX = 0;
        foreach ($values as $i => $y) {
            $sumX += $i;
            $sumY += $y;
            $sumXY += $i * $y;
            $sumXX += $i * $i;
        }

        $denominator = $n * $sumXX - $sumX * $sumX;
        $b = $denominator != 0 ? ($n * $sumXY - $sumX * $sumY) / $denominator : 0;
        $a = ($sumY - $b * $sumX) / $n;

        $lastKey = array_key_last($monthlySeries);
        $lastDate = Carbon::createFromFormat('Y-m', $lastKey);

        $forecast = [];
        for ($i = 1; $i <= $monthsAhead; $i++) {
            $x = $n - 1 + $i;
            $y = round(max(0, min(100, $a + $b * $x)), 1);
            $futureKey = $lastDate->copy()->addMonths($i)->format('Y-m');
            $forecast[$futureKey] = $y;
        }

        return $forecast;
    }

    public function getChartStatisticsData($userJabatan, $requestJabatan, $tahunFilter, $idTargetFilter, $bulanFilter)
    {
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
                $finalJabatanFilter = $isPermitted ? [$requestJabatan] : $allowedJabatans;
            } else {
                $finalJabatanFilter = $allowedJabatans;
            }
        }

        $query = targetKPI::with(['karyawan', 'detailTargetKPI.detailPersonKPI.karyawan']);
        if ($idTargetFilter) {
            $query->where('id', $idTargetFilter);
        }
        $query->whereYear('created_at', $tahunFilter);

        if ($finalJabatanFilter !== null && !empty($finalJabatanFilter)) {
            $query->whereHas('detailTargetKPI', function ($q) use ($finalJabatanFilter) {
                count($finalJabatanFilter) > 1 ? $q->whereIn('jabatan', $finalJabatanFilter) : $q->where('jabatan', $finalJabatanFilter[0]);
            });
        }

        $targets = $query->get();
        $allTargetData = [];
        $monthlyAggregates = [];
        $jabatanAggregates = [];
        $jabatanMonthlyAggregates = [];
        $stats = ['total_targets' => 0, 'completed_targets' => 0, 'achieved_targets' => 0, 'in_progress_targets' => 0];

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
                if (!$isDetailAllowed) continue;
            }

            $calculationData = $this->getCalculationByRoute($target, null);
            if (!$calculationData || !isset($calculationData['progress'])) {
                continue;
            }

            $rawProgress = (float) $calculationData['progress'];
            $tipeTarget  = $detail->tipe_target;
            $nilaiTarget = (float) $detail->nilai_target;
            $jabatan     = $detail->jabatan ?? 'Unknown';
            $monthlyData = $calculationData['monthly_data'] ?? [];

            $progressPercent = $this->normalizeToPercent($rawProgress, $tipeTarget, $nilaiTarget);

            $rawGap       = (float) ($calculationData['gap'] ?? 0);
            $gapPercent   = ($nilaiTarget > 0 && in_array($tipeTarget, ['rupiah', 'angka']))
                ? ($rawGap / $nilaiTarget) * 100
                : $rawGap;

            $stats['total_targets']++;
            if ($progressPercent >= 100) {
                $stats['completed_targets']++;
                $stats['achieved_targets']++;
            } else {
                $stats['in_progress_targets']++;
            }

            $allTargetData[] = [
                'id'             => $target->id,
                'judul'          => $target->judul,
                'jabatan'        => $jabatan,
                'progress'       => round($progressPercent, 2),        
                'raw_progress'   => $rawProgress,                    
                'target'         => $nilaiTarget,                     
                'gap'            => round($gapPercent, 2),             
                'asistant_route' => $target->asistant_route,
                'tipe_target'    => $tipeTarget,                       
            ];

            $jabatanAggregates[$jabatan][] = $progressPercent;

            foreach ($monthlyData as $monthKey => $avgScore) {
                if ($bulanFilter) {
                    $monthPart = (int) explode('-', $monthKey)[1];
                    if ($monthPart !== (int) $bulanFilter) continue;
                }

                $monthlyScorePercent = $this->normalizeToPercent((float) $avgScore, $tipeTarget, $nilaiTarget);

                $monthlyAggregates[$monthKey][]          = $monthlyScorePercent;
                $jabatanMonthlyAggregates[$jabatan][$monthKey][] = $monthlyScorePercent;
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

        return [
            'filters' => [
                'jabatan' => $requestJabatan,
                'bulan' => $bulanFilter,
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
    }

    public function getPersonalOverviewData($karyawanId, $tahunFilter)
    {
        $karyawan = karyawan::find($karyawanId);

        if (!$karyawan) {
            return ['error' => 'Data karyawan tidak ditemukan', 'code' => 404];
        }

        $currentYear = now()->year;

        // ✅ FILTER UTAMA: id_karyawan dipaksa di query targetKPI
        // Data karyawan lain TIDAK PERNAH diambil dari DB
        // whereHas + with keduanya difilter agar konsisten & efisien
        $allTargets = targetKPI::with([
                'karyawan',
                'detailTargetKPI' => function ($query) use ($karyawanId) {
                    $query->whereHas('detailPersonKPI', fn($q) => $q->where('id_karyawan', $karyawanId))
                        ->with([
                            'detailPersonKPI' => fn($q) => $q->where('id_karyawan', $karyawanId),
                            'dataTarget',
                        ]);
                },
            ])
            ->whereYear('created_at', $tahunFilter)
            ->whereHas('detailTargetKPI.detailPersonKPI', fn($q) => $q->where('id_karyawan', $karyawanId))
            ->get();

        $processedTargets = collect();
        $progressPercentagesRaw = collect();
        $aggregatedMonthlyProgress = [];
        $aggregatedMonthlyCount = [];

        foreach ($allTargets as $target) {
            foreach ($target->detailTargetKPI as $detail) {
                // ✅ Skip ini sekarang hanya sebagai safety net
                // Seharusnya tidak pernah trigger karena sudah difilter di query
                if ($detail->detailPersonKPI->isEmpty()) {
                    continue;
                }

                $nilaiTarget = $detail->dataTarget?->nilai_target ?? $detail->nilai_target;
                $tipeTarget = $detail->tipe_target;

                $result = $this->getCalculationByRoute($target, $karyawanId);
                $rawProgress = (float) ($result['progress'] ?? 0);

                // --- Agregasi Monthly Progress ---
                $monthlyProgressData = $result['monthly_progress'] ?? ($result['monthly_data'] ?? []);
                if ($monthlyProgressData instanceof \Illuminate\Support\Collection) {
                    $monthlyProgressData = $monthlyProgressData->toArray();
                }

                if (is_array($monthlyProgressData) && !empty($monthlyProgressData)) {
                    foreach ($monthlyProgressData as $monthKey => $value) {
                        $monthNum = null;
                        $finalValue = $value;

                        if (is_numeric($monthKey) && $monthKey >= 1 && $monthKey <= 12) {
                            $monthNum = (int) $monthKey;
                        } elseif (is_string($monthKey) && preg_match('/^\d{4}-\d{2}$/', $monthKey)) {
                            $monthNum = (int) substr($monthKey, 5, 2);
                        } elseif (is_array($value) || is_object($value)) {
                            $valArray = (array) $value;
                            $mKey = $valArray['month'] ?? ($valArray['bulan'] ?? null);
                            $mVal = $valArray['value'] ?? ($valArray['avg'] ?? ($valArray['progress'] ?? null));

                            if ($mKey !== null && $mVal !== null) {
                                $monthNum = is_numeric($mKey) ? (int) $mKey : (int) substr((string) $mKey, 5, 2);
                                $finalValue = $mVal;
                            }
                        }

                        if ($monthNum !== null && $monthNum >= 1 && $monthNum <= 12) {
                            $aggregatedMonthlyProgress[$monthNum] = ($aggregatedMonthlyProgress[$monthNum] ?? 0) + (float) $finalValue;
                            $aggregatedMonthlyCount[$monthNum] = ($aggregatedMonthlyCount[$monthNum] ?? 0) + 1;
                        }
                    }
                }

                // --- Hitung Persen Progress ---
                $percent = match ($tipeTarget) {
                    'rupiah', 'angka' => $nilaiTarget > 0 ? ($rawProgress / $nilaiTarget) * 100 : 0,
                    default => $rawProgress,
                };
                $percent = max(0, min(100, $percent));

                // --- Status ---
                $status = match (true) {
                    $tahunFilter < $currentYear => $percent >= 100 ? 'Selesai' : 'Gagal',
                    $tahunFilter == $currentYear => 'Sedang Berjalan',
                    default => 'Belum Mulai',
                };

                $statusBadge = match ($status) {
                    'Selesai' => 'bg-success',
                    'Gagal' => 'bg-dark',
                    'Sedang Berjalan' => 'bg-primary',
                    default => 'bg-secondary',
                };

                $progressDisplay = match ($tipeTarget) {
                    'rupiah' => 'Rp ' . number_format($rawProgress, 0, ',', '.'),
                    'persen' => round($rawProgress, 2) . '%',
                    default => number_format($rawProgress, 0, ',', '.'),
                };

                $processedTargets->push([
                    'id' => $target->id,
                    'judul' => $target->judul,
                    'asistant_route' => $detail->dataTarget?->asistant_route,
                    'periode' => $detail->jangka_target . ' ' . $detail->detail_jangka,
                    'tipe_target' => $tipeTarget,
                    'target' => $nilaiTarget,
                    'progress' => round($rawProgress),
                    'progress_display' => $progressDisplay,
                    'progress_percent' => round($percent, 2),
                    'status' => $status,
                    'status_badge' => $statusBadge,
                    'deskripsi' => $detail->deskripsi ?? '-',
                    'manual_value' => $detail->manual_value,
                    'created_at' => $target->created_at->format('d M Y'),
                    'data_detail' => $result['monthly_progress'] ?? ($result['monthly_data'] ?? []),
                ]);

                $progressPercentagesRaw->push($percent);
            }
        }

        $rataRataProgress = $progressPercentagesRaw->isNotEmpty()
            ? round($progressPercentagesRaw->sum() / $progressPercentagesRaw->count(), 2)
            : 0;

        $finalMonthlyProgress = [];
        for ($m = 1; $m <= 12; $m++) {
            $finalMonthlyProgress[$m] = isset($aggregatedMonthlyCount[$m]) && $aggregatedMonthlyCount[$m] > 0
                ? round($aggregatedMonthlyProgress[$m] / $aggregatedMonthlyCount[$m], 2)
                : null;
        }

        return [
            'success' => true,
            'user_info' => [
                'nama' => $karyawan->nama_lengkap ?? '-',
                'jabatan' => $karyawan->jabatan ?? '-',
                'divisi' => $karyawan->divisi ?? '-',
            ],
            'total_target' => $processedTargets->count(),
            'rata_rata_progress' => $rataRataProgress,
            'kpi_aktif' => $processedTargets->where('status', 'Sedang Berjalan')->count(),
            'kpi_selesai' => $processedTargets->where('status', 'Selesai')->count(),
            'statistik_per_target' => $processedTargets
                ->map(fn($t) => [
                    'judul' => $t['judul'],
                    'periode' => $t['periode'],
                    'tipe_target' => $t['tipe_target'],
                    'target' => $t['target'],
                    'progress' => $t['progress'],
                    'status' => $t['status'],
                ])
                ->values(),
            'distribusi_status' => [
                'Selesai' => $processedTargets->where('status', 'Selesai')->count(),
                'Gagal' => $processedTargets->where('status', 'Gagal')->count(),
                'Sedang Berjalan' => $processedTargets->where('status', 'Sedang Berjalan')->count(),
                'Belum Mulai' => $processedTargets->where('status', 'Belum Mulai')->count(),
            ],
            'daftar_target_pribadi' => $processedTargets->values(),
            'monthly_progress' => $finalMonthlyProgress,
            'tahun' => $tahunFilter,
        ];
    }

    public function getDepartmentOverviewData($divisiFilter, $tahunFilter)
    {
        $currentYear = now()->year;

        // 1. Ambil hanya kolom yang dibutuhkan, langsung sebagai array agar lebih ringan
        $karyawanList = karyawan::query()
            ->where('divisi', $divisiFilter)
            ->where('status_aktif', '1')
            ->whereNot('jabatan', 'Outsource')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNot('divisi', 'Direksi')
            ->whereNotNull('nip')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->select(['id', 'nama_lengkap', 'jabatan'])
            ->get();

        if ($karyawanList->isEmpty()) {
            return [
                'total_target' => 0,
                'rata_rata_progress' => 0,
                'kpi_aktif' => 0,
                'kpi_selesai' => 0,
                'kpi_gagal' => 0,
                'karyawan_departemen' => [],
                'statistik_karyawan' => [],
                'distribusi_nilai' => ['Sangat Baik' => 0, 'Baik' => 0, 'Cukup' => 0, 'Kurang' => 0, 'Sangat Kurang' => 0],
                'daftar_target_kpi' => [],
                'monthly_progress' => array_fill(1, 12, null),
            ];
        }

        // Gunakan array untuk lookup cepat O(1) tanpa collection overhead
        $personIds = $karyawanList->pluck('id')->all();
        $personIdSet = array_flip($personIds);

        // 2. Query target KPI dengan filter di DB, bukan di PHP
        //    Hanya load relasi yang benar-benar dipakai
        $allTargets = DetailTargetKPI::query()
            ->with(['targetKPI', 'dataTarget'])
            ->whereYear('created_at', $tahunFilter)
            ->whereHas('detailPersonKPI', function ($q) use ($personIds) {
                $q->whereIn('id_karyawan', $personIds);
            })
            ->get();

        // Inisialisasi variabel accumulator
        $employeeProgressMap = [];
        $employeeTargetStatusMap = [];
        $employeeTargetsMap = [];
        $processedTargets = [];
        $distribusi = ['Sangat Baik' => 0, 'Baik' => 0, 'Cukup' => 0, 'Kurang' => 0, 'Sangat Kurang' => 0];
        $aggregatedMonthlyProgress = [];
        $aggregatedMonthlyCount = [];
        $daftarTargetKPI = [];
        $uniqueTargetTitles = [];

        foreach ($allTargets as $detail) {
            $target = $detail->targetKPI;
            if (!$target) {
                continue;
            }

            $nilaiTarget = $detail->dataTarget?->nilai_target ?? $detail->nilai_target;
            $tipeTarget = $detail->tipe_target;

            // 3. Filter person langsung di query, bukan filter collection di PHP
            //    Hanya ambil id_karyawan yang ada di divisi filter
            $assignedPersonIds = $detail->detailPersonKPI()
                ->whereIn('id_karyawan', $personIds)
                ->pluck('id_karyawan')
                ->all();

            if (empty($assignedPersonIds)) {
                continue;
            }

            $targetProgressPercentagesRaw = [];

            foreach ($assignedPersonIds as $personId) {
                $uniqueKey = $detail->id . '_' . $personId;
                if (isset($processedTargets[$uniqueKey])) {
                    continue;
                }
                $processedTargets[$uniqueKey] = true;

                // Inisialisasi map karyawan jika belum ada
                if (!isset($employeeProgressMap[$personId])) {
                    $employeeProgressMap[$personId] = [];
                    $employeeTargetStatusMap[$personId] = [
                        'Sedang Berjalan' => 0,
                        'Selesai' => 0,
                        'Gagal' => 0,
                        'Belum Mulai' => 0,
                    ];
                    $employeeTargetsMap[$personId] = [];
                }

                // Hitung progress per person
                $result = $this->getCalculationByRoute($target, $personId);
                $rawProgress = (float) ($result['progress'] ?? 0);

                // 4. FIX BUG: Monthly progress diambil SETELAH $result didefinisikan
                $monthlyProgressData = $result['monthly_progress'] ?? ($result['monthly_data'] ?? []);
                if (is_array($monthlyProgressData) && !empty($monthlyProgressData)) {
                    foreach ($monthlyProgressData as $monthKey => $value) {
                        $monthNum = null;
                        if (is_numeric($monthKey) && $monthKey >= 1 && $monthKey <= 12) {
                            $monthNum = (int) $monthKey;
                        } elseif (is_string($monthKey) && preg_match('/^\d{4}-\d{2}$/', $monthKey)) {
                            $monthNum = (int) substr($monthKey, 5, 2);
                        }

                        if ($monthNum !== null && $monthNum >= 1 && $monthNum <= 12) {
                            $aggregatedMonthlyProgress[$monthNum] = ($aggregatedMonthlyProgress[$monthNum] ?? 0) + (float) $value;
                            $aggregatedMonthlyCount[$monthNum] = ($aggregatedMonthlyCount[$monthNum] ?? 0) + 1;
                        }
                    }
                }

                // Konversi progress ke persen
                $percent = 0.0;
                if (($tipeTarget === 'rupiah' || $tipeTarget === 'angka') && $nilaiTarget > 0) {
                    $percent = ($rawProgress / $nilaiTarget) * 100;
                } else {
                    $percent = $rawProgress;
                }
                $percent = max(0.0, min(100.0, $percent));
                $roundedPercent = round($percent, 2);

                // Tentukan status target
                if ($tahunFilter < $currentYear) {
                    $statusTarget = $roundedPercent >= 100 ? 'Selesai' : 'Gagal';
                } elseif ($tahunFilter == $currentYear) {
                    $statusTarget = 'Sedang Berjalan';
                } else {
                    $statusTarget = 'Belum Mulai';
                }

                $employeeTargetStatusMap[$personId][$statusTarget]++;
                $employeeProgressMap[$personId][] = $percent;

                $statusBadge = match ($statusTarget) {
                    'Selesai' => 'bg-success',
                    'Gagal' => 'bg-dark',
                    'Sedang Berjalan' => 'bg-primary',
                    default => 'bg-secondary',
                };

                $progressDisplay = match ($tipeTarget) {
                    'rupiah' => 'Rp ' . number_format($rawProgress, 2, ',', '.'),
                    'persen' => number_format($rawProgress, 2, ',', '.') . '%',
                    default => number_format($rawProgress, 2, ',', '.'),
                };

                $employeeTargetsMap[$personId][] = [
                    'judul' => $target->judul,
                    'periode' => $detail->jangka_target . ' ' . $detail->detail_jangka,
                    'tipe_target' => $tipeTarget,
                    'target' => $nilaiTarget,
                    'progress' => round($rawProgress, 2),
                    'progress_display' => $progressDisplay,
                    'progress_percent' => $roundedPercent,
                    'status' => $statusTarget,
                    'status_badge' => $statusBadge,
                ];

                $targetProgressPercentagesRaw[] = $percent;
            }

            // Rata-rata progress target ini (across all assigned persons in this division)
            $avgTarget = !empty($targetProgressPercentagesRaw)
                ? round(array_sum($targetProgressPercentagesRaw) / count($targetProgressPercentagesRaw), 2)
                : 0.0;

            // Status agregat target
            if ($tahunFilter < $currentYear) {
                $status = $avgTarget >= 100 ? 'Selesai' : 'Gagal';
            } elseif ($tahunFilter == $currentYear) {
                $status = 'Sedang Berjalan';
            } else {
                $status = 'Belum Mulai';
            }

            // Distribusi nilai (hanya jika ada progress)
            if ($avgTarget > 0) {
                if ($avgTarget >= 100) {
                    $distribusi['Sangat Baik']++;
                } elseif ($avgTarget >= 80) {
                    $distribusi['Baik']++;
                } elseif ($avgTarget >= 70) {
                    $distribusi['Cukup']++;
                } elseif ($avgTarget >= 60) {
                    $distribusi['Kurang']++;
                } else {
                    $distribusi['Sangat Kurang']++;
                }
            }

            // 5. Deduplikasi daftar target KPI tanpa collect()->unique()
            if (!isset($uniqueTargetTitles[$target->judul])) {
                $uniqueTargetTitles[$target->judul] = true;
                $daftarTargetKPI[] = [
                    'judul' => $target->judul,
                    'periode' => $detail->jangka_target . ' ' . $detail->detail_jangka,
                    'target' => $nilaiTarget,
                    'progress' => $avgTarget,
                    'status' => $status,
                ];
            }
        }

        // 6. Hitung rata-rata progress per karyawan menggunakan data yang sudah terkumpul
        //    TIDAK perlu query karyawan lagi
        $karyawanDepartemen = [];
        $totalProgressSum = 0.0;
        $totalEmployeeWithProgress = 0;

        foreach ($karyawanList as $karyawan) {
            $personId = $karyawan->id;
            $rawProgressList = $employeeProgressMap[$personId] ?? [];
            $statusData = $employeeTargetStatusMap[$personId] ?? [
                'Sedang Berjalan' => 0,
                'Selesai' => 0,
                'Gagal' => 0,
                'Belum Mulai' => 0,
            ];

            $rataRataProgressKaryawan = !empty($rawProgressList)
                ? round(array_sum($rawProgressList) / count($rawProgressList), 2)
                : 0.0;

            if (!empty($rawProgressList)) {
                $totalProgressSum += $rataRataProgressKaryawan;
                $totalEmployeeWithProgress++;
            }

            $karyawanDepartemen[] = [
                'id_karyawan' => $personId,
                'nama' => $karyawan->nama_lengkap,
                'jabatan' => $karyawan->jabatan,
                'total_target_sedang_berjalan' => $statusData['Sedang Berjalan'],
                'total_target_selesai' => $statusData['Selesai'],
                'total_target_gagal' => $statusData['Gagal'],
                'total_target_belum_mulai' => $statusData['Belum Mulai'],
                'jumlah_target' => count($rawProgressList),
                'rata_rata_progress' => $rataRataProgressKaryawan,
                'daftar_target_pribadi' => $employeeTargetsMap[$personId] ?? [],
            ];
        }

        $rataRataProgress = $totalEmployeeWithProgress > 0
            ? round($totalProgressSum / $totalEmployeeWithProgress, 2)
            : 0.0;

        // Build monthly progress final
        $finalMonthlyProgress = [];
        for ($m = 1; $m <= 12; $m++) {
            $finalMonthlyProgress[$m] = isset($aggregatedMonthlyCount[$m]) && $aggregatedMonthlyCount[$m] > 0
                ? round($aggregatedMonthlyProgress[$m] / $aggregatedMonthlyCount[$m], 2)
                : null;
        }

        // Count status KPI tanpa membuat collection baru
        $kpiAktif = 0;
        $kpiSelesai = 0;
        $kpiGagal = 0;
        foreach ($daftarTargetKPI as $tk) {
            match ($tk['status']) {
                'Sedang Berjalan' => $kpiAktif++,
                'Selesai' => $kpiSelesai++,
                'Gagal' => $kpiGagal++,
                default => null,
            };
        }

        return [
            'total_target' => count($daftarTargetKPI),
            'rata_rata_progress' => $rataRataProgress,
            'kpi_aktif' => $kpiAktif,
            'kpi_selesai' => $kpiSelesai,
            'kpi_gagal' => $kpiGagal,
            'karyawan_departemen' => $karyawanDepartemen,
            'statistik_karyawan' => $karyawanDepartemen, // Data sama, tidak perlu query ulang
            'distribusi_nilai' => $distribusi,
            'daftar_target_kpi' => $daftarTargetKPI,
            'monthly_progress' => $finalMonthlyProgress,
        ];
    }

    private function getEmployeeStatistics($karyawanIds, $employeeProgressMap, $employeeTargetStatusMap, $employeeTargetsMap = [])
    {
        return karyawan::whereIn('id', $karyawanIds)
            ->get()
            ->map(function ($karyawan) use ($employeeProgressMap, $employeeTargetStatusMap, $employeeTargetsMap) {
                $progressList = $employeeProgressMap[$karyawan->id] ?? [];
                $statusData = $employeeTargetStatusMap[$karyawan->id] ?? ['Sedang Berjalan' => 0, 'Selesai' => 0, 'Gagal' => 0, 'Belum Mulai' => 0];

                $rataRataProgress = !empty($progressList) ? round(array_sum($progressList) / count($progressList), 2) : 0.0;

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
            })
            ->values();
    }
}
