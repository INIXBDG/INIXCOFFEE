<?php

namespace Tests\Unit;

use App\Models\DetailTargetKPI;
use App\Models\targetKPI;
use App\Services\KPI\Jabatan\EducationManagerKPIService;
use Tests\TestCase;

class EducationKPIServiceTest extends TestCase
{
    protected EducationManagerKPIService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EducationManagerKPIService;
    }

    /**
     * Skenario Data Kosong: Tidak ada detail target KPI.
     */
    public function test_calculate_peningkatan_knowledge_sharing_detail_returns_empty_response_when_no_detail()
    {
        $itemDetail = new targetKPI;
        $itemDetail->setRelation('detailTargetKPI', collect());

        $result = $this->service->calculatePeningkatanKnowledgeSharingDetail($itemDetail);

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['progress']);
        $this->assertEquals(0, $result['gap']);
    }

    /**
     * Edge Case: detail_jangka bernilai null/kosong.
     */
    public function test_calculate_peningkatan_knowledge_sharing_detail_returns_empty_response_when_detail_jangka_missing()
    {
        $detail = new DetailTargetKPI;
        $detail->detail_jangka = null;
        $detail->nilai_target = 100;

        $itemDetail = new targetKPI;
        $itemDetail->setRelation('detailTargetKPI', collect([$detail]));

        $result = $this->service->calculatePeningkatanKnowledgeSharingDetail($itemDetail);

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['progress']);
    }

    /**
     * Edge Case: nilai_target tidak valid (<= 0 atau bukan angka).
     */
    public function test_calculate_peningkatan_knowledge_sharing_detail_returns_empty_response_when_nilai_target_invalid()
    {
        $detail = new DetailTargetKPI;
        $detail->detail_jangka = '2026';
        $detail->nilai_target = 0;

        $itemDetail = new targetKPI;
        $itemDetail->setRelation('detailTargetKPI', collect([$detail]));

        $result = $this->service->calculatePeningkatanKnowledgeSharingDetail($itemDetail);

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['progress']);
    }

    /**
     * Edge Case: Tahun/detail_jangka di luar rentang valid (< 2000 atau jauh di masa depan).
     */
    public function test_calculate_peningkatan_knowledge_sharing_detail_returns_empty_response_when_tahun_invalid()
    {
        $detail = new DetailTargetKPI;
        $detail->detail_jangka = '1995';
        $detail->nilai_target = 50;

        $itemDetail = new targetKPI;
        $itemDetail->setRelation('detailTargetKPI', collect([$detail]));

        $result = $this->service->calculatePeningkatanKnowledgeSharingDetail($itemDetail);

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['progress']);
    }

    /**
     * Skenario Data Kosong: Tahun valid tetapi tidak ada data aktivitas sharing knowledge.
     */
    public function test_calculate_peningkatan_knowledge_sharing_detail_returns_zero_progress_when_no_activities()
    {
        $detail = new DetailTargetKPI;
        $detail->detail_jangka = '2026';
        $detail->nilai_target = 10;

        $itemDetail = new targetKPI;
        $itemDetail->setRelation('detailTargetKPI', collect([$detail]));

        $result = $this->service->calculatePeningkatanKnowledgeSharingDetail($itemDetail);

        $this->assertIsArray($result);
        $this->assertEquals(0, $result['progress']);
        $this->assertArrayHasKey('pie_chart', $result);
    }
}
