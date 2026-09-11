<?php

namespace Tests\Feature\Sales;

use App\Http\Controllers\netSalesController;
use App\Models\perhitunganNetSales;
use App\Models\trackingNetSales;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaymentAdvanceTest extends TestCase
{
    public function test_payment_advance_exposes_post_put_and_delete_routes(): void
    {
        $this->assertTrue(Route::has('paymantAdvance.store'));
        $this->assertTrue(Route::has('netSales.update'));
        $this->assertTrue(Route::has('paymantAdvance.destroy'));

        $this->assertContains('POST', Route::getRoutes()->getByName('paymantAdvance.store')->methods());
        $this->assertContains('PUT', Route::getRoutes()->getByName('netSales.update')->methods());
        $this->assertContains('DELETE', Route::getRoutes()->getByName('paymantAdvance.destroy')->methods());
    }

    public function test_payment_advance_controller_requires_authentication(): void
    {
        $route = Route::getRoutes()->getByName('paymantAdvance.index');

        $this->assertContains('auth', $route->gatherMiddleware());
    }

    public function test_payment_advance_store_validates_required_form_fields(): void
    {
        $this->expectException(ValidationException::class);

        (new netSalesController)->store(Request::create('/paymantAdvance', 'POST', []));
    }

    public function test_payment_advance_store_rejects_invalid_payment_type(): void
    {
        $this->expectException(ValidationException::class);

        (new netSalesController)->store(Request::create('/paymantAdvance', 'POST', [
            'id_rkm' => 1,
            'tgl_pa' => '2026-09-03',
            'tipe_pembayaran' => 'Credit Card',
        ]));
    }

    public function test_payment_advance_feature_exposes_controller_views_and_update_action(): void
    {
        $this->assertTrue(class_exists(netSalesController::class));
        $this->assertTrue(method_exists(netSalesController::class, 'store'));
        $this->assertTrue(method_exists(netSalesController::class, 'updateNetSales'));
        $this->assertTrue(View::exists('netSales.index'));
        $this->assertTrue(View::exists('netSales.create'));
        $this->assertTrue(Route::has('netsales.detail'));
        $this->assertTrue(Route::has('netSales.edit.get'));
    }

    public function test_payment_advance_models_define_tracking_and_rkm_relations(): void
    {
        $netSales = new perhitunganNetSales;
        $tracking = new trackingNetSales;

        $this->assertInstanceOf(BelongsTo::class, $netSales->rkm());
        $this->assertInstanceOf(BelongsTo::class, $netSales->trackingNetSales());
        $this->assertSame('id_tracking', $netSales->trackingNetSales()->getForeignKeyName());
        $this->assertInstanceOf(HasMany::class, $tracking->perhitunganNetSales());
        $this->assertSame('id_tracking', $tracking->perhitunganNetSales()->getForeignKeyName());
    }

    public function test_sales_payment_advance_listing_route_is_available_for_sales_filtering(): void
    {
        $route = Route::getRoutes()->getByName('paymantAdvance.index');

        $this->assertSame('paymantAdvance', $route->uri());
        $this->assertContains('GET', $route->methods());
        $this->assertContains('auth', $route->gatherMiddleware());
    }
}