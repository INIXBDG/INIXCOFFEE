<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateCutiKontrak extends Command
{
    protected $signature = 'app:update-cuti-kontrak';

    protected $description = 'Isi jatah cuti setelah karyawan menjalani kontrak selama 6 bulan';

    public function handle(): int
    {
        $executedAt = Carbon::now();
        // Include one extra month to catch anniversaries reached after the prior monthly run.
        $earliestContractStart = $executedAt->copy()->startOfYear()->subMonthsNoOverflow(7);
        $startDate = $earliestContractStart->toDateString();
        $cutoffDate = $executedAt->copy()->subMonthsNoOverflow(6)->toDateString();

        Log::info('app:update-cuti-kontrak started', [
            'executed_at' => $executedAt->toIso8601String(),
            'contract_start_from' => $startDate,
            'contract_start_until' => $cutoffDate,
        ]);

        try {
            $employees = DB::table('karyawans')
                ->select('id', 'awal_kontrak', 'cuti')
                ->whereDate('awal_kontrak', '>=', $startDate)
                ->whereDate('awal_kontrak', '<=', $cutoffDate)
                ->get();

            $updatedRows = 0;
            foreach ($employees as $employee) {
                if (! $this->hasMissingCuti($employee->cuti)) {
                    continue;
                }

                $contractStart = Carbon::parse($employee->awal_kontrak);
                if (
                    $contractStart->lt($earliestContractStart)
                    || $contractStart->copy()->addMonthsNoOverflow(6)->gt($executedAt)
                ) {
                    continue;
                }

                $query = DB::table('karyawans')->where('id', $employee->id);
                if ($employee->cuti === null) {
                    $query->whereNull('cuti');
                } else {
                    $query->where('cuti', $employee->cuti);
                }

                $updatedRows += $query->update([
                    'cuti' => 12,
                    'updated_at' => $executedAt,
                ]);
            }
        } catch (\Throwable $exception) {
            Log::error('app:update-cuti-kontrak failed', [
                'executed_at' => $executedAt->toIso8601String(),
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        Log::info('app:update-cuti-kontrak completed', [
            'executed_at' => $executedAt->toIso8601String(),
            'updated_rows' => $updatedRows,
        ]);

        $this->info("Pemeriksaan cuti kontrak selesai. Total karyawan diperbarui: {$updatedRows}.");

        return self::SUCCESS;
    }

    private function hasMissingCuti(mixed $cuti): bool
    {
        return $cuti === null
            || (is_string($cuti) && (trim($cuti) === '' || trim($cuti) === '-'));
    }
}
