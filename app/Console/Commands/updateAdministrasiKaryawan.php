<?php

namespace App\Console\Commands;

use App\Models\AdministrasiKaryawan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class updateAdministrasiKaryawan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-administrasi-karyawan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $administrasi = AdministrasiKaryawan::all();

        foreach ($administrasi as $a) {
            $dateline = Carbon::parse($a->dateline);
            $tanggalSelesai = Carbon::parse($a->tanggal_selesai); 

            if ($dateline->day < $now->day && $tanggalSelesai !== null && !in_array($a->status, ['selesai', 'pending'])) {
                $a->status = 'terlambat';
                $a->save();

                $this->info("Administrasi karyawan berhasil diupdate!");
            }
        }
    }
}
