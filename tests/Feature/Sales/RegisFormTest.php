<?php

namespace Tests\Feature\Sales;

use App\Http\Controllers\Crm\RegisFormController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class RegisFormTest extends TestCase
{
    public function test_sales_registration_and_offer_feature_exposes_controller_routes_and_views(): void
    {
        $this->assertTrue(class_exists(RegisFormController::class));
        $this->assertTrue(method_exists(RegisFormController::class, 'index'));
        $this->assertTrue(method_exists(RegisFormController::class, 'indexPenawaran'));
        $this->assertTrue(method_exists(RegisFormController::class, 'generateWord'));
        $this->assertTrue(method_exists(RegisFormController::class, 'storeProspectAktivitas'));
        $this->assertTrue(Route::has('crm.index.regis'));
        $this->assertTrue(Route::has('crm.index.penawaran'));
        $this->assertTrue(Route::has('crm.generate.word'));
        $this->assertTrue(Route::has('crm.store.prospect.penawaran'));
        $this->assertTrue(View::exists('crm.regisform.regis'));
        $this->assertTrue(View::exists('crm.regisform.penawaran'));
    }
}