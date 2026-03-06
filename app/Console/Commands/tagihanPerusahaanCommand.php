<?php

namespace App\Console\Commands;

use App\Models\tagihanPerusahaan;
use App\Models\trackingTagihanPerusahaan;
use Carbon\Carbon;
use Illuminate\Console\Command;

class tagihanPerusahaanCommand extends Command
{
    protected $signature = 'app:tagihan-perusahaan-command';
    protected $description = 'generate tagihan perusahaan dan mengatur status';


    public function handle()
    {
        $now = Carbon::now()->year(2027);

        $tagihans = tagihanPerusahaan::all();

        foreach ($tagihans as $tagihan)
        {
            $tanggalMulai = Carbon::parse($tagihan->tanggal_perkiraan_mulai);
            $tanggalSelesai = $tagihan->tanggal_perkiraan_selesai  ? Carbon::parse($tagihan->tanggal_perkiraan_selesai) : null;
        
            if ($tagihan->tipe === 'bulanan')
            {
    
                if ($tanggalMulai->lte($now))
                {
                    trackingTagihanPerusahaan::create([
                        'id_tagihan_perusahaan' => $tagihan->id,
                        'nominal' => $tagihan->nominal,
                        'tracking' => 'Diajukan dan Sedang Ditinjau oleh Finance',
                    ]);

                    $tagihan->update([
                        'diperbaharui' => 1,
                        'last_generate' => $now,
                        'tanggal_perkiraan_mulai' => $tanggalMulai->addMonth(),
                        'tanggal_perkiraan_selesai' => optional($tanggalSelesai)->addMonth(),
                    ]);

                    $this->info('Tracking tagihan berhasil dibuat.');
                }

                if ($tanggalMulai->year != $now->year)
                {
                    $tagihan->update([
                        'tanggal_perkiraan_mulai' => $tanggalMulai->addYear(),
                        'tanggal_perkiraan_selesai' => optional($tanggalSelesai)->addYear(),
                    ]);              
                }

            } elseif ($tagihan->tipe === 'tahunan')
            {

                if ($tanggalMulai->year < $now->year)
                {
                    trackingTagihanPerusahaan::create([
                        'id_tagihan_perusahaan' => $tagihan->id,
                        'nominal' => $tagihan->nominal,
                        'tracking' => 'Diajukan dan Sedang Ditinjau oleh Finance',
                    ]);

                    $tagihan->update([
                        'diperbaharui' => 1,
                        'last_generate' => $now,
                        'tanggal_perkiraan_mulai' => $tanggalMulai->addYear(),
                        'tanggal_perkiraan_selesai' => optional($tanggalSelesai)->addYear(),
                    ]);

                    $this->info('Tracking tagihan berhasil dibuat.');
                }
            }

            
        }

    }
}
