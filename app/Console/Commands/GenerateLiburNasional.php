<?php

namespace App\Console\Commands;

use App\Models\HariLibur;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GenerateLiburNasional extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-libur-nasional';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = Carbon::now()->year;

        $response = Http::get("https://libur.deno.dev/api", [
            'year' => $year
        ]);

        $data = $response->json();

        foreach ($data as $item) {
            HariLibur::updateOrCreate(
                ['tanggal' => $item['date']],
                [
                    'nama' => $item['name'],
                    'year' => $year,
                    'tipe' => 'nasional'
                ]
            );
        }
        
        $this->info("Generating national holidays for the year $year...");
    }
}
