<?php

namespace App\Console\Commands;

use App\Models\Peluang;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckTahapPeluang extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'peluang:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check peluang status and update to lost';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $peluang = Peluang::with('rkm')->whereIn('tahap', ['biru', 'hitam'])
            ->get();

        foreach ($peluang as $item) {
            // Cek sudah melewati periode mulai atau belom
            if ($item->periode_mulai && $now->greaterThanOrEqualTo($item->periode_mulai)) {
                $item->update([
                    'tahap' => 'lost',
                    'lost' => Carbon::now()->format('Y-m-d'),
                    'deleted_at' => $now,
                    'deleted_by' => null,
                ]);

                if ($item->rkm) {
                    $item->rkm->update([
                        'deleted_at' => $now,
                        'deleted_by' => null,
                    ]);
                }

                continue;
            }

            // Kalau tentatif, cek apakah sudah lebih dari 3 bulan sejak dibuat
            if ($item->tentatif == 1) {
                $created = Carbon::parse($item->created_at);

                if ($now->greaterThanOrEqualTo($created->addMonths(3))) {
                    $item->update([
                        'tahap' => 'lost',
                        'lost' => Carbon::now()->format('Y-m-d'),
                        'deleted_at' => $now,
                        'deleted_by' => null,
                    ]);

                    if ($item->rkm) {
                        $item->rkm->update([
                            'deleted_at' => $now,
                            'deleted_by' => null,
                        ]);
                    }
                }
            }
        }

        $this->info("Check peluang selesai");
    }
}
