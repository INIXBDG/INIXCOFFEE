<?php

namespace Tests\Feature\Sales;

use App\Http\Controllers\netSalesController;
use App\Models\RKM;
use App\Models\perhitunganNetSales;
use App\Models\trackingNetSales;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class NetSalesTest extends TestCase
{
    public function test_net_sales_feature_exposes_controller_routes_and_views(): void
    {
        $this->assertTrue(class_exists(netSalesController::class));
        $this->assertTrue(method_exists(netSalesController::class, 'index'));
        $this->assertTrue(method_exists(netSalesController::class, 'store'));
        $this->assertTrue(method_exists(netSalesController::class, 'updateNetSales'));
        $this->assertTrue(Route::has('paymantAdvance.index'));
        $this->assertTrue(Route::has('paymantAdvance.store'));
        $this->assertTrue(Route::has('netSales.update'));
        $this->assertTrue(Route::has('netsales.detail'));
        $this->assertTrue(View::exists('netSales.index'));
        $this->assertTrue(View::exists('netSales.create'));
    }

    public function test_net_sales_accepts_cost_components_and_casts_them_to_integers(): void
    {
        $netSales = new perhitunganNetSales([
            'id_rkm' => 25,
            'transportasi' => '1000000',
            'akomodasi_peserta' => '2000000',
            'fresh_money' => '500000',
            'cashback' => '250000',
            'tipe_pembayaran' => 'Transfer',
        ]);

        $this->assertSame(1000000, $netSales->transportasi);
        $this->assertSame(2000000, $netSales->akomodasi_peserta);
        $this->assertSame(500000, $netSales->fresh_money);
        $this->assertSame('Transfer', $netSales->tipe_pembayaran);
    }

    public function test_net_sales_defines_rkm_and_tracking_relations(): void
    {
        $netSales = new perhitunganNetSales;

        $this->assertInstanceOf(BelongsTo::class, $netSales->rkm());
        $this->assertSame(RKM::class, $netSales->rkm()->getRelated()::class);
        $this->assertSame('id_rkm', $netSales->rkm()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $netSales->trackingNetSales());
        $this->assertSame(trackingNetSales::class, $netSales->trackingNetSales()->getRelated()::class);
        $this->assertSame('id_tracking', $netSales->trackingNetSales()->getForeignKeyName());
    }

    public function test_tracking_net_sales_has_many_calculations(): void
    {
        $tracking = new trackingNetSales(['id_rkm' => 25, 'tracking' => 'Submitted']);

        $this->assertSame('Submitted', $tracking->tracking);
        $this->assertInstanceOf(HasMany::class, $tracking->perhitunganNetSales());
        $this->assertSame(perhitunganNetSales::class, $tracking->perhitunganNetSales()->getRelated()::class);
        $this->assertSame('id_tracking', $tracking->perhitunganNetSales()->getForeignKeyName());
    }
}