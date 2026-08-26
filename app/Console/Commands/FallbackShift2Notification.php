<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\KontrolTugas;
use App\Models\Karyawan;
use App\Notifications\ShiftConfirmationNotification;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;

class FallbackShift2Notification extends Command
{
    protected $signature = 'tasks:fallback-shift2';
    protected $description = 'Cek Shift 2, jika kosong tawarkan ke pemegang Shift 1';

    public function handle()
    {
        $now = Carbon::now('Asia/Jakarta');
        $today = $now->toDateString();

        if ($now->hour < 16) return;

        $shift2Exists = KontrolTugas::whereDate('Deadline_Date', $today)
            ->whereHas('KategoriDaftarTugas', fn($q) => $q->where('tipe_turunan', 'Shift 2'))
            ->exists();

        if ($shift2Exists) return;

        $shift1Task = KontrolTugas::whereDate('Deadline_Date', $today)
            ->whereHas('KategoriDaftarTugas', fn($q) => $q->where('tipe_turunan', 'Shift 1'))
            ->first();

        if (!$shift1Task) return;

        $karyawanShift1 = Karyawan::find($shift1Task->id_karyawan);

        if ($karyawanShift1) {
            Notification::send($karyawanShift1, new ShiftConfirmationNotification(2, $today));
            $this->info("Notifikasi fallback Shift 2 dikirim ke {$karyawanShift1->nama_lengkap}");
        }
    }
}