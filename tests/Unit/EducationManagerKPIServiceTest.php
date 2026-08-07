<?php

namespace Tests\Unit;

use App\Models\DetailTargetKPI;
use App\Models\targetKPI;
use App\Services\KPI\Jabatan\EducationManagerKPIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EducationManagerKPIServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EducationManagerKPIService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EducationManagerKPIService;
    }

    public function test_calculate_pengembangan_kurikulum_pelatihan_returns_zero_when_no_detail()
    {
        $item = new targetKPI;
        $item->setRelation('detailTargetKPI', collect());

        $result = $this->service->calculatePengembanganKurikulumPelatihan($item, 1);

        $this->assertEquals(0, $result);
    }

    public function test_calculate_pengembangan_kurikulum_pelatihan_returns_zero_on_invalid_year()
    {
        $detail = new DetailTargetKPI;
        $detail->detail_jangka = '1990';

        $item = new targetKPI;
        $item->setRelation('detailTargetKPI', collect([$detail]));

        $result = $this->service->calculatePengembanganKurikulumPelatihan($item, 1);

        $this->assertEquals(0, $result);
    }
}
