<?php

namespace Tests\Feature\Sales;

use App\Http\Controllers\Crm\CRMController;
use App\Http\Controllers\Crm\salesPribadiController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class DashboardSalesTest extends TestCase
{
    public function test_sales_dashboards_expose_controllers_routes_and_views(): void
    {
        $this->assertTrue(class_exists(CRMController::class));
        $this->assertTrue(class_exists(salesPribadiController::class));
        $this->assertTrue(method_exists(CRMController::class, 'index'));
        $this->assertTrue(method_exists(CRMController::class, 'chartRKM'));
        $this->assertTrue(method_exists(CRMController::class, 'apiProspekMingguan'));
        $this->assertTrue(method_exists(salesPribadiController::class, 'index'));
        $this->assertTrue(Route::has('CRM.index'));
        $this->assertTrue(Route::has('CRM.myDasboard'));
        $this->assertTrue(Route::has('chartRKM'));
        $this->assertTrue(Route::has('crm.api.prospek'));
        $this->assertTrue(View::exists('crm.dashboard'));
        $this->assertTrue(View::exists('crm.myDashboard'));
    }
}