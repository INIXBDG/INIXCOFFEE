<?php

namespace Tests\Feature\Sales;

use App\Http\Controllers\Crm\ApprovalPendapatanSalesController;
use App\Models\ApprovalPendapatanSales;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ApprovalPendapatanTest extends TestCase
{
    public function test_sales_income_approval_exposes_controller_routes_and_view(): void
    {
        $this->assertTrue(class_exists(ApprovalPendapatanSalesController::class));
        $this->assertTrue(method_exists(ApprovalPendapatanSalesController::class, 'index'));
        $this->assertTrue(method_exists(ApprovalPendapatanSalesController::class, 'get'));
        $this->assertTrue(method_exists(ApprovalPendapatanSalesController::class, 'update'));
        $this->assertTrue(Route::has('crm.approval.index'));
        $this->assertTrue(View::exists('crm.approvalPendapatan.index'));
    }

    public function test_sales_income_approval_model_accepts_approval_attributes(): void
    {
        $approval = new ApprovalPendapatanSales([
            'id_rkm' => 25,
            'status' => 'valid',
            'tanggal_mulai' => '2026-09-03',
        ]);

        $this->assertSame(25, $approval->id_rkm);
        $this->assertSame('valid', $approval->status);
        $this->assertSame('2026-09-03', $approval->tanggal_mulai->format('Y-m-d'));
    }
}