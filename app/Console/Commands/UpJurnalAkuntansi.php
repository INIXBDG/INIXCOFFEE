<?php

namespace App\Console\Commands;

use App\Models\JurnalAkuntansi;
use App\Models\PengajuanBarang;
use Illuminate\Console\Command;

class UpJurnalAkuntansi extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:up-jurnal-akuntansi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'auto up pengajuan barang ke jurnal akuntansi setiap pagi pukul 07:00';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dataPengajuanBarang = PengajuanBarang::with('detail', 'karyawan')
            ->whereNotNull('no_kk')
            ->whereNotNull('tanggal_pencairan')
            ->get();

        $totalDibuat = 0;
        $totalDilewati = 0;

        foreach ($dataPengajuanBarang as $pengajuan) {

            // Cek apakah jurnal untuk pengajuan ini sudah ada
            $jurnalExist = JurnalAkuntansi::whereJsonContains(
                'id_pengajuan_barang',
                (int) $pengajuan->id
            )->exists();

            if ($jurnalExist) {
                $totalDilewati++;
                continue;
            }

            // Hitung total pengeluaran dari detail pengajuan
            $totalPengeluaran = 0;
            foreach ($pengajuan->detail as $item) {
                $qtyValue   = (int) $item->qty;
                $hargaClean = str_replace('.', '', $item->harga);
                $totalPengeluaran += ($qtyValue * (float) $hargaClean);
            }

            $keterangan = 'Pengeluaran untuk Pengajuan Barang dari : '
                . $pengajuan->karyawan->nama_lengkap
                . ' (' . $pengajuan->tipe . ')';

            JurnalAkuntansi::create([
                'nomor_kk'            => $pengajuan->no_kk,
                'id_pengajuan_barang' => [(int) $pengajuan->id],
                'tanggal_transaksi'   => now(),
                'keterangan'          => $keterangan,
                'kredit'              => $totalPengeluaran,
                'debit'               => 0,
            ]);

            $totalDibuat++;
        }

        $this->info("Jurnal dibuat: {$totalDibuat}, dilewati (sudah ada/belum layak): {$totalDilewati}");
    }
}