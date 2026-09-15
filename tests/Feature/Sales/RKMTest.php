<?php

namespace Tests\Feature\Sales;

use App\Http\Controllers\RKMController;
use App\Models\Peluang;
use App\Models\RKM;
use App\Models\perhitunganNetSales;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class RKMTest extends TestCase
{
    public function test_rkm_feature_exposes_controller_crud_routes_and_views(): void
    {
        $this->assertTrue(class_exists(RKMController::class));
        $this->assertTrue(method_exists(RKMController::class, 'index'));
        $this->assertTrue(method_exists(RKMController::class, 'store'));
        $this->assertTrue(method_exists(RKMController::class, 'update'));
        $this->assertTrue(method_exists(RKMController::class, 'destroy'));
        $this->assertTrue(Route::has('rkm.index'));
        $this->assertTrue(Route::has('rkm.store'));
        $this->assertTrue(Route::has('rkm.update'));
        $this->assertTrue(Route::has('rkm.destroy'));
        $this->assertTrue(View::exists('rkm.index'));
        $this->assertTrue(View::exists('rkm.tambahrkm'));
    }

    public function test_rkm_accepts_sales_training_data(): void
    {
        $rkm = new RKM([
            'sales_key' => 'SLS001',
            'materi_key' => 4,
            'perusahaan_key' => 10,
            'harga_jual' => '25000000',
            'pax' => 20,
            'tanggal_awal' => '2026-09-03',
            'tanggal_akhir' => '2026-09-04',
            'metode_kelas' => 'In House',
            'event' => 'Leadership Training',
            'status' => '0',
        ]);

        $this->assertSame('2026-09-03', $rkm->tanggal_awal->format('Y-m-d'));
        $this->assertSame('2026-09-04', $rkm->tanggal_akhir->format('Y-m-d'));
        $this->assertSame('SLS001', $rkm->sales_key);
        $this->assertSame('Leadership Training', $rkm->event);
    }

    public function test_rkm_defines_sales_pipeline_and_net_sales_relations(): void
    {
        $rkm = new RKM;

        $this->assertInstanceOf(HasMany::class, $rkm->perhitunganNetSales());
        $this->assertSame(perhitunganNetSales::class, $rkm->perhitunganNetSales()->getRelated()::class);
        $this->assertSame('id_rkm', $rkm->perhitunganNetSales()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $rkm->sales());
        $this->assertSame('sales_key', $rkm->sales()->getForeignKeyName());
        $this->assertSame('kode_karyawan', $rkm->sales()->getOwnerKeyName());

        $this->assertInstanceOf(HasOne::class, $rkm->peluang());
        $this->assertSame(Peluang::class, $rkm->peluang()->getRelated()::class);
    }
}