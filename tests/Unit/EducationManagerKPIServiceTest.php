<?php

namespace Tests\Unit;

use App\Models\DetailTargetKPI;
use App\Models\targetKPI;
use App\Services\KPI\Jabatan\EducationManagerKPIService;
use Tests\TestCase;

class EducationManagerKPIServiceTest extends TestCase
{
    protected EducationManagerKPIService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EducationManagerKPIService;
    }

    /**
     * Test calculatePengembanganKurikulumPelatihan
     */
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

    /**
     * Test calculatePengembanganKurikulumPelatihanDetail
     */
    public function test_calculate_pengembangan_kurikulum_pelatihan_detail_returns_default_structure_when_no_detail()
    {
        $itemDetail = new targetKPI;
        $itemDetail->setRelation('detailTargetKPI', collect());

        $result = $this->service->calculatePengembanganKurikulumPelatihanDetail($itemDetail);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('progress', $result);
    }

    /**
     * Test calculatePeningkatanKnowledgeSharing
     */
    public function test_calculate_peningkatan_knowledge_sharing_returns_zero_when_no_detail()
    {
        $item = new targetKPI;
        $item->setRelation('detailTargetKPI', collect());

        $result = $this->service->calculatePeningkatanKnowledgeSharing($item, 1);

        $this->assertEquals(0, $result);
    }

    public function test_calculate_peningkatan_knowledge_sharing_detail_returns_default_structure_when_no_detail()
    {
        $itemDetail = new targetKPI;
        $itemDetail->setRelation('detailTargetKPI', collect());

        $result = $this->service->calculatePeningkatanKnowledgeSharingDetail($itemDetail, 1);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('progress', $result);
    }

    /**
     * Test calculatePeningkatanKontribusiPelatihan
     */
    public function test_calculate_peningkatan_kontribusi_pelatihan_returns_zero_when_no_detail()
    {
        $item = new targetKPI;
        $item->setRelation('detailTargetKPI', collect());

        $result = $this->service->calculatePeningkatanKontribusiPelatihan($item);

        $this->assertEquals(0, $result);
    }

    public function test_calculate_peningkatan_kontribusi_pelatihan_detail_returns_default_structure_when_no_detail()
    {
        $itemDetail = new targetKPI;
        $itemDetail->setRelation('detailTargetKPI', collect());

        $result = $this->service->calculatePeningkatanKontribusiPelatihanDetail($itemDetail);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('progress', $result);
    }

    /**
     * Test calculateEvaluasiKinerjaInstruktur
     */
    public function test_calculate_evaluasi_kinerja_instruktur_returns_zero_when_no_detail()
    {
        $item = new targetKPI;
        $item->setRelation('detailTargetKPI', collect());

        $result = $this->service->calculateEvaluasiKinerjaInstruktur($item, 1);

        $this->assertEquals(0, $result);
    }

    public function test_calculate_evaluasi_kinerja_instruktur_detail_returns_default_structure_when_no_detail()
    {
        $itemDetail = new targetKPI;
        $itemDetail->setRelation('detailTargetKPI', collect());

        $result = $this->service->calculateEvaluasiKinerjaInstrukturDetail($itemDetail, 1);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('progress', $result);
    }

    /**
     * Test calculatePembuatanArtikel
     */
    public function test_calculate_pembuatan_artikel_returns_zero_when_no_detail()
    {
        $item = new targetKPI;
        $item->setRelation('detailTargetKPI', collect());

        $result = $this->service->calculatePembuatanArtikel($item, 1);

        $this->assertEquals(0, $result);
    }

    public function test_calculate_pembuatan_artikel_detail_returns_default_structure_when_no_detail()
    {
        $itemDetail = new targetKPI;
        $itemDetail->setRelation('detailTargetKPI', collect());

        $result = $this->service->calculatePembuatanArtikelDetail($itemDetail);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('progress', $result);
    }
}
