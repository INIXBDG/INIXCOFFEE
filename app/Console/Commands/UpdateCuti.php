<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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
    protected $description = 'Update cuti menjadi 12 hari setiap bulan Februari';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (Carbon::now()->month !== 2) {
            $this->info('Bukan bulan Februari, update cuti dibatalkan.');
            return;
        }

        DB::table('karyawans')->update([
            'cuti' => 12,
            'updated_at' => now()
        ]);

        $this->info('Jatah cuit berhasil di update');
    }
}
