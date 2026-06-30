<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PembelianHr;
use App\Models\User;
use \Carbon\Carbon;
use App\Notifications\PembelianHrNotification;
use Illuminate\Support\Facades\Notification as NotificationFacade;

class NotificationPembelianHr extends Command
{
    protected $signature = 'app:notification-pembelian-hr';
    protected $description = 'Notifikasi untuk rencana pembelian hr';

    public function handle()
    {
        $pembelianHr = PembelianHr::with('details')->get();
        $now = Carbon::now();

        foreach ($pembelianHr as $p) {
            $periodeMap = [
                'Q1' => '28 03',
                'Q2' => '29 06',
                'Q3' => '27 09',
                'Q4' => '28 12',
            ];

            $barang = $p->details
                ->pluck('nama_barang')
                ->implode(', ');

            $path = '/HR-dashboard/rencana-pembelian';

            if ($periodeMap[$p->periode] === $now->format('d m') && $p->created_at->format('Y') === $now->format('Y') && $p->status_pembelian === 'Rencana') {
                $receiver = User::findOrFail($p->id_karyawan);
                NotificationFacade::send($receiver, new PembelianHrNotification($barang, $p->periode, $path, $receiver->id));
                $this->info('Notification pembelian hr ');
            }
        }
    }
}
