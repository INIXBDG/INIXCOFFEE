<?php

namespace App\Console;

use App\Console\Commands\AutoJobRKMCommands;
use App\Models\activityLog;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Notifications\OutstandingNotification;
use Illuminate\Support\Facades\Notification;
use App\Models\Outstanding;
use App\Models\User;
use App\Models\RKM;
use App\Notifications\SurveyReminderNotification;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\ActivityInstruktur;

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

        $schedule->call(function () {
            try {
                $now = now();

                // Ambil user beserta survey terakhir
                $users = User::with(['surveyKepuasan' => fn($q) => $q->latest('created_at')])->get();

                $notifications = [];

                foreach ($users as $user) {
                    $lastSurvey = $user->surveyKepuasan->first();

                    // Belum pernah atau sudah >= 3 bulan
                    if (!$lastSurvey || $now->diffInMonths($lastSurvey->created_at) >= 3) {
                        $notifications[] = [
                            'id' => \Illuminate\Support\Str::uuid(),
                            'type' => 'App\\Notifications\\SurveyReminderNotification',
                            'notifiable_type' => 'App\\Models\\User',
                            'notifiable_id' => $user->id,
                            'data' => json_encode([
                                'user' => 'System',
                                'message' => [
                                    'tipe' => 'survey_reminder',
                                    'judul' => 'Survey Kepuasan ITSM!',
                                    'deskripsi' => 'Dimohon untuk anda dapat mengisi survey kepuasan pelayanan ITSM.',
                                ],
                                'path' => route('surveyKepuasan.create'),
                                'status' => 'unread',
                                'data' => [
                                    'id_user' => $user->id,
                                    'terakhir_survey' => $lastSurvey ? $lastSurvey->created_at->format('d/m/Y') : 'Belum Pernah',
                                ]
                            ]),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                // Insert batch hanya jika ada notifikasi
                if (!empty($notifications)) {
                    DB::table('notifications')->insert($notifications);
                    Log::info('Survey reminder executed successfully. Total: ' . count($notifications));
                } else {
                    Log::info('Survey reminder executed, no pending notifications.');
                }
            } catch (\Throwable $e) {
                Log::error('Survey reminder failed: ' . $e->getMessage());
            }
        })->dailyAt('14:04');

        $schedule->call(function () {
            try {
                activityLog::whereNotIn('status', [
                    'login',
                    'logout',
                    'Absen Masuk',
                    'Absen Keluar',
                    'UpTime'
                ])->delete();

                Log::info("Data activityLog dengan status 'visit' berhasil dihapus oleh scheduler.");
            } catch (\Throwable $e) {
                Log::error("Schedule gagal: " . $e->getMessage());
            }
        })->weeklyOn(2, '08:00');

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
                    ->whereDate('outstandings.due_date', now()->addDay()->toDateString())
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
        })->dailyAt('08:00');

        $schedule->command('app:update-status')->dailyAt('23:00');

        $schedule->command('uptime:check')->everySixHours();

        // Di dalam method schedule(Schedule $schedule)
        $schedule->call(function () {
            // Tentukan tanggal akhir minggu yang harus dikunci (e.g., dua minggu yang lalu)
            $lockEndDate = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks(2)->endOfWeek(Carbon::SUNDAY);

            // Kunci semua activity_instrukturs hingga tanggal tersebut yang belum terkunci
            ActivityInstruktur::where('activity_date', '<=', $lockEndDate)
                ->where('is_locked', 0)
                ->update(['is_locked' => 1]);
        })->dailyAt('01:00'); // Jalankan setiap hari pukul 01:00
        $schedule->command('RKM:auto-job')->mondays()->at('10:47');
    }
    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    // protected $commands = [
    //     \App\Console\Commands\CheckUptime::class,
    // ];

}
