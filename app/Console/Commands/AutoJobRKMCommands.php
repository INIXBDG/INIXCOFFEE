<?php

namespace App\Console\Commands;

use App\Models\RKM;
use Illuminate\Console\Command;

class AutoJobRKMCommands extends Command
{
    protected $signature = 'RKM:auto-job';
    protected $description = 'Auto isi otomatis instruktur RKM - hanya Senin jam 07:00';

    public function handle()
    {
        if (! today()->isMonday()) {
            return;
        }

        $senin  = today()->startOfWeek()->format('Y-m-d');  // 2025-12-08
        $minggu = today()->endOfWeek()->format('Y-m-d');    // 2025-12-14

        $rkm = RKM::where('tanggal_awal', '<=', $minggu)
            ->where('tanggal_akhir', '>=', $senin)
            ->get();

        if ($rkm->isEmpty()) {
            return;
        }

        $groups = [];
        foreach ($rkm as $item) {
            $awal  = substr($item->tanggal_awal, 0, 10);
            $akhir = substr($item->tanggal_akhir, 0, 10);
            $key   = $item->materi_key . '|' . $awal . '|' . $akhir;

            $groups[$key][] = $item;
        }

        $totalDiisi = 0;

        foreach ($groups as $group) {
            $instrukturKey  = null;
            $instrukturKey2 = null;
            $asistenKey     = null;

            foreach ($group as $r) {
                if ($r->instruktur_key !== null && trim($r->instruktur_key) !== '' && trim($r->instruktur_key) !== '-') {
                    $instrukturKey = $r->instruktur_key;
                }

                if ($r->instruktur_key2 !== null && trim($r->instruktur_key2) !== '' && $r->instruktur_key2 !== '-') {
                    $instrukturKey2 = $r->instruktur_key2;
                }

                if ($r->asisten_key !== null && trim($r->asisten_key) !== '' && $r->asisten_key !== '-') {
                    $asistenKey = $r->asisten_key;
                }

                if ($instrukturKey !== null) {
                    break;
                }
            }

            if ($instrukturKey === null) {
                continue;
            }

            foreach ($group as $r) {
                $perluSimpan = false;

                if ($r->instruktur_key === null || trim($r->instruktur_key) === '' || $r->instruktur_key === '-') {
                    $r->instruktur_key = $instrukturKey;
                    $perluSimpan = true;
                }

                if ($instrukturKey2 === '-') {
                    $r->instruktur_key2 = '-';
                    $perluSimpan = true;
                } elseif ($instrukturKey2 !== null) {
                    if ($r->instruktur_key2 === null || trim($r->instruktur_key2) === '' || $r->instruktur_key2 === '-') {
                        $r->instruktur_key2 = $instrukturKey2 ?? '-';
                        $perluSimpan = true;
                    }
                }

                if ($asistenKey === '-') {
                    $r->asisten_key = '-';
                    $perluSimpan = true;
                } elseif ($asistenKey !== null) {
                    if ($r->asisten_key === null || trim($r->asisten_key) === '' || $r->asisten_key === '-') {
                        $r->asisten_key = $asistenKey ?? '-';
                        $perluSimpan = true;
                    }
                }

                if ($perluSimpan) {
                    $r->saveQuietly();
                    $totalDiisi++;
                }
            }
        }

        $this->info("Selesai. {$totalDiisi} kolom berhasil diisi otomatis.");
    }
}
