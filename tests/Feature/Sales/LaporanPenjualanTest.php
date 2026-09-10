<?php

namespace Tests\Feature\Sales;

use App\Http\Controllers\Crm\LaporanPenjualanController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class LaporanPenjualanTest extends TestCase
{
    public function test_sales_report_feature_exposes_controller_routes_and_views(): void
    {
        $this->assertTrue(class_exists(LaporanPenjualanController::class));
        $this->assertTrue(method_exists(LaporanPenjualanController::class, 'index'));
        $this->assertTrue(method_exists(LaporanPenjualanController::class, 'indexJson'));
        $this->assertTrue(method_exists(LaporanPenjualanController::class, 'downloadWinExcel'));
        $this->assertTrue(method_exists(LaporanPenjualanController::class, 'downloadLostExcel'));
        $this->assertTrue(Route::has('crm.laporanPenjualan'));
        $this->assertTrue(Route::has('jsonLaporan'));
        $this->assertTrue(Route::has('laporan.win.excel'));
        $this->assertTrue(Route::has('laporan.lost.excel'));
        $this->assertTrue(Route::has('laporan.win.pdf'));
        $this->assertTrue(Route::has('laporan.lost.pdf'));
        $this->assertTrue(View::exists('crm.LaporanPenjualan.index'));
        $this->assertTrue(View::exists('crm.LaporanPenjualan.laporan_for_gm'));
    }
}