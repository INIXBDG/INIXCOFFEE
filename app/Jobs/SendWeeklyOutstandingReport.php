<?php

namespace App\Jobs;

// 1. Tambahkan use statement untuk class yang dibutuhkan
use App\Models\Outstanding;
use App\Models\User;
use App\Notifications\OutstandingNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use romanzipp\QueueMonitor\Traits\IsMonitored; // <-- Pastikan ini ada

class SendWeeklyOutstandingReport implements ShouldQueue
{
    // Pastikan use statement ini juga ada
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;

    public function __construct()
    {
        // Konstruktor bisa kosong untuk job ini
    }

    public function handle(): void
    {
        Log::info('Menjalankan Job: SendWeeklyOutstandingNotificationsJob...'); // Ubah nama log agar sesuai

        $outstandings = Outstanding::where('status_pembayaran', '0')
            ->whereDate('due_date', '>=', now())
            ->get();

        $financeUsers = User::where('jabatan', 'Finance & Accounting')->get();
        $path = '/outstanding';

        try {
            if ($financeUsers->isNotEmpty() && $outstandings->isNotEmpty()) {
                Notification::send($financeUsers, new OutstandingNotification($outstandings, $path));
                 Log::info('Job SendWeeklyOutstandingNotificationsJob: Mengirim notifikasi ke ' . $financeUsers->count() . ' user untuk ' . $outstandings->count() . ' outstanding.');
            } else {
                 Log::info('Job SendWeeklyOutstandingNotificationsJob: Tidak ada user finance atau outstanding ditemukan.');
            }
             Log::info('Job SendWeeklyOutstandingNotificationsJob selesai.');
        } catch (\Exception $e) {
             Log::error('Job SendWeeklyOutstandingNotificationsJob GAGAL: ' . $e->getMessage());
             throw $e; // Lempar error agar ditandai Failed di monitor
        }
    }
}