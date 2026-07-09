<?php

namespace App\Traits;

use Carbon\Carbon;

trait KPIFormatTrait
{
    public function formatTenggatWaktuExport(string $jangka, string $detail): string
    {
        $namaBulanId = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agt', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        switch (strtolower($jangka)) {
            case 'tahunan':
                $year = (int) $detail;
                return "31 Des {$year}";

            case 'bulanan':
                $parts = explode('-', trim($detail));
                if (count($parts) === 2) {
                    [$year, $month] = $parts;
                    $lastDay  = date('t', mktime(0, 0, 0, (int)$month, 1, (int)$year));
                    return "{$lastDay} " . ($namaBulanId[(int)$month] ?? $month) . " {$year}";
                }
                return $detail;

            case 'kuartalan':
            case 'quartal':
            case 'quarter':
                if (preg_match('/(\d{4})\D?Q?(\d)/i', $detail, $m)) {
                    $year     = $m[1];
                    $quarter  = (int)$m[2];
                    $monthEnd = $quarter * 3;
                    $monthStart = $monthEnd - 2;
                    $lastDay  = date('t', mktime(0, 0, 0, $monthEnd, 1, (int)$year));
                    $bulanStart = $namaBulanId[$monthStart] ?? $monthStart;
                    $bulanEnd   = $namaBulanId[$monthEnd] ?? $monthEnd;
                    return "Q{$quarter} {$year} ({$bulanStart}-{$bulanEnd}) - {$lastDay} {$bulanEnd} {$year}";
                }
                return $detail;

            case 'mingguan':
                if (preg_match('/(\d{4})\D?W?(\d{1,2})/i', $detail, $m)) {
                    return "Minggu ke-{$m[2]}, {$m[1]}";
                }
                return $detail;
        }
        return $detail;
    }

    public function hitungStatusExport(float $progressPersen, float $nilaiTarget, string $tipe, float $progressRaw, string $tenggatWaktu): string
    {
        $isTargetReached = false;
        if ($tipe === 'rupiah' || $tipe === 'angka') {
            $isTargetReached = $progressRaw >= $nilaiTarget;
        } else {
            $isTargetReached = $progressPersen >= $nilaiTarget;
        }

        try {
            $deadline   = Carbon::parse($tenggatWaktu)->startOfDay();
            $now        = now()->startOfDay();
            $isOverdue  = $now->gt($deadline);
            $isSameYear = $now->year === $deadline->year;
        } catch (\Exception $e) {
            return 'Dalam Progress';
        }

        if (!$isOverdue && $isSameYear) {
            return $progressPersen <= 0 ? 'Belum Dimulai' : 'Dalam Progress';
        }

        if ($isOverdue) {
            return $isTargetReached ? 'Selesai' : 'Gagal';
        }

        return 'Dalam Progress';
    }

    public function getGradeLabel(float $nilai): string
    {
        if ($nilai >= 100) return 'Sangat Baik';
        if ($nilai >= 80) return 'Baik';
        if ($nilai >= 70) return 'Cukup';
        if ($nilai >= 60) return 'Kurang';
        return 'Sangat Kurang';
    }

    public function groupDataByQuarter(array $monthlyData, int $tahun): array
    {
        $quarters = [
            1 => ['months' => [1, 2, 3], 'label' => 'Q1', 'total' => 0, 'count' => 0],
            2 => ['months' => [4, 5, 6], 'label' => 'Q2', 'total' => 0, 'count' => 0],
            3 => ['months' => [7, 8, 9], 'label' => 'Q3', 'total' => 0, 'count' => 0],
            4 => ['months' => [10, 11, 12], 'label' => 'Q4', 'total' => 0, 'count' => 0],
        ];

        foreach ($monthlyData as $key => $value) {
            if (preg_match('/^\d{4}-(\d{2})$/', (string)$key, $m)) {
                $month = (int)$m[1];
            } elseif (is_numeric($key) && $key >= 1 && $key <= 12) {
                $month = (int)$key;
            } else {
                continue;
            }

            foreach ($quarters as $q => &$data) {
                if (in_array($month, $data['months'])) {
                    $data['total'] += (float)$value;
                    $data['count']++;
                    break;
                }
            }
        }

        $result = [];
        foreach ($quarters as $q => $data) {
            $quarterAvg = $data['count'] > 0 ? round($data['total'] / $data['count'], 2) : 0;
            $result[$q] = [
                'label'      => $data['label'],
                'periode'    => "{$tahun}-Q{$q}",
                'rata_rata'  => $quarterAvg,
                'total'      => $quarterAvg,
                'bulan_aktif' => $data['count'],
            ];
        }

        return $result;
    }

    public function formatNilaiTargetExport($nilaiTarget, string $tipe): string
    {
        if ($tipe === 'rupiah') {
            return 'Rp ' . number_format((float) $nilaiTarget, 0, ',', '.');
        }
        if ($tipe === 'persen' || $tipe === 'angka') {
            return number_format((float) $nilaiTarget, 0, ',', '.') . '%';
        }
        return number_format((float) $nilaiTarget, 0, ',', '.');
    }
}
