<?php

namespace App\Console;

use App\Models\User;
use App\Models\Outstanding;
use App\Models\RKM;
use App\Models\ActivityInstruktur;
use App\Models\activityLog;
use App\Notifications\OutstandingNotification;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 1. Kirim Notifikasi Outstanding Mingguan
        $schedule->call(function () {
            try {
                $outstandings = Outstanding::where('status_pembayaran', '0')
                    ->whereDate('due_date', '>=', now())
                    ->get();

                $financeUsers = User::where('jabatan', 'Finance & Accounting')->get();
                $path = '/outstanding';

                foreach ($outstandings as $outstanding) {
                    Notification::send($financeUsers, new OutstandingNotification($outstanding, $path));
                }
            } catch (\Exception $e) {
                Log::error('Failed to send notifications: ' . $e->getMessage());
                throw $e; 
            }
        })->weeklyOn(1, '08:00')->description('Kirim Notifikasi Outstanding Mingguan');

        // 2. Update Status Read Notifikasi Outstanding
        $schedule->call(function () {
            try {
                $outstandings = Outstanding::where('status_pembayaran', '1')->get();

                foreach ($outstandings as $outstanding) {
                    $rkm = RKM::where('id', $outstanding->id_rkm)->with('perusahaan', 'materi')->first();

                    if ($rkm && $rkm->perusahaan && $rkm->materi) {
                        DB::table('notifications')
                            ->where('type', 'App\Notifications\OutstandingNotification')
                            ->whereJsonContains('data->message->nama_perusahaan', $rkm->perusahaan->nama_perusahaan)
                            ->whereJsonContains('data->message->nama_materi', $rkm->materi->nama_materi)
                            ->whereJsonContains('data->message->due_date', $outstanding->due_date)
                            ->update(['read_at' => Carbon::now()]);
                    }
                }
            } catch (\Throwable $e) {
                Log::error('Update Status Read failed: ' . $e->getMessage());
                throw $e;
            }
        })->dailyAt('23:00')->description('Update Status Read Notifikasi Outstanding');

        // 3. Kirim Reminder Survey Kepuasan ITSM
        $schedule->call(function () {
            try {
                $now = now();
                $users = User::with(['surveyKepuasan' => fn($q) => $q->latest('created_at')])->get();
                $notifications = [];

                foreach ($users as $user) {
                    $lastSurvey = $user->surveyKepuasan->first();

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
                                'path' => route('surveykepuasan.index'),
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

                if (!empty($notifications)) {
                    DB::table('notifications')->insert($notifications);
                    Log::info('Survey reminder executed successfully. Total: ' . count($notifications));
                } else {
                    Log::info('Survey reminder executed, no pending notifications.');
                }
            } catch (\Throwable $e) {
                Log::error('Survey reminder failed: ' . $e->getMessage());
                throw $e;
            }
        })->dailyAt('14:04')->description('Kirim Reminder Survey Kepuasan ITSM');

        // 4. Pembersihan Otomatis Activity Log
        $schedule->call(function () {
            try {
                $deleted = activityLog::query()
                    ->whereIn('status', [
                        'visit',
                        'login',
                        'logout',
                        'Absen Masuk',
                        'Absen Keluar'
                    ])
                    ->delete();

                Log::info("Berhasil menghapus {$deleted} activity log dari status visit/login/logout/absen.");
            } catch (\Throwable $e) {
                Log::error("Schedule gagal: " . $e->getMessage());
                throw $e;
            }
        })->weeklyOn(2, '08:00')->description('Pembersihan Otomatis Activity Log');

        // 5. Kirim Notifikasi Outstanding H-1
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
                        ->whereDate('outstandings.due_date', now()->addDay()->toDateString())
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
                throw $e;
            }
        })->dailyAt('08:00')->description('Kirim Notifikasi Outstanding H-1');

        // 6. Kunci Otomatis Aktivitas Instruktur
        $schedule->call(function () {
            try {
                $lockEndDate = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeeks(2)->endOfWeek(Carbon::SUNDAY);
                ActivityInstruktur::where('activity_date', '<=', $lockEndDate)
                    ->where('is_locked', 0)
                    ->update(['is_locked' => 1]);
            } catch (\Throwable $e) {
                Log::error("Error Kunci Aktivitas: " . $e->getMessage());
                throw $e;
            }
        })->dailyAt('01:00')->description('Kunci Otomatis Aktivitas Instruktur');

        // Artisan Command Tasks
        $schedule->command('app:update-status')->dailyAt('23:00')->description('app:update-status');
        $schedule->command('uptime:check')->everySixHours()->description('uptime:check');
        $schedule->command('assign:shift2')->dailyAt('17:30')->description('assign:shift2');
        $schedule->command('RKM:auto-job')->sundays()->at('00:47')->description('RKM:auto-job');
        $schedule->command('app:update-cuti')->yearlyOn(2, 1, '00:01')->description('app:update-cuti');
        $schedule->command('peluang:check')->dailyAt('12:00')->description('peluang:check');
        $schedule->command('app:tagihan-perusahaan-command')->daily()->description('app:tagihan-perusahaan-command');
        $schedule->command('app:update-administrasi-karyawan')->daily()->description('app:update-administrasi-karyawan');
        $schedule->command('app:generate-libur-nasional')->daily()->description('app:generate-libur-nasional');
        $schedule->command('perusahaan:evaluasi-status')->daily()->description('perusahaan:evaluasi-status');
        $schedule->command('app:notification-pembelian-hr')->daily()->description('app:notification-pembelian-hr');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}