<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rkms = App\Models\RKM::with('analisisrkm.analisisrkmmingguan')
    ->where('status', '0')
    ->whereYear('tanggal_awal', 2026)
    ->whereMonth('tanggal_awal', 2)
    ->get();

$weekFixCosts = [];

foreach($rkms as $rkm) {
    if($rkm->analisisrkm && $rkm->analisisrkm->analisisrkmmingguan) {
        foreach($rkm->analisisrkm->analisisrkmmingguan as $mingguan) {
            $weekNum = $mingguan->minggu;
            if(!isset($weekFixCosts[$weekNum]) && $mingguan->fixcost !== null) {
                $weekFixCosts[$weekNum] = floatval($mingguan->fixcost);
            }
        }
    }
}

echo json_encode(['weekFixCosts' => $weekFixCosts, 'total' => array_sum($weekFixCosts)]);
