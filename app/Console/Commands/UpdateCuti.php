<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateCuti extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-cuti';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update cuti tahunan pada Februari setelah masa kerja minimal 6 bulan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $executedAt = Carbon::now();
        Log::info('app:update-cuti started', [
            'executed_at' => $executedAt->toIso8601String(),
        ]);

        if ($executedAt->month !== 2) {
            Log::info('app:update-cuti skipped', [
                'reason' => 'outside_february',
                'month' => $executedAt->month,
            ]);
            $this->info('Bukan bulan Februari, update cuti dibatalkan.');

            return self::SUCCESS;
        }

        try {
            $cutoffDate = $executedAt->copy()->subMonthsNoOverflow(6)->toDateString();
            $updatedRows = DB::table('karyawans')
                ->where(function ($query) use ($cutoffDate) {
                    $query->whereNull('awal_kontrak')
                        ->orWhereDate('awal_kontrak', '<=', $cutoffDate);
                })
                ->update([
                    'cuti' => 12,
                    'updated_at' => $executedAt,
                ]);
        } catch (\Throwable $exception) {
            Log::error('app:update-cuti failed', [
                'executed_at' => $executedAt->toIso8601String(),
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        Log::info('app:update-cuti completed', [
            'executed_at' => $executedAt->toIso8601String(),
            'updated_rows' => $updatedRows,
        ]);

        $this->info("Jatah cuti berhasil di update. Total karyawan diperbarui: {$updatedRows}.");

        return self::SUCCESS;
    }
}
