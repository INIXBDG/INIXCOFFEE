<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Carbon\Carbon;

class OperationalBudgetCalculator
{
    const OUTSIDE_OPS_ID = '999999999';
    const DEFAULT_BUDGET = 1000000;

    public static function isOperasionalKantor($item): bool
    {
        if (!empty($item->id_pengajuan_spj)) {
            return $item->tipe === 'Operasional Kantor';
        }
        if ((string) $item->id_pickup_driver === self::OUTSIDE_OPS_ID) {
            return in_array($item->tipe, ['Operasional Kantor', 'Budget Lebih']);
        }
        if ($item->pickupDriver) {
            return $item->pickupDriver->tipe_perjalanan === 'Operasional Kantor';
        }
        return false;
    }

    private static function sourceOf($item): string
    {
        if (!empty($item->id_pengajuan_spj)) return 'spj';
        if ((string) $item->id_pickup_driver === self::OUTSIDE_OPS_ID) return 'outside';
        return 'driver';
    }

    public static function filterBySource(Collection $items, array $sources): Collection
    {
        return $items->filter(fn($i) => in_array(self::sourceOf($i), $sources));
    }

    public static function calculate(Collection $items, array $sources = ['driver', 'spj', 'outside']): array
    {
        $filtered = self::filterBySource($items, $sources)
            ->filter(fn($i) => self::isOperasionalKantor($i))
            ->sortBy('created_at');

        $grouped = $filtered->groupBy(fn($i) => Carbon::parse($i->created_at)->startOfWeek()->format('Y-m-d'));

        $result = [];
        foreach ($grouped as $weekKey => $weekItems) {
            $running = self::DEFAULT_BUDGET;
            foreach ($weekItems as $item) {
                $running += $item->tipe === 'Budget Lebih' ? $item->harga : -$item->harga;
                $result[$item->id] = ['minggu' => $weekKey, 'sisa_budget' => $running];
            }
        }
        return $result;
    }

    public static function weeklySummary(Collection $allItems, string $weekStart, array $sources = ['driver', 'spj', 'outside']): array
    {
        $start = Carbon::parse($weekStart)->startOfWeek();
        $end = $start->copy()->endOfWeek();

        $weekItems = self::filterBySource($allItems, $sources)
            ->filter(fn($i) => self::isOperasionalKantor($i))
            ->filter(fn($i) => Carbon::parse($i->created_at)->between($start, $end))
            ->sortBy('created_at');

        $running = self::DEFAULT_BUDGET;
        $rows = [];
        $totalTambahan = 0;
        $totalTerpakai = 0;

        foreach ($weekItems as $item) {
            if ($item->tipe === 'Budget Lebih') {
                $running += $item->harga;
                $totalTambahan += $item->harga;
            } else {
                $running -= $item->harga;
                $totalTerpakai += $item->harga;
            }

            $rows[] = [
                'id' => $item->id,
                'tanggal' => Carbon::parse($item->created_at)->format('d M Y H:i'),
                'sumber' => match (self::sourceOf($item)) {
                    'spj' => 'SPJ',
                    'outside' => 'Diluar Koordinasi',
                    default => 'Koordinasi Driver',
                },
                'tipe' => $item->tipe,
                'harga' => $item->harga,
                'keterangan' => $item->keterangan,
                'sisa_setelah' => $running,
            ];
        }

        return [
            'week_key' => $start->format('Y-m-d'),
            'week_start' => $start->format('d M Y'),
            'week_end' => $end->format('d M Y'),
            'budget_awal' => self::DEFAULT_BUDGET,
            'total_tambahan' => $totalTambahan,
            'total_terpakai' => $totalTerpakai,
            'sisa_budget' => $running,
            'items' => $rows,
        ];
    }
}