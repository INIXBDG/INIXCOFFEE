<?php

namespace App\Console;

use App\Models\activityLog;

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
use App\Jobs\SendWeeklyOutstandingReport;
use App\Jobs\MarkOutstandingNotificationsAsReadJob;
use App\Jobs\CleanActivityLogJob;
use App\Jobs\GenerateAbsensiReportJob;
use App\Jobs\SendDailyOutstandingNotificationsJob;
use App\Jobs\UpdateStatusJob;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->call(function () {
        //     $outstandings = Outstanding::where('status_pembayaran', '0')
        //         ->whereDate('due_date', '>=', now())
        //         ->get();

        //     $financeUsers = User::where('jabatan', 'Finance & Accounting')->get();
        //     $path = '/outstanding';

        //     try {
        //         foreach ($outstandings as $outstanding) {
        //             Notification::send($financeUsers, new OutstandingNotification($outstanding, $path));
        //         }
        //     } catch (\Exception $e) {
        //             Log::error('Failed to send notifications: ' . $e->getMessage());
        //     }

        // })->weeklyOn(1, '8:00');
        $schedule->job(new SendWeeklyOutstandingReport)
         ->weeklyOn(1, '15:30');


        // $schedule->call(function () {
        //     $outstandings = Outstanding::where('status_pembayaran', '1')->get();

        //     foreach ($outstandings as $outstanding) {
        //         $rkm = RKM::where('id', $outstanding->id_rkm)->with('perusahaan', 'materi')->first();

        //         if ($rkm && $rkm->perusahaan && $rkm->materi) {
        //             // Tandai notifikasi terkait sebagai dibaca (set read_at)
        //             DB::table('notifications')
        //                 ->where('type', 'App\Notifications\OutstandingNotification')
        //                 ->whereJsonContains('data->message->nama_perusahaan', $rkm->perusahaan->nama_perusahaan) // Sesuaikan dengan struktur data Anda
        //                 ->whereJsonContains('data->message->nama_materi', $rkm->materi->nama_materi) // Sesuaikan dengan struktur data Anda
        //                 ->whereJsonContains('data->message->due_date', $outstanding->due_date) // Sesuaikan dengan struktur data Anda
        //                 ->update(['read_at' => Carbon::now()]);
        //         }
        //     }
        // })->dailyAt('23:00');
        $schedule->job(new MarkOutstandingNotificationsAsReadJob)
         ->dailyAt('23:00');
        // ->everyMinute();

        // $schedule->call(function () {
        //     try {
        //         activityLog::whereNotIn('status', [
        //             'login',
        //             'logout',
        //             'Absen Masuk',
        //             'Absen Keluar',
        //         ])->delete();

        //         Log::info("Data activityLog dengan status 'visit' berhasil dihapus oleh scheduler.");
        //     } catch (\Throwable $e) {
        //         Log::error("Schedule gagal: " . $e->getMessage());
        //     }
        // })->weeklyOn(2, '08:00');
        $schedule->job(new CleanActivityLogJob)
         ->weeklyOn(2, '08:00');
        // ->everyMinute();

        // $schedule->call(function () {
        //     try {
        //         DB::table('notifications')->insertUsing(
        //             [
        //                 'id',
        //                 'type',
        //                 'notifiable_type',
        //                 'notifiable_id',
        //                 'data',
        //                 'created_at',
        //                 'updated_at'
        //             ],
        //             DB::table('outstandings')
        //                 ->join('r_k_m_s', 'r_k_m_s.id', '=', 'outstandings.id_rkm')
        //                 ->join('perusahaans', 'r_k_m_s.perusahaan_key', '=', 'perusahaans.id')
        //                 ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
        //                 ->join('users', function ($join) {
        //                     $join->on(DB::raw("'Finance & Accounting'"), '=', 'users.jabatan');
        //                 })
        //                 ->where('outstandings.status_pembayaran', '0')
        //                 ->whereDate('outstandings.due_date', now()->addDay()->toDateString()) // hanya H-1
        //                 ->selectRaw('
        //             UUID() as id,
        //             "App\\\Notifications\\\OutstandingNotification" as type,
        //             "App\\\Models\\\User" as notifiable_type,
        //             users.id as notifiable_id,
        //             JSON_OBJECT(
        //                 "user", users.username,
        //                 "message", JSON_OBJECT(
        //                     "nama_perusahaan", perusahaans.nama_perusahaan,
        //                     "nama_materi", materis.nama_materi,
        //                     "net_sales", outstandings.net_sales,
        //                     "due_date", outstandings.due_date,
        //                     "status_pembayaran", outstandings.status_pembayaran,
        //                     "tipe", "Outstanding"
        //                 ),
        //                 "path", "/outstanding",
        //                 "status", "unread"
        //             ) as data,
        //             NOW() as created_at,
        //             NOW() as updated_at
        //         ')
        //         );

        //         $outstandings = DB::table('outstandings')
        //             ->join('r_k_m_s', 'r_k_m_s.id', '=', 'outstandings.id_rkm')
        //             ->join('perusahaans', 'r_k_m_s.perusahaan_key', '=', 'perusahaans.id')
        //             ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
        //             ->join('users', function ($join) {
        //                 $join->on(DB::raw("'Finance & Accounting'"), '=', 'users.jabatan');
        //             })
        //             ->where('outstandings.status_pembayaran', '0')
        //             ->whereDate('outstandings.due_date', now()->addDay()->toDateString())
        //             ->select(
        //                 'users.id as user_id',
        //                 'users.username',
        //                 'perusahaans.nama_perusahaan',
        //                 'materis.nama_materi',
        //                 'outstandings.net_sales',
        //                 'outstandings.due_date',
        //                 'outstandings.status_pembayaran'
        //             )
        //             ->get();

        //         $notificationsSent = 0;

        //         foreach ($outstandings as $item) {
        //             try {
        //                 $userData = [
        //                     'user' => $item->username,
        //                     'nama_perusahaan' => $item->nama_perusahaan,
        //                     'nama_materi' => $item->nama_materi,
        //                     'net_sales' => $item->net_sales,
        //                     'due_date' => $item->due_date,
        //                     'status_pembayaran' => $item->status_pembayaran,
        //                 ];

        //                 $user = User::find($item->user_id);
        //                 if ($user) {
        //                     $user->notify(new OutstandingNotification($userData, '/outstanding'));
        //                     $notificationsSent++;
        //                 }
        //             } catch (\Exception $e) {
        //                 Log::error("Gagal mengirim notifikasi ke user ID {$item->user_id}: " . $e->getMessage());
        //                 continue;
        //             }
        //         }

        //         Log::info("Job selesai. Total notifikasi yang dikirim: {$notificationsSent}");
        //     } catch (\Throwable $e) {
        //         Log::error("Error di job outstanding: " . $e->getMessage());
        //     }
        // })->dailyAt('08:00');

        $schedule->job(new SendDailyOutstandingNotificationsJob)
         ->dailyAt('08:00');
        //  ->everyMinute();

        // $schedule->command('app:update-status')->dailyAt('23:00');
        $schedule->job(new UpdateStatusJob)
         ->dailyAt('23:00');
            // ->everyMinute();


        // Rekap Harian
            $schedule->call(function () {
                $kemarin = Carbon::yesterday()->toDateString();
                GenerateAbsensiReportJob::dispatch($kemarin, $kemarin, 'harian');
            })
            ->dailyAt('23:55')
            // ->everyMinute()
            ->name('Rekap Absensi Harian')
            ->withoutOverlapping();

        // JADWAL BARU: Rekap Absensi Bulanan (dijalankan setiap awal bulan jam 01:00)
        // $schedule->job(function() {
        //              $bulanLalu = \Carbon\Carbon::now()->subMonth();
        //              return new GenerateAbsensiReportJob(
        //                  $bulanLalu->startOfMonth()->toDateString(),
        //                  $bulanLalu->endOfMonth()->toDateString(),
        //                  'bulanan'
        //              );
        //          })
        //          ->monthlyOn(1, '01:00') // Jalankan tanggal 1 setiap bulan jam 1 pagi
        //          ->name('Rekap Absensi Bulanan')
        //          ->withoutOverlapping();
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
