<?php

namespace Tests\Feature\Sales;

use App\Http\Controllers\Crm\CatatanSalesController;
use App\Models\Aktivitas;
use App\Models\CatatanSales;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CatatanSalesTest extends TestCase
{
    public function test_catatan_sales_feature_exposes_controller_crud_routes(): void
    {
        $this->assertTrue(class_exists(CatatanSalesController::class));
        $this->assertTrue(method_exists(CatatanSalesController::class, 'store'));
        $this->assertTrue(method_exists(CatatanSalesController::class, 'update'));
        $this->assertTrue(method_exists(CatatanSalesController::class, 'delete'));
        $this->assertTrue(Route::has('store.catatan.sales'));
        $this->assertTrue(Route::has('update.catatan.sales'));
        $this->assertTrue(Route::has('delete.catatan.sales'));
    }

    public function test_catatan_sales_accepts_activity_note_attributes(): void
    {
        $attributes = [
            'id_aktivitas' => 15,
            'id_sales' => 'SLS001',
            'catatan' => 'Client meminta revisi jadwal pelatihan.',
        ];

        $catatan = new CatatanSales($attributes);

        $this->assertSame($attributes, $catatan->only(array_keys($attributes)));
    }

    public function test_catatan_sales_belongs_to_sales_activity(): void
    {
        $catatan = new CatatanSales;

        $this->assertInstanceOf(BelongsTo::class, $catatan->aktivitas());
        $this->assertSame(Aktivitas::class, $catatan->aktivitas()->getRelated()::class);
        $this->assertSame('id_aktivitas', $catatan->aktivitas()->getForeignKeyName());
        $this->assertSame('id', $catatan->aktivitas()->getOwnerKeyName());
    }
}