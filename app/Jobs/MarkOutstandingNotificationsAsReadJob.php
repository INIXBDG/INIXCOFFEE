<?php

namespace App\Jobs;

use App\Models\Outstanding;
use App\Models\RKM;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class MarkOutstandingNotificationsAsReadJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, IsMonitored;

    public function __construct()
    {
        // Optional: bisa isi parameter jika butuh
    }

    public function handle(): void
    {
        Log::info('Menjalankan Job: MarkOutstandingNotificationsAsReadJob...');

        try {
            $outstandings = Outstanding::where('status_pembayaran', '1')->get();

            foreach ($outstandings as $outstanding) {
                $rkm = RKM::where('id', $outstanding->id_rkm)
                    ->with('perusahaan', 'materi')
                    ->first();

                if ($rkm && $rkm->perusahaan && $rkm->materi) {
                    DB::table('notifications')
                        ->where('type', 'App\Notifications\OutstandingNotification')
                        ->whereJsonContains('data->message->nama_perusahaan', $rkm->perusahaan->nama_perusahaan)
                        ->whereJsonContains('data->message->nama_materi', $rkm->materi->nama_materi)
                        ->whereJsonContains('data->message->due_date', $outstanding->due_date)
                        ->update(['read_at' => Carbon::now()]);
                }
            }

            Log::info('Job MarkOutstandingNotificationsAsReadJob selesai.');
        } catch (\Exception $e) {
            Log::error('Job MarkOutstandingNotificationsAsReadJob GAGAL: ' . $e->getMessage());
            throw $e;
        }
    }
}
