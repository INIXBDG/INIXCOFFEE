<?php

namespace App\Console\Commands;

use App\Models\Modul;
use App\Models\Materi;
use Illuminate\Console\Command;

class MatchMateri extends Command
{
    protected $signature = 'materi:match {--dry-run}';
    protected $description = 'Match kode_materi/nama_materi di moduls ke id materi di materis';

    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $materis = Materi::all();
        $moduls = Modul::whereNull('id_materi')->get();

        $matched = 0;
        $unmatched = [];

        foreach ($moduls as $modul) {
            $kode = trim(mb_strtolower($modul->kode_materi ?? ''));
            $nama = trim(mb_strtolower($modul->nama_materi ?? ''));

            $found = $materis->first(function ($m) use ($kode) {
                return $kode !== '' && trim(mb_strtolower($m->kode_materi ?? '')) === $kode;
            }) ?? $materis->first(function ($m) use ($kode) {
                return $kode !== '' && trim(mb_strtolower($m->kode_alias ?? '')) === $kode;
            }) ?? $materis->first(function ($m) use ($nama) {
                return $nama !== '' && trim(mb_strtolower($m->nama_materi ?? '')) === $nama;
            }) ?? $materis->first(function ($m) use ($nama) {
                return $nama !== '' && trim(mb_strtolower($m->alias ?? '')) === $nama;
            });

            if ($found) {
                $matched++;
                if (!$dryRun) {
                    $modul->id_materi = $found->id;
                    $modul->save();
                }
            } else {
                $unmatched[] = [
                    'modul_id' => $modul->id,
                    'kode_materi' => $modul->kode_materi,
                    'nama_materi' => $modul->nama_materi,
                ];
            }
        }

        $this->info("Matched: {$matched}");
        $this->warn("Unmatched: " . count($unmatched));

        if (count($unmatched)) {
            $this->table(['modul_id', 'kode_materi', 'nama_materi'], $unmatched);
        }

        if ($dryRun) {
            $this->comment('Dry run only, belum ada perubahan disimpan.');
        }
    }
}