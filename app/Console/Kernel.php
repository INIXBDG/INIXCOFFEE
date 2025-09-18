<?php

namespace App\Console;

use App\Mail\KaryawanMail;
use App\Models\karyawan;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Notification;
use App\Models\Outstanding;
use App\Models\User;
use App\Models\RKM;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Google\Service\ServiceControl\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Notifications\OutstandingNotification;


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

        // $schedule->call(function () {
        //     try {
        //         $userId = '29';
        //         $karyawan = Karyawan::find($userId);

        //             Mail::to('vickypplg12@gmail.com')
        //                 ->send(new KaryawanMail($karyawan));
        //     } catch (\Throwable $e) {
        //         Log::error("Schedule gagal: " . $e->getMessage());
        //     }
        // })->everyMinute();

        $schedule->call(function () {
            try {
                DB::table('notifications')->insertUsing(
                    [
                        'id',
                        'type',
                        'notifiable_type',
                        'notifiable_id',
                        'data',
                        'created_at',
                        'updated_at'
                    ],
                    DB::table('outstandings')
                        ->join('r_k_m_s', 'r_k_m_s.id', '=', 'outstandings.id_rkm')
                        ->join('perusahaans', 'r_k_m_s.perusahaan_key', '=', 'perusahaans.id')
                        ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
                        ->join('users', function ($join) {
                            $join->on(DB::raw("'Finance & Accounting'"), '=', 'users.jabatan');
                        })
                        ->where('outstandings.status_pembayaran', '0')
                        ->whereDate('outstandings.due_date', now()->addDay()->toDateString()) // hanya H-1
                        ->selectRaw('
                    UUID() as id,
                    "App\\\Notifications\\\OutstandingNotification" as type,
                    "App\\\Models\\\User" as notifiable_type,
                    users.id as notifiable_id,
                    JSON_OBJECT(
                        "user", users.username,
                        "message", JSON_OBJECT(
                            "nama_perusahaan", perusahaans.nama_perusahaan,
                            "nama_materi", materis.nama_materi,
                            "net_sales", outstandings.net_sales,
                            "due_date", outstandings.due_date,
                            "status_pembayaran", outstandings.status_pembayaran,
                            "tipe", "Outstanding"
                        ),
                        "path", "/outstanding",
                        "status", "unread"
                    ) as data,
                    NOW() as created_at,
                    NOW() as updated_at
                ')
                );

                $outstandings = DB::table('outstandings')
                    ->join('r_k_m_s', 'r_k_m_s.id', '=', 'outstandings.id_rkm')
                    ->join('perusahaans', 'r_k_m_s.perusahaan_key', '=', 'perusahaans.id')
                    ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
                    ->join('users', function ($join) {
                        $join->on(DB::raw("'Finance & Accounting'"), '=', 'users.jabatan');
                    })
                    ->where('outstandings.status_pembayaran', '0')
                    ->whereDate('outstandings.due_date', now()->addDay()->toDateString()) // hanya H-1
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
                        Log::error("Gagal mengirim notifikasi ke user ID {$item->user_id}: " . $e->getMessage());
                        continue;
                    }
                }

                Log::info("Job selesai. Total notifikasi yang dikirim: {$notificationsSent}");
            } catch (\Throwable $e) {
                Log::error("Error di job outstanding: " . $e->getMessage());
            }
        })->dailyAt('15:10');

        // $schedule->call(function () {
        //     try {
        //         Log::info('Mulai menjalankan GenerateOutstandingNotificationsJob');
        //         $outstandings = DB::table('outstandings')
        //             ->join('r_k_m_s', 'outstandings.id_rkm', '=', 'r_k_m_s.id')
        //             ->join('users', 'users.id_sales', '=', 'r_k_m_s.sales_key')
        //             ->join('perusahaans', 'r_k_m_s.perusahaan_key', '=', 'perusahaans.id')
        //             ->join('materis', 'r_k_m_s.materi_key', '=', 'materis.id')
        //             ->select(
        //                 'users.id as user_id',
        //                 'users.username',
        //                 'perusahaans.nama_perusahaan',
        //                 'materis.nama_materi',
        //                 'outstandings.net_sales',
        //                 'outstandings.due_date',
        //                 'outstandings.status_pembayaran'
        //             )->where('outstandings.status_pembayaran', '0')
        //             ->whereBetween('outstandings.due_date', [now()->toDateString(), now()->addMonth()->toDateString()])
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
        //     } catch (QueryException $e) {
        //         Log::error('Error saat query database dalam job: ' . $e->getMessage());
        //         throw $e;
        //     } catch (\Exception $e) {
        //         Log::error('Error tidak terduga dalam GenerateOutstandingNotificationsJob: ' . $e->getMessage());
        //         throw $e;
        //     }
        // })->everyMinute();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
