<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rkms = App\Models\RKM::with(['analisisrkm.analisisrkmmingguan'])->where('status', '0')->whereYear('tanggal_awal', 2026)->whereMonth('tanggal_awal', 2)->get();
$diff = [];
foreach($rkms as $rkm) {
    if($rkm->analisisrkm) {
        $nett_rkm = (float) $rkm->analisisrkm->nett_penjualan;
        $nett_mingguan = 0;
        if($rkm->analisisrkm->analisisrkmmingguan) {
            foreach($rkm->analisisrkm->analisisrkmmingguan as $mingguan) {
                $nett_mingguan += (float) $mingguan->nett_penjualan;
            }
        }
        if($nett_rkm != $nett_mingguan) {
            $diff[] = [
                'rkm_id' => $rkm->id,
                'materi' => $rkm->materi_key,
                'nett_rkm' => $nett_rkm,
                'nett_mingguan' => $nett_mingguan
            ];
        }
    }
}
echo json_encode(['diff' => $diff]);
