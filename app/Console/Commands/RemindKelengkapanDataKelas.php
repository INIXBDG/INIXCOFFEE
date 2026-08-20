<?php

namespace App\Console\Commands;

use App\Models\KelasSetting;
use App\Models\Karyawan;
use App\Models\User;
use App\Notifications\KelengkapanDataKelasNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RemindKelengkapanDataKelas extends Command
{
    protected $signature = 'kelas:remind-kelengkapan
        {--min-days=5 : Batas minimal H- (paling dekat) sebelum kelas mulai}
        {--max-days=7 : Batas maksimal H- (paling jauh / 1 minggu) sebelum kelas mulai}';

    protected $description = 'Kirim notifikasi pengingat ke instruktur untuk melengkapi data Kelas Setting yang masih kosong, untuk kelas yang mulai 5-7 hari lagi';

    public function handle(): int
    {
        $minDays = (int) $this->option('min-days'); // 5 hari (paling dekat)
        $maxDays = (int) $this->option('max-days'); // 7 hari / 1 minggu (paling jauh)

        // Batas jendela tanggal: dari H+minDays sampai H+maxDays
        $batasAwal = Carbon::now()->addDays($minDays)->format('Y-m-d');
        $batasAkhir = Carbon::now()->addDays($maxDays)->format('Y-m-d');

        $rows = KelasSetting::whereNotNull('dari')
            ->whereBetween('dari', [$batasAwal, $batasAkhir])
            ->where(function ($q) {
                $q->whereNull('software')->orWhere('software', '');
            })
            ->whereNotNull('instruktur')
            ->where('instruktur', '!=', '')
            ->get();

        if ($rows->isEmpty()) {
            $this->info("Tidak ada kelas dengan data belum lengkap dalam rentang H-{$maxDays} s/d H-{$minDays}.");
            return self::SUCCESS;
        }

        $grouped = $rows->groupBy('instruktur');
        $totalDikirim = 0;
        $hariIni = Carbon::now()->format('Y-m-d');

        foreach ($grouped as $kodeInstruktur => $kelasList) {
            $lockKey = 'reminder_kelengkapan_' . $kodeInstruktur . '_' . $hariIni;

            if (Cache::has($lockKey)) {
                continue;
            }

            $karyawan = Karyawan::where('kode_karyawan', $kodeInstruktur)->first();
            if (!$karyawan) {
                Log::warning("Reminder kelengkapan data: karyawan dengan kode {$kodeInstruktur} tidak ditemukan.");
                continue;
            }

            $user = User::whereHas('karyawan', function ($q) use ($kodeInstruktur) {
                $q->where('kode_karyawan', $kodeInstruktur);
            })->first();

            if (!$user) {
                Log::warning("Reminder kelengkapan data: user untuk karyawan {$kodeInstruktur} tidak ditemukan.");
                continue;
            }

            $namaKelasList = $kelasList->map(function ($row) {
                $missing = [];
                if (empty($row->software)) $missing[] = 'Software';

                return [
                    'kelas' => $row->kelas,
                    'dari' => $row->dari,
                    'missing' => $missing,
                ];
            })->values()->toArray();

            $tanggalTerdekat = $kelasList->min('dari');

            $data = [
                'kelas_list' => $namaKelasList,
                'jumlah_kelas' => count($namaKelasList),
                'tanggal_terdekat' => $tanggalTerdekat,
            ];

            try {
                $user->notify(new KelengkapanDataKelasNotification(
                    $data,
                    '/kelas-setting/index',
                    'Pengingat Kelengkapan Data Kelas',
                    $user->id
                ));

                Cache::put($lockKey, true, now()->endOfDay());
                $totalDikirim++;
            } catch (\Exception $e) {
                Log::error("Gagal kirim reminder kelengkapan data ke {$kodeInstruktur}: " . $e->getMessage());
            }
        }

        $this->info("Reminder terkirim ke {$totalDikirim} instruktur (jendela H-{$maxDays} s/d H-{$minDays}).");
        Log::info("Reminder kelengkapan data kelas: {$totalDikirim} notifikasi terkirim (H-{$maxDays} s/d H-{$minDays}).");

        return self::SUCCESS;
    }
}