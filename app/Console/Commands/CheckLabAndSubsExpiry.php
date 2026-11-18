<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lab;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\ApprovalLabSubsNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CheckLabAndSubsExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'labsubs:check-expiry';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Memeriksa masa aktif (end_date) Lab & Subscription untuk mengirim pengingat H-7, H-3, H-1 serta mengubah status menjadi expired jika sudah melewati batas waktu.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today       = Carbon::today();
        $target7Days = $today->copy()->addWeek()->toDateString();
        $target3Days = $today->copy()->addDays(3)->toDateString();
        $target1Day  = $today->copy()->addDay()->toDateString();
        $targets     = [$target7Days, $target3Days, $target1Day];

        $itsmUsers = User::whereHas('karyawan', function ($q) {
            $q->whereIn('jabatan', ['Koordinator ITSM', 'Technical Support']);
        })->get();

        $reminderSentCount = 0;
        $expiredCount      = 0;

        // =====================================================================
        // 1. CEK PENGINGAT MASA AKTIF (H-7, H-3, H-1) UNTUK LAB & SUBSCRIPTION
        // =====================================================================
        $expiringLabs = Lab::with(['pengajuans.karyawan', 'pengajuans.tracking'])
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereIn(DB::raw('DATE(end_date)'), $targets)
            ->get();

        foreach ($expiringLabs as $lab) {
            $reminderSentCount += $this->processReminderItem(
                $lab,
                'Lab',
                $lab->nama_labs,
                $lab->merk,
                '/pengajuanlabsdansubs',
                $itsmUsers,
                $today,
                $target7Days,
                $target3Days
            );
        }

        $expiringSubs = Subscription::with(['pengajuans.karyawan', 'pengajuans.tracking'])
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereIn(DB::raw('DATE(end_date)'), $targets)
            ->get();

        foreach ($expiringSubs as $sub) {
            $reminderSentCount += $this->processReminderItem(
                $sub,
                'Subscription',
                $sub->nama_subs,
                $sub->merk,
                '/pengajuansubs',
                $itsmUsers,
                $today,
                $target7Days,
                $target3Days
            );
        }

        // =====================================================================
        // 2. OTOMATIS SET EXPIRED JIKA SUDAH MELEWATI END_DATE (< TODAY)
        // =====================================================================
        $expiredLabs = Lab::with(['pengajuans.karyawan', 'pengajuans.tracking'])
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', $today)
            ->get();

        foreach ($expiredLabs as $lab) {
            $this->processExpiredItem(
                $lab,
                'Lab',
                $lab->nama_labs,
                $lab->merk,
                '/pengajuanlabsdansubs',
                $itsmUsers,
                $today
            );
            $expiredCount++;
        }

        $expiredSubs = Subscription::with(['pengajuans.karyawan', 'pengajuans.tracking'])
            ->where('status', 'active')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', $today)
            ->get();

        foreach ($expiredSubs as $sub) {
            $this->processExpiredItem(
                $sub,
                'Subscription',
                $sub->nama_subs,
                $sub->merk,
                '/pengajuansubs',
                $itsmUsers,
                $today
            );
            $expiredCount++;
        }

        $this->info("Pengecekan selesai. Notifikasi pengingat terkirim: {$reminderSentCount}, Item otomatis di-set Expired: {$expiredCount}.");

        return Command::SUCCESS;
    }

    /**
     * Memproses pengiriman notifikasi pengingat H-7, H-3, atau H-1
     */
    private function processReminderItem($item, string $kategori, string $namaItem, ?string $merk, string $path, $itsmUsers, Carbon $today, string $target7Days, string $target3Days): int
    {
        $endDateStr = Carbon::parse($item->end_date)->toDateString();

        if ($endDateStr === $target7Days) {
            $hari        = 7;
            $rentangTeks = '1 minggu (7 hari) lagi';
        } elseif ($endDateStr === $target3Days) {
            $hari        = 3;
            $rentangTeks = '3 hari lagi';
        } else {
            $hari        = 1;
            $rentangTeks = 'besok (1 hari lagi)';
        }

        $merkLabel   = $merk ?: 'Tanpa Vendor';
        $namaLengkap = "{$namaItem} ({$merkLabel})";
        $tglBerakhir = Carbon::parse($item->end_date)->translatedFormat('d F Y');
        $statusText  = "Pengingat H-{$hari}: Masa aktif {$kategori} {$namaItem} ({$merkLabel}) akan berakhir dalam {$rentangTeks} pada tanggal {$tglBerakhir}. Silakan lakukan perpanjangan jika masih dibutuhkan.";

        $notifData = [
            'tanggal' => now(),
            'status'  => $statusText,
        ];

        $receivers = $this->getReceiversForItem($item, $itsmUsers);
        $sent      = 0;

        foreach ($receivers as $user) {
            if ($this->sendUniqueNotification($user, $notifData, $path, $namaLengkap, 'Peringatan Masa Aktif Lab/Subscription', $today)) {
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * Memproses item yang masa aktifnya telah habis (Expired)
     */
    private function processExpiredItem($item, string $kategori, string $namaItem, ?string $merk, string $path, $itsmUsers, Carbon $today): void
    {
        $receivers = $this->getReceiversForItem($item, $itsmUsers);

        $item->update([
            'status'    => 'expired',
            'is_active' => false,
        ]);

        $merkLabel   = $merk ?: 'Tanpa Vendor';
        $namaLengkap = "{$namaItem} ({$merkLabel})";
        $tglBerakhir = Carbon::parse($item->end_date)->translatedFormat('d F Y');
        $statusText  = "Masa aktif {$kategori} {$namaItem} ({$merkLabel}) telah berakhir (Expired) per tanggal {$tglBerakhir}. Status otomatis diubah menjadi Expired.";

        $notifData = [
            'tanggal' => now(),
            'status'  => $statusText,
        ];

        foreach ($receivers as $user) {
            $this->sendUniqueNotification($user, $notifData, $path, $namaLengkap, 'Masa Aktif Lab/Subscription Habis', $today);
        }
    }

    /**
     * Mengambil daftar User penerima (ITSM + Pengguna yang sedang aktif menggunakan item tersebut)
     */
    private function getReceiversForItem($item, $itsmUsers)
    {
        $activeKodeKaryawans = $item->pengajuans->filter(function ($p) {
            $tracks      = $p->tracking->pluck('tracking')->implode(' ');
            $tracksLower = strtolower($tracks);

            $hasAccess    = str_contains($tracksLower, 'lihat akses nya di detail')
                || str_contains($tracksLower, 'akses lab telah diberikan')
                || str_contains($tracksLower, 'akses subscription siap digunakan')
                || str_contains($tracksLower, 'siap digunakan');
            $isUnassigned = str_contains($tracksLower, 'di-unassign') || str_contains($tracksLower, 'akses dicabut');

            return $hasAccess && !$isUnassigned;
        })->pluck('kode_karyawan')->filter()->unique()->values()->toArray();

        if (empty($activeKodeKaryawans)) {
            return $itsmUsers->unique('id');
        }

        $activeUsers = User::whereHas('karyawan', function ($q) use ($activeKodeKaryawans) {
            $q->whereIn('kode_karyawan', $activeKodeKaryawans);
        })->get();

        return $itsmUsers->concat($activeUsers)->unique('id');
    }

    /**
     * Mengirim notifikasi dengan pengecekan anti-duplikasi di hari yang sama
     */
    private function sendUniqueNotification(User $user, array $notifData, string $path, string $namaLengkap, string $type, Carbon $today): bool
    {
        $statusText = $notifData['status'];

        $alreadySent = $user->notifications()
            ->whereDate('created_at', $today)
            ->where('data->message->status', $statusText)
            ->exists();

        if ($alreadySent) {
            return false;
        }

        NotificationFacade::send(
            $user,
            new ApprovalLabSubsNotification(
                $notifData,
                $path,
                $namaLengkap,
                $type,
                $user->id
            )
        );

        return true;
    }
}
