<?php

namespace App\Services\KPI\Dashboard;

use App\Models\karyawan;
use App\Models\nilaiKPI;
use App\Models\targetKPI;
use App\Traits\KPIResolverTrait;
use Carbon\Carbon;

class ExecutiveAnalyticsService
{
    use KPIResolverTrait;

    public function getTrendData(array $filters)
    {
        $tahun = $filters['tahun'] ?? now()->year;
        $karyawanIds = $this->getKaryawanIdsFromFilters($filters);

        $query = targetKPI::with([
            'karyawan',
            'detailTargetKPI' => function ($q) {
                $q->with(['dataTarget', 'detailPersonKPI.karyawan']);
            },
        ])
        ->whereYear('created_at', $tahun)
        ->whereHas('detailTargetKPI.detailPersonKPI', function ($q) use ($karyawanIds) {
            $q->whereIn('id_karyawan', $karyawanIds);
        });

        $targets = $query->get();
        $allProgressData = $this->extractAllProgressData($targets, $filters);
        $trendData = $this->calculateTrendMetrics($allProgressData, $filters['granularity']);
        $comparisonData = $this->calculatePeriodComparison($filters, $allProgressData);

        $overallAverage = $allProgressData->isNotEmpty() ? round($allProgressData->avg('progress'), 1) : 0;
        $completedTargets = $targets->filter(function ($t) {
            return in_array(strtolower($t->status ?? ''), ['completed', 'selesai', 'done']);
        })->count();

        return [
            'meta' => [
                'filters' => $filters,
                'generated_at' => now(),
                'data_points' => $trendData['count'] ?? 0
            ],
            'trend' => $trendData,
            'comparison' => $comparisonData,
            'summary' => [
                'overall_average' => $overallAverage,
                'total_targets' => $targets->count(),
                'completed_targets' => $completedTargets
            ],
            'insights' => $this->generateTrendInsights($trendData)
        ];
    }

    public function getPredictiveAnalysisData(array $filters)
    {
        $currentYear = $filters['tahun'] ?? now()->year;

        $targets = targetKPI::with([
            'karyawan',
            'detailTargetKPI' => function ($q) {
                $q->with(['dataTarget', 'detailPersonKPI.karyawan']);
            },
        ])
        ->whereYear('created_at', $currentYear)
        ->get();

        $historicalData = $this->extractAllProgressData($targets);
        $timeSeries = $this->prepareTimeSeries($historicalData, 'monthly');

        if (count($timeSeries) < 3) {
            return [
                'prediction' => [
                    'next_period' => null,
                    'next_3_periods' => [],
                    'confidence_level' => '30%',
                    'method' => 'insufficient_data'
                ],
                'debug' => [
                    'targets_found' => $targets->count(),
                    'progress_points' => $historicalData->count(),
                    'time_series_points' => count($timeSeries),
                ],
                'message' => 'Data historis belum cukup untuk prediksi akurat',
                'recommendations' => ['Kumpulkan minimal 3 bulan data historis untuk prediksi yang lebih akurat']
            ];
        }

        $predictions = $this->applyLinearRegression($timeSeries);
        $confidence = $this->calculatePredictionConfidence($timeSeries, $predictions);

        return [
            'prediction' => [
                'next_period' => $predictions['next'],
                'next_3_periods' => $predictions['next_3'],
                'confidence_level' => round($confidence * 100, 1) . '%',
                'method' => 'linear_regression',
                'slope' => $predictions['slope']
            ],
            'historical_basis' => array_slice($timeSeries, -12, null, true),
            'recommendations' => $this->generateRecommendations($predictions, $confidence)
        ];
    }

    public function getMatrixData(array $filters)
    {
        $tahun = $filters['tahun'] ?? now()->year;

        $query = karyawan::where('status_aktif', '1')
            ->whereNotNull('nama_lengkap')
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi');

        if (!empty($filters['divisi'])) $query->where('divisi', $filters['divisi']);
        if (!empty($filters['jabatan'])) $query->where('jabatan', $filters['jabatan']);
        if (!empty($filters['id_karyawan'])) $query->where('id', $filters['id_karyawan']);

        $employees = $query->get();

        if ($employees->isEmpty()) {
            return [
                'matrix' => [],
                'summary' => ['total_employees' => 0, 'message' => 'Tidak ada data karyawan untuk periode ini'],
                'visualization_data' => []
            ];
        }

        $matrixData = [];
        $allPoints = [];

        foreach ($employees as $emp) {
            $kpiTargets = targetKPI::with(['detailTargetKPI.detailPersonKPI'])
                ->whereYear('created_at', $tahun)
                ->whereHas('detailTargetKPI.detailPersonKPI', fn($q) => $q->where('id_karyawan', $emp->id))
                ->get();

            $progressValues = [];
            $processedTargets = [];

            foreach ($kpiTargets as $target) {
                foreach ($target->detailTargetKPI as $detail) {
                    $assignedIds = $detail->detailPersonKPI
                        ->where('id_karyawan', $emp->id)
                        ->pluck('id_karyawan')
                        ->unique()
                        ->toArray();

                    if (empty($assignedIds)) continue;

                    foreach ($assignedIds as $personId) {
                        $targetKey = $target->id . '_' . $detail->id . '_' . $personId;
                        if (isset($processedTargets[$targetKey])) continue;
                        $processedTargets[$targetKey] = true;

                        $result = $this->getCalculationByRoute($target, $personId);
                        if (!$result || !isset($result['progress'])) continue;

                        $rawProgress = $this->normalizeNumber($result['progress']);
                        $percent = max(0, min(100, round($rawProgress, 2)));
                        $progressValues[] = $percent;
                    }
                }
            }

            $performance = !empty($progressValues) ? round(array_sum($progressValues) / count($progressValues), 1) : 0;
            $penilaian = nilaiKPI::where('id_evaluated', $emp->id)->whereYear('created_at', $tahun)->get();
            $bobotJenis = [
                'General Manager' => 35,
                'Manager/SPV/Team Leader (Atasan Langsung)' => 30,
                'Rekan Kerja (Satu Divisi)' => 20,
                'Pekerja (Beda Divisi)' => 10,
                'Self Apprisial' => 5,
            ];

            $jenisTotalRaw = [];
            foreach ($bobotJenis as $jenis => $bobot) {
                $nilaiForJenis = $penilaian->where('jenis_penilaian', $jenis)
                    ->pluck('nilai')
                    ->filter(fn($n) => is_numeric($n) && $n > 0);

                if ($nilaiForJenis->isNotEmpty()) {
                    $avgNilai = $nilaiForJenis->avg();
                    $jenisTotalRaw[$jenis] = ($avgNilai * $bobot) / 100;
                }
            }
            $potential = empty($jenisTotalRaw) ? 0 : round(array_sum($jenisTotalRaw), 1);

            $perfLevel = $performance >= 75 ? 'high' : ($performance >= 50 ? 'moderate' : 'low');
            $potenLevel = $potential >= 70 ? 'high' : ($potential >= 40 ? 'moderate' : 'low');

            $quadrant = match (true) {
                $perfLevel === 'high' && $potenLevel === 'high' => 'star',
                $perfLevel === 'moderate' && $potenLevel === 'high' => 'high_potential',
                $perfLevel === 'low' && $potenLevel === 'high' => 'potential_gem',
                $perfLevel === 'high' && $potenLevel === 'moderate' => 'high_performer',
                $perfLevel === 'moderate' && $potenLevel === 'moderate' => 'core_player',
                $perfLevel === 'low' && $potenLevel === 'moderate' => 'inconsistent',
                $perfLevel === 'high' && $potenLevel === 'low' => 'solid_performer',
                $perfLevel === 'moderate' && $potenLevel === 'low' => 'average_performer',
                $perfLevel === 'low' && $potenLevel === 'low' => 'risk',
                default => 'core_player'
            };

            $strengths = [];
            if ($performance >= 80) $strengths[] = 'Kinerja konsisten di atas target';
            elseif ($performance >= 70) $strengths[] = 'Kinerja stabil dan dapat diandalkan';
            if ($potential >= 75) $strengths[] = 'Potensi pengembangan tinggi';
            elseif ($potential >= 60) $strengths[] = 'Menunjukkan tren peningkatan yang positif';
            if ($potential >= 80) $strengths[] = 'Penilaian 360° sangat baik';
            elseif ($potential >= 70) $strengths[] = 'Penilaian rekan kerja positif';
            if (empty($strengths)) $strengths[] = 'Dalam proses pengembangan';

            $areas = [];
            if ($performance < 60) $areas[] = 'Fokus pada peningkatan kualitas eksekusi target';
            elseif ($performance < 75) $areas[] = 'Optimalkan konsistensi pencapaian target';
            if ($potential < 50) $areas[] = 'Perlu eksposur ke target yang lebih beragam untuk pengembangan skill';
            if ($potential < 60 && $potential > 0) $areas[] = 'Perlu peningkatan kolaborasi dan komunikasi dengan tim';
            if (empty($areas)) $areas[] = 'Pertahankan kinerja saat ini';

            $matrixData[] = [
                'id' => $emp->id,
                'nama' => $emp->nama_lengkap,
                'jabatan' => $emp->jabatan,
                'divisi' => $emp->divisi,
                'performance_score' => $performance,
                'potential_score' => $potential,
                'three_sixty_score' => $potential,
                'quadrant' => $quadrant,
                'key_strengths' => $strengths,
                'development_areas' => $areas
            ];

            $allPoints[] = [
                'x' => $performance,
                'y' => $potential,
                'name' => $emp->nama_lengkap,
                'jabatan' => $emp->jabatan,
                'divisi' => $emp->divisi,
                'quadrant' => $quadrant,
                'three_sixty' => $potential,
                'type' => 'unified'
            ];
        }

        $matrix = [
            'star' => [], 'high_potential' => [], 'potential_gem' => [],
            'high_performer' => [], 'core_player' => [], 'inconsistent' => [],
            'solid_performer' => [], 'average_performer' => [], 'risk' => [],
        ];

        foreach ($matrixData as $emp) {
            $matrix[$emp['quadrant']][] = $emp;
        }

        $avgPerformance = collect($matrixData)->avg('performance_score') ?? 0;
        $avgPotential = collect($matrixData)->avg('potential_score') ?? 0;
        $highPotentialCount = count($matrix['star']) + count($matrix['high_potential']) + count($matrix['potential_gem']);

        return [
            'matrix' => $matrix,
            'summary' => [
                'total_employees' => count($employees),
                'high_potential_count' => $highPotentialCount,
                'avg_performance' => round($avgPerformance, 1),
                'avg_potential' => round($avgPotential, 1),
                'avg_three_sixty' => round($avgPotential, 1),
                'type' => 'unified'
            ],
            'visualization_data' => $allPoints
        ];
    }

    private function getKaryawanIdsFromFilters($filters)
    {
        $query = karyawan::where('status_aktif', '1')
            ->whereNotNull('nama_lengkap')
            ->whereNot('jabatan', 'Outsource')
            ->where('kode_karyawan', 'NOT LIKE', 'OL%')
            ->whereNot('jabatan', 'Pilih Jabatan')
            ->whereNotNull('nip')
            ->whereNot('divisi', 'Direksi');

        if (!empty($filters['divisi'])) $query->where('divisi', $filters['divisi']);
        if (!empty($filters['jabatan'])) $query->where('jabatan', $filters['jabatan']);
        if (!empty($filters['id_karyawan'])) $query->where('id', $filters['id_karyawan']);

        return $query->pluck('id')->toArray();
    }

    private function extractAllProgressData($targets, $filters = [])
    {
        $progressData = [];
        $processedTargets = [];

        foreach ($targets as $target) {
            foreach ($target->detailTargetKPI as $detail) {
                $assignedPersons = $detail->detailPersonKPI
                    ->whereIn('id_karyawan', $this->getKaryawanIdsFromFilters($filters))
                    ->groupBy('id_karyawan');

                if ($assignedPersons->isEmpty()) continue;

                foreach ($assignedPersons as $personId => $assignments) {
                    $targetKey = $target->id . '_' . $detail->id . '_' . $personId;
                    if (isset($processedTargets[$targetKey])) continue;
                    $processedTargets[$targetKey] = true;

                    // Menggunakan trait KPIResolverTrait
                    $result = $this->getCalculationByRoute($target, $personId);

                    if (!$result || !isset($result['progress'])) continue;

                    $rawProgress = $this->normalizeNumber($result['progress']);
                    $percent = max(0, min(100, round($rawProgress, 2)));

                    $monthlyProgress = $result['monthly_progress'] ?? [];

                    foreach ($monthlyProgress as $month => $mp) {
                        if (is_numeric($mp) && $mp >= 0 && $mp <= 100) {
                            $progressData[] = [
                                'target_id' => $target->id,
                                'employee_id' => $personId,
                                'divisi' => $detail->divisi ?? null,
                                'jabatan' => $detail->jabatan ?? null,
                                'period' => $month,
                                'period_type' => 'monthly',
                                'progress' => (float) $percent,
                                'created_at' => $target->created_at
                            ];
                        }
                    }
                }
            }
        }
        return collect($progressData);
    }

    private function normalizeNumber($value)
    {
        if ($value === null || $value === '') return 0;
        return (float) str_replace(',', '', $value);
    }

    private function calculateTrendMetrics($progressData, $granularity)
    {
        if ($progressData->isEmpty()) {
            return [
                'count' => 0, 'avg_progress' => 0, 'median_progress' => 0,
                'total_targets' => 0, 'completed' => 0, 'std_deviation' => 0,
                'trend_direction' => 'stable', 'trend_delta' => 0, 'periods' => []
            ];
        }

        $grouped = $progressData->groupBy(function ($item) use ($granularity) {
            $date = Carbon::parse($item['period']);
            return match ($granularity) {
                'monthly' => $date->format('Y-m'),
                'quarterly' => $date->format('Y') . '-Q' . ceil($date->month / 3),
                'yearly' => $date->format('Y'),
                default => $date->format('Y-m')
            };
        });

        $metrics = [];
        foreach ($grouped as $period => $items) {
            $progressValues = $items->pluck('progress')->filter(fn($v) => $v !== null && is_numeric($v));

            $sorted = $progressValues->sort()->values();
            $count = $sorted->count();
            if ($count === 0) continue;

            $middle = floor(($count - 1) / 2);
            $median = $count % 2 ? round($sorted->get($middle), 2) : round(($sorted->get($middle) + $sorted->get($middle + 1)) / 2, 2);

            $mean = $progressValues->avg();
            $variance = $progressValues->map(fn($v) => pow($v - $mean, 2))->avg();
            $stdDev = sqrt($variance);

            $metrics[$period] = [
                'avg_progress' => round($mean, 2),
                'median_progress' => $median,
                'total_targets' => $items->unique('target_id')->count(),
                'completed' => $progressValues->filter(fn($v) => $v >= 100)->count(),
                'std_deviation' => round($stdDev, 2),
                'min_progress' => $progressValues->min() ?? 0,
                'max_progress' => $progressValues->max() ?? 0,
            ];
        }

        ksort($metrics);
        $periods = array_keys($metrics);
        $metrics['trend_direction'] = 'stable';
        $metrics['trend_delta'] = 0;

        if (count($periods) >= 2) {
            $lastPeriod = end($periods);
            $prevPeriod = $periods[count($periods) - 2];
            $last = $metrics[$lastPeriod]['avg_progress'];
            $prev = $metrics[$prevPeriod]['avg_progress'];

            if ($last > $prev + 1) $metrics['trend_direction'] = 'up';
            elseif ($last < $prev - 1) $metrics['trend_direction'] = 'down';
            $metrics['trend_delta'] = round($last - $prev, 2);
        }

        $metrics['count'] = $progressData->unique('target_id')->count();
        $metrics['periods'] = array_values($periods);
        return $metrics;
    }

    private function calculatePeriodComparison($filters, $allProgressData)
    {
        $currentYear = $filters['tahun'] ?? now()->year;
        $previousYear = $currentYear - 1;

        $currentData = $allProgressData->filter(fn($d) => Carbon::parse($d['period'])->year == $currentYear);
        $previousData = $allProgressData->filter(fn($d) => Carbon::parse($d['period'])->year == $previousYear);

        $currentAvg = $currentData->pluck('progress')->filter()->avg() ?? 0;
        $previousAvg = $previousData->pluck('progress')->filter()->avg() ?? 0;
        $change = $previousAvg > 0 ? round((($currentAvg - $previousAvg) / $previousAvg) * 100, 1) : 0;

        return [
            'current_period' => [
                'year' => $currentYear, 'avg_progress' => round($currentAvg, 1),
                'total_data_points' => $currentData->count(), 'unique_targets' => $currentData->unique('target_id')->count()
            ],
            'previous_period' => [
                'year' => $previousYear, 'avg_progress' => round($previousAvg, 1),
                'total_data_points' => $previousData->count(), 'unique_targets' => $previousData->unique('target_id')->count()
            ],
            'change_percentage' => $change,
            'trend_label' => $change > 2 ? 'improving' : ($change < -2 ? 'declining' : 'stable')
        ];
    }

    private function generateTrendInsights($trendData)
    {
        $insights = [];
        if (isset($trendData['trend_direction'])) {
            if ($trendData['trend_direction'] === 'up' && ($trendData['trend_delta'] ?? 0) > 5) {
                $insights[] = 'Trend kinerja menunjukkan peningkatan signifikan (' . $trendData['trend_delta'] . '%)';
            } elseif ($trendData['trend_direction'] === 'down' && ($trendData['trend_delta'] ?? 0) < -5) {
                $insights[] = 'Perlu perhatian: trend kinerja mengalami penurunan (' . $trendData['trend_delta'] . '%)';
            }
        }
        if (isset($trendData['std_deviation']) && $trendData['std_deviation'] > 20) {
            $insights[] = 'Variasi kinerja antar target cukup tinggi (σ=' . $trendData['std_deviation'] . '), pertimbangkan standarisasi';
        }
        return $insights;
    }

    private function prepareTimeSeries($progressData, $granularity = 'monthly')
    {
        if ($progressData->isEmpty()) return [];
        $grouped = $progressData->groupBy(function ($item) use ($granularity) {
            $period = $item['period'] ?? '';
            if (preg_match('/^\d{4}-\d{2}$/', $period)) return $period;
            try {
                $date = Carbon::parse($period);
                return match ($granularity) {
                    'monthly' => $date->format('Y-m'),
                    'quarterly' => $date->format('Y') . '-Q' . ceil($date->month / 3),
                    default => $date->format('Y-m')
                };
            } catch (\Exception $e) {
                return 'unknown';
            }
        });

        $timeSeries = [];
        foreach ($grouped as $period => $items) {
            if ($period === 'unknown') continue;
            $progressValues = $items->pluck('progress')->filter(fn($v) => $v !== null && is_numeric($v) && $v >= 0 && $v <= 100);
            if ($progressValues->isNotEmpty()) {
                $timeSeries[$period] = round($progressValues->avg(), 1);
            }
        }
        ksort($timeSeries);
        return $timeSeries;
    }

    private function applyLinearRegression($dataPoints)
    {
        $n = count($dataPoints);
        if ($n < 2) return ['next' => null, 'next_3' => [], 'slope' => 0];

        $x = array_keys($dataPoints);
        $y = array_values($dataPoints);
        $xNumeric = range(0, $n - 1);

        $sumX = array_sum($xNumeric);
        $sumY = array_sum($y);
        $sumXY = array_sum(array_map(fn($i) => $xNumeric[$i] * $y[$i], range(0, $n - 1)));
        $sumX2 = array_sum(array_map(fn($i) => $xNumeric[$i] * $xNumeric[$i], range(0, $n - 1)));

        $denominator = ($n * $sumX2 - $sumX * $sumX);
        if ($denominator == 0) {
            $b = 0; $a = $sumY / $n;
        } else {
            $b = ($n * $sumXY - $sumX * $sumY) / $denominator;
            $a = ($sumY - $b * $sumX) / $n;
        }

        $nextIndex = $n;
        $nextValue = max(0, min(100, $a + $b * $nextIndex));

        $next3 = [];
        for ($i = 1; $i <= 3; $i++) {
            $val = $a + $b * ($nextIndex + $i);
            $next3[] = round(max(0, min(100, $val)), 1);
        }

        return ['next' => round($nextValue, 1), 'next_3' => $next3, 'slope' => round($b, 3), 'intercept' => round($a, 3)];
    }

    private function calculatePredictionConfidence($historicalData, $predictions)
    {
        if (count($historicalData) < 3) return 0.3;
        $values = array_values($historicalData);
        $nonZeroValues = array_filter($values, fn($v) => $v > 0);
        if (empty($nonZeroValues)) return 0.3;

        $mean = array_sum($nonZeroValues) / count($nonZeroValues);
        $variance = array_sum(array_map(fn($v) => pow($v - $mean, 2), $nonZeroValues)) / count($nonZeroValues);
        $stdDev = sqrt($variance);
        $cv = $mean > 0 ? $stdDev / $mean : 1;
        $confidence = max(0, min(1, 1 - $cv));

        $dataPoints = count($nonZeroValues);
        $dataFactor = min(1, $dataPoints / 12);
        return max(0.3, min(0.95, $confidence * 0.6 + $dataFactor * 0.4));
    }

    private function generateRecommendations($predictions, $confidence)
    {
        $recommendations = [];
        if ($predictions['slope'] > 0.5) $recommendations[] = 'Pertahankan strategi saat ini, trend menunjukkan peningkatan konsisten';
        elseif ($predictions['slope'] < -0.5) $recommendations[] = 'Evaluasi pendekatan saat ini, pertimbangkan intervensi strategis untuk membalikkan trend';
        if ($confidence < 0.6) $recommendations[] = 'Data historis belum cukup konsisten untuk prediksi akurat';
        return $recommendations;
    }
}
