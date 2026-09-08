<?php

namespace Tests\Feature\Sales;

use App\Models\targetKPI;
use App\Services\KPI\Jabatan\SalesKPIService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SalesKPIServiceTest extends TestCase
{
    protected SalesKPIService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SalesKPIService;
    }

    public function test_sales_target_penjualan_returns_zero_without_target_detail(): void
    {
        $item = new targetKPI;
        $item->setRelation('detailTargetKPI', collect());

        $this->assertSame(0, $this->service->calculateTargetPenjualanTahunan($item, null));
    }

    public function test_sales_target_penjualan_detail_returns_default_response_for_invalid_target(): void
    {
        $item = new targetKPI;
        $item->setRelation('detailTargetKPI', collect());

        $result = $this->service->calculateTargetPenjualanTahunanDetail($item);

        $this->assertSame(0, $result['progress']);
        $this->assertSame([], $result['triwulan_data']);
        $this->assertNull($result['sales_performance']);
    }

    public function test_sales_acquisition_cost_returns_zero_without_target_detail(): void
    {
        $item = new targetKPI;
        $item->setRelation('detailTargetKPI', collect());

        $this->assertSame(0, $this->service->calculateBiayaAkuisisiClient($item, null));
    }

    public function test_sales_competency_improvement_returns_zero_without_target_detail(): void
    {
        Http::fake([
            '*' => Http::response(['data' => ['data' => []]], 200),
        ]);

        $item = new targetKPI;
        $item->setRelation('detailTargetKPI', collect());

        $this->assertSame(0, $this->service->calculatePeningkatanKemampuanKompetensiSales($item, null));
    }
}