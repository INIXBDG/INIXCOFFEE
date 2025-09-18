<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Notifications\OutstandingNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\Outstanding;
use App\Models\User;
use App\Models\RKM;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->call(function () {
            $outstandings = Outstanding::where('status_pembayaran', '0')
                ->whereDate('due_date', '>=', now())
                ->get();
    
            $financeUsers = User::where('jabatan', 'Finance & Accounting')->get();
            $path = '/outstanding';
    
            try {
                foreach ($outstandings as $outstanding) {
                    Notification::send($financeUsers, new OutstandingNotification($outstanding, $path));
                }
            } catch (\Exception $e) {
                    Log::error('Failed to send notifications: ' . $e->getMessage());
            }
            
        })->weeklyOn(1, '8:00');

        $schedule->call(function () {
            $outstandings = Outstanding::where('status_pembayaran', '1')->get();
        
            foreach ($outstandings as $outstanding) {
                $rkm = RKM::where('id', $outstanding->id_rkm)->with('perusahaan', 'materi')->first();
        
                if ($rkm && $rkm->perusahaan && $rkm->materi) {
                    // Tandai notifikasi terkait sebagai dibaca (set read_at)
                    DB::table('notifications')
                        ->where('type', 'App\Notifications\OutstandingNotification')
                        ->whereJsonContains('data->message->nama_perusahaan', $rkm->perusahaan->nama_perusahaan) // Sesuaikan dengan struktur data Anda
                        ->whereJsonContains('data->message->nama_materi', $rkm->materi->nama_materi) // Sesuaikan dengan struktur data Anda
                        ->whereJsonContains('data->message->due_date', $outstanding->due_date) // Sesuaikan dengan struktur data Anda
                        ->update(['read_at' => Carbon::now()]);
                }
            }
        })->dailyAt('23:00');
        
        
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
