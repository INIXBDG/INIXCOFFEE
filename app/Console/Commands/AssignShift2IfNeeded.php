<?php

namespace App\Console\Commands;

use App\Models\KategoriDaftarTugas;
use App\Models\KontrolTugas;
use Illuminate\Console\Command;

class AssignShift2IfNeeded extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:shift2';

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
        $today = now()->toDateString();

        // cek apakah shift 2 sudah ada
        $shift2Exists = KontrolTugas::whereDate('Deadline_Date', $today)
            ->whereHas('kategoriDaftarTugas', function ($q) {
                $q->where('Tipe', 'Harian')->where('tipe_turunan', 'Shift 2');
            })
            ->exists();

        if ($shift2Exists) {
            return;
        }

        // cari OB yang punya shift 1
        $obShift1 = KontrolTugas::whereDate('Deadline_Date', $today)
            ->whereHas('kategoriDaftarTugas', function ($q) {
                $q->where('Tipe', 'Harian')->where('tipe_turunan', 'Shift 1');
            })
            ->first();

        if (!$obShift1) {
            return;
        }

        $karyawanId = $obShift1->id_karyawan;

        // ambil kategori shift 2
        $kategoriShift2 = KategoriDaftarTugas::where('Tipe', 'Harian')->where('tipe_turunan', 'Shift 2')->get();

        foreach ($kategoriShift2 as $kat) {
            $exists = KontrolTugas::where('id_karyawan', $karyawanId)->where('id_DaftarTugas', $kat->id)->whereDate('Deadline_Date', $today)->exists();

            if (!$exists) {
                KontrolTugas::create([
                    'id_karyawan' => $karyawanId,
                    'id_DaftarTugas' => $kat->id,
                    'status' => 0,
                    'Deadline_Date' => $today,
                ]);
            }
        }
    }
}
