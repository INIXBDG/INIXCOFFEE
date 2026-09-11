<?php

namespace Tests\Feature\Sales;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SalesFeatureAccessAndCrudTest extends TestCase
{
    public function test_sales_features_expose_expected_write_endpoints(): void
    {
        $expectedRoutes = [
            'store.peluang' => 'POST',
            'edit.peluang' => 'PUT',
            'delete.peluang' => 'PUT',
            'store.aktivitas.new' => 'POST',
            'update.aktivitas' => 'PUT',
            'delete.aktivitas' => 'DELETE',
            'index.target.store' => 'POST',
            'index.target.update' => 'PUT',
            'index.target.delete' => 'DELETE',
            'store.catatan.sales' => 'POST',
            'update.catatan.sales' => 'PUT',
            'delete.catatan.sales' => 'DELETE',
            'laporan.harian.store' => 'POST',
            'laporan.harian.update' => 'POST',
            'laporan.harian.delete' => 'DELETE',
            'paymantAdvance.store' => 'POST',
            'netSales.update' => 'PUT',
            'paymantAdvance.destroy' => 'DELETE',
            'leads.store' => 'POST',
            'leads.update_status' => 'POST',
            'leads.update_data' => 'POST',
            'store.contact' => 'POST',
            'update.contact' => 'PUT',
            'delete.contact' => 'DELETE',
            'store.pic' => 'POST',
            'pic.update' => 'PUT',
            'pic.delete' => 'DELETE',
            'crm.store.ketentuan' => 'POST',
            'crm.update.ketentuan' => 'PUT',
            'crm.delete.ketentuan' => 'DELETE',
            'crm.generate.word' => 'POST',
            'crm.store.deskripsi' => 'POST',
            'crm.update.deskripsi' => 'PUT',
            'crm.delete.deskripsi' => 'DELETE',
            'crm.approval.index' => 'GET',
            'komisiSales.index' => 'GET',
            'komisiSales.get' => 'GET',
        ];

        foreach ($expectedRoutes as $routeName => $method) {
            $this->assertTrue(Route::has($routeName), "Route {$routeName} tidak ditemukan.");
            $this->assertContains($method, Route::getRoutes()->getByName($routeName)->methods());
        }
    }

    public function test_sales_feature_routes_require_authenticated_access(): void
    {
        $routeNames = [
            'index.peluang',
            'index.aktivitas',
            'index.target',
            'laporan.harian',
            'paymantAdvance.index',
            'leads.index',
            'index.contact',
            'index.pic',
            'crm.checklist-rkm.index',
            'crm.index.penawaran',
            'crm.laporanPenjualan',
            'crm.approval.index',
            'komisiSales.index',
        ];

        foreach ($routeNames as $routeName) {
            $this->assertTrue(Route::has($routeName), "Route {$routeName} tidak ditemukan.");
            $this->assertContains('auth', Route::getRoutes()->getByName($routeName)->gatherMiddleware());
        }
    }

    public function test_sales_listing_and_filter_endpoints_are_available(): void
    {
        $listingRoutes = [
            'index.peluang',
            'index.peluang.json',
            'index.aktivitas',
            'index.aktivitas.json',
            'get.target',
            'getall.target',
            'laporan.harian',
            'jsonLaporan',
            'crm.approval.index',
            'komisiSales.get',
        ];

        foreach ($listingRoutes as $routeName) {
            $this->assertTrue(Route::has($routeName), "Listing/filter route {$routeName} tidak ditemukan.");
        }
    }

    public function test_sales_forms_have_validation_entry_points(): void
    {
        $controllers = [
            \App\Http\Controllers\Crm\PeluangController::class => ['store', 'update'],
            \App\Http\Controllers\Crm\AktivitasController::class => ['storeNew', 'update'],
            \App\Http\Controllers\Crm\CatatanSalesController::class => ['store'],
            \App\Http\Controllers\LaporanHarianSalesController::class => ['store', 'update'],
            \App\Http\Controllers\netSalesController::class => ['store', 'updateNetSales'],
            \App\Http\Controllers\Crm\ContactController::class => ['store', 'update'],
            \App\Http\Controllers\Crm\PicController::class => ['store', 'updatePIC'],
            \App\Http\Controllers\Crm\RegisFormController::class => ['generateWord'],
        ];

        foreach ($controllers as $controller => $methods) {
            foreach ($methods as $method) {
                $this->assertTrue(method_exists($controller, $method), "Method {$controller}@{$method} tidak ditemukan.");
            }
        }
    }
}