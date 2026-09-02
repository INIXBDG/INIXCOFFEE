<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $jurnals = App\Models\JurnalAkuntansi::whereNotNull('id_surat_perjalanan')->get(); 
    $count = 0;
    foreach($jurnals as $j) { 
        if ($j->suratPerjalanan && $j->suratPerjalanan->tanggal_berangkat) { 
            $j->tanggal_transaksi = $j->suratPerjalanan->tanggal_berangkat; 
            $j->save(); 
            $count++;
        } 
    } 
    echo 'Successfully updated ' . $count . ' existing SPJ journal records to use their departure dates.';
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
