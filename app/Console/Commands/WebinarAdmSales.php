<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\YearMapping;
use App\Notifications\WebinarAdmSalesNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class WebinarAdmSales extends Command
{
    protected $signature = 'webinar:notify-adm-sales
        {--now : Kirim sekarang untuk webinar terdekat (abaikan aturan H-14 dan cek duplikat), untuk test}
        {--user= : Batasi penerima ke satu user ID, untuk test}';

    protected $description = 'Kirim notifikasi pengingat webinar (H-14) ke user dengan jabatan Adm Sales';

    protected string $path = '/crm/timeline-webinar';

    private const DAYS_BEFORE = 14;

    public function handle(): int
    {
        $test  = (bool) $this->option('now');
        $today = Carbon::today();

        if ($test) {
            $webinars = YearMapping::with('eventDetail')
                ->whereNotNull('planned_date')
                ->whereDate('planned_date', '>=', $today->toDateString())
                ->orderBy('planned_date')
                ->limit(1)
                ->get();

            if ($webinars->isEmpty()) {
                $webinars = YearMapping::with('eventDetail')
                    ->whereNotNull('planned_date')
                    ->orderByDesc('planned_date')
                    ->limit(1)
                    ->get();
            }
        } else {
            $webinars = YearMapping::with('eventDetail')
                ->whereNotNull('planned_date')
                ->whereDate('planned_date', '>=', $today->toDateString())
                ->get()
                ->filter(fn ($mapping) => Carbon::parse($mapping->planned_date)
                    ->subDays(self::DAYS_BEFORE)
                    ->isSameDay($today));
        }

        if ($webinars->isEmpty()) {
            $this->info($test
                ? 'Tidak ada webinar dengan planned_date.'
                : 'Tidak ada webinar yang tepat H-' . self::DAYS_BEFORE . ' hari ini.');
            return self::SUCCESS;
        }

        $userId = $this->option('user');

        $receivers = $userId
            ? User::whereKey($userId)->get()
            : User::where('jabatan', 'Adm Sales')->where('status_akun', "1")->get();

        if ($receivers->isEmpty()) {
            $this->warn($userId
                ? "User dengan ID {$userId} tidak ditemukan."
                : 'Tidak ada user dengan jabatan Adm Sales.');
            return self::SUCCESS;
        }

        $sent = 0;

        foreach ($webinars as $mapping) {
            $hariH = Carbon::parse($mapping->planned_date);

            $data = [
                'year_mapping_id' => $mapping->id,
                'nama_webinar'    => $mapping->eventDetail->title ?? 'Belum ada judul',
                'tema'            => $mapping->theme ?? '-',
                'tanggal'         => $hariH->translatedFormat('l, d F Y'),
            ];

            foreach ($receivers as $user) {
                // Sekali saja per webinar per user (dilewati di mode test)
                if (!$test) {
                    $already = $user->notifications()
                        ->where('type', WebinarAdmSalesNotification::class)
                        ->where('data->message->year_mapping_id', $mapping->id)
                        ->exists();

                    if ($already) {
                        continue;
                    }
                }

                $user->notify(new WebinarAdmSalesNotification($data, $this->path, $user->id));
                $sent++;
            }
        }

        $this->info("Notifikasi terkirim: {$sent}");

        return self::SUCCESS;
    }
}