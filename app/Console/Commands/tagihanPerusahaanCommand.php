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
        $now = Carbon::now();

        $tagihans = tagihanPerusahaan::all();

        foreach ($tagihans as $tagihan)
        {
            $lastGenerate = Carbon::parse($tagihan->last_generate);
            $tanggalMulai = Carbon::parse($tagihan->tanggal_perkiraan_mulai);
            $tanggalSelesai = $tagihan->tanggal_perkiraan_selesai  ? Carbon::parse($tagihan->tanggal_perkiraan_selesai) : null;
        
            if ($tagihan->tipe === 'bulanan')
            {
    
                if (!$lastGenerate->isSameMonth($now))
                {
                    trackingTagihanPerusahaan::create([
                        'id_tagihan_perusahaan' => $tagihan->id,
                        'nominal' => $tagihan->nominal,
                        'tracking' => 'Diajukan dan Sedang Ditinjau oleh Finance',
                        'tanggal_perkiraan_mulai' => $tanggalMulai->copy()->addMonth(),
                        'tanggal_perkiraan_selesai' => $tanggalSelesai ? $tanggalSelesai->copy()->addMonth() : $tagihan->tanggal_perkiraan_selesai,
                    ]);

                    $tagihan->update([
                        'last_generate' => $now,
                        'tanggal_perkiraan_mulai' => $tanggalMulai->copy()->addMonth(),
                        'tanggal_perkiraan_selesai' => $tanggalSelesai ? $tanggalSelesai->copy()->addMonth() : $tagihan->tanggal_perkiraan_selesai,
                    ]);

                    $this->info('Tracking tagihan berhasil dibuat.');
                }

            } elseif ($tagihan->tipe === 'tahunan')
            {

                if ($lastGenerate->year < $now->year)
                {
                    trackingTagihanPerusahaan::create([
                        'id_tagihan_perusahaan' => $tagihan->id,
                        'nominal' => $tagihan->nominal,
                        'tracking' => 'Diajukan dan Sedang Ditinjau oleh Finance',
                        'tanggal_perkiraan_mulai' => $tanggalMulai->copy()->addYear(),
                        'tanggal_perkiraan_selesai' => $tanggalSelesai ? $tanggalSelesai->copy()->addYear() : null,
                    ]);

                    $tagihan->update([
                        'last_generate' => $now,
                        'tanggal_perkiraan_mulai' => $tanggalMulai->copy()->addYear(),
                        'tanggal_perkiraan_selesai' => $tanggalSelesai ? $tanggalSelesai->copy()->addYear() : null,
                    ]);

                    $this->info('Tracking tagihan berhasil dibuat.');
                }
            }

            // set status otomatis
            $tracking = $tagihan->trackingTagihan->first();
            $dueDate = null;

            if (!$tracking) continue;

            if($tracking->tanggal_perkiraan_selesai) {
                $dueDate = Carbon::parse($tracking->tanggal_perkiraan_selesai);
            } else {
                $dueDate = Carbon::parse($tracking->tanggal_perkiraan_mulai);
            }         
            
            if ($dueDate->day < $now->day && !in_array($tracking->status, ['selesai', 'pending'])) {
                $tracking->status = 'telat';
                $tracking->save();
                $this->info('status tagihan terupdate');
            }
                
        }

    }
}
