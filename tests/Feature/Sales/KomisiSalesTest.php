<?php

namespace Tests\Feature\Sales;

use App\Http\Controllers\KomisiSalesController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class KomisiSalesTest extends TestCase
{
    public function test_sales_commission_feature_exposes_controller_routes(): void
    {
        $this->assertTrue(class_exists(KomisiSalesController::class));
        $this->assertTrue(method_exists(KomisiSalesController::class, 'index'));
        $this->assertTrue(method_exists(KomisiSalesController::class, 'get'));
        $this->assertTrue(method_exists(KomisiSalesController::class, 'exportPdf'));
        $this->assertTrue(method_exists(KomisiSalesController::class, 'checkLockStatus'));
        $this->assertTrue(method_exists(KomisiSalesController::class, 'unlock'));
        $this->assertTrue(Route::has('komisiSales.index'));
        $this->assertTrue(Route::has('komisiSales.get'));
        $this->assertTrue(Route::has('komisiSales.export-pdf'));
    }
}