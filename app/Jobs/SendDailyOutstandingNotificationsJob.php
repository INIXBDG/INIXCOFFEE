<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\OutstandingNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class SendDailyOutstandingNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;

    public function __construct()
    {
        // Kosongkan jika tidak ada parameter yang dikirim
    }

    public function handle(): void
    {
        Log::info('Menjalankan Job: SendDailyOutstandingNotificationsJob...');

        try {
            $outstandings = DB::table('outstandings')
                ->join('r_k_m_s', 'r_k_m_s.id', '=', 'outstandings.id_rkm')
                ->join('perusahaans', 'r_k_m_s.perusahaan_key', '=', 'perusahaans.id')
                ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
                ->join('users', 'users.jabatan', '=', DB::raw("'Finance & Accounting'"))
                ->where('outstandings.status_pembayaran', '0')
                ->whereDate('outstandings.due_date', now()->addDay()->toDateString()) // H-1
                ->select(
                    'users.id as user_id',
                    'users.username',
                    'perusahaans.nama_perusahaan',
                    'materis.nama_materi',
                    'outstandings.net_sales',
                    'outstandings.due_date',
                    'outstandings.status_pembayaran'
                )
                ->get();

            $notificationsSent = 0;

            foreach ($outstandings as $item) {
                try {
                    $userData = [
                        'user' => $item->username,
                        'nama_perusahaan' => $item->nama_perusahaan,
                        'nama_materi' => $item->nama_materi,
                        'net_sales' => $item->net_sales,
                        'due_date' => $item->due_date,
                        'status_pembayaran' => $item->status_pembayaran,
                    ];

                    $user = User::find($item->user_id);
                    if ($user) {
                        $user->notify(new OutstandingNotification($userData, '/outstanding'));
                        $notificationsSent++;
                    }
                } catch (\Exception $e) {
                    Log::error("Gagal kirim notifikasi ke user ID {$item->user_id}: " . $e->getMessage());
                    continue;
                }
            }

            Log::info("Job SendDailyOutstandingNotificationsJob selesai. Total notifikasi terkirim: {$notificationsSent}");
        } catch (\Exception $e) {
            Log::error("Job SendDailyOutstandingNotificationsJob GAGAL: " . $e->getMessage());
            throw $e;
        }
    }
}
