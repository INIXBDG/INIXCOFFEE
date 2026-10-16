<?php

namespace App\Console\Commands;

use App\Models\Legalitas;
use App\Models\User;
use App\Notifications\LegalitasNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class LegalitasReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:legalitas-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim pengingat untuk dokumen legalitas yang akan berakhir dalam 1 bulan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $penerima = $this->penerima();

        if ($penerima->isEmpty()) {
            $this->warn('Tidak ada penerima notifikasi, command dihentikan.');

            return self::FAILURE;
        }

        $today = Carbon::today();
        $total = 0;

        Legalitas::where('status', 'aktif')
            ->chunkById(100, function ($items) use ($today, $penerima, &$total) {
                foreach ($items as $legalitas) {
                    $berakhir = $this->tanggalBerakhir($legalitas);

                    // Kirim notif jika sekarang sudah masuk 1 bulan sebelum tanggal berakhir
                    // dan dokumen belum lewat masa berlakunya.
                    if ($berakhir->gte($today) && $berakhir->copy()->subMonth()->lte($today)) {
                        $sisaHari = (int) $today->diffInDays($berakhir);

                        $this->kirimNotifikasi($legalitas, $berakhir, $sisaHari, $penerima);
                        $total++;
                    }
                }
            });

        $this->info("Reminder legalitas selesai: {$total} dokumen, {$penerima->count()} penerima.");

        return self::SUCCESS;
    }

    /**
     * Tanggal berakhir: pakai kolom tanggal_berakhir,
     * jika kosong hitung dari tanggal_terbit + masa_berlaku (sesuai satuan).
     */
    private function tanggalBerakhir(Legalitas $legalitas): Carbon
    {
        if ($legalitas->tanggal_berakhir) {
            return Carbon::parse($legalitas->tanggal_berakhir)->startOfDay();
        }

        $terbit = Carbon::parse($legalitas->tanggal_terbit)->startOfDay();
        $masa   = (int) $legalitas->masa_berlaku;

        return match ($legalitas->satuan_masa_berlaku) {
            'hari'  => $terbit->addDays($masa),
            'bulan' => $terbit->addMonthsNoOverflow($masa),
            'tahun' => $terbit->addYearsNoOverflow($masa),
            default => $terbit,
        };
    }

    /**
     * Penerima: user dengan akun aktif dan jabatan HRD.
     * Model User harus memakai trait Notifiable.
     */
    private function penerima(): Collection
    {
        return User::where('status_akun', '1')
            ->where('jabatan', 'HRD')
            ->get();
    }

    private function kirimNotifikasi(Legalitas $legalitas, Carbon $berakhir, int $sisaHari, Collection $penerima): void
    {
        $data = [
            'legalitas'        => $legalitas->nama_dokumen,
            'tanggal_berkahir' => $berakhir->format('d M Y'), // key mengikuti LegalitasNotification
            'sisa_hari'        => $sisaHari,
        ];

        foreach ($penerima as $user) {
            $user->notify(new LegalitasNotification($data, null, $user->id));
        }
    }
}