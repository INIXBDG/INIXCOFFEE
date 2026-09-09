<?php

namespace Tests\Feature\EducationManager\PengembanganKurikulum;

use App\Models\DetailTargetKPI;
use App\Models\Materi;
use App\Models\targetKPI;
use App\Services\KPI\Jabatan\EducationManagerKPIService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PengembanganKurikulumEducationManagerTest extends TestCase
{
    use DatabaseTransactions;

    protected EducationManagerKPIService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EducationManagerKPIService();
        session(['last_activity' => time()]);
    }

    public function test_education_manager_can_calculate_kurikulum_pelatihan_progress(): void
    {
        Materi::create([
            'nama_materi' => 'Kurikulum AI 2026',
            'kode_materi' => 'KUR-AI-01',
            'status' => 'Aktif',
        ]);

        $item = new targetKPI();
        $detail = new DetailTargetKPI();
        $detail->detail_jangka = (string) now()->year;
        $detail->nilai_target = 12;
        $item->setRelation('detailTargetKPI', collect([$detail]));

        $progress = $this->service->calculatePengembanganKurikulumPelatihan($item, 1);

        $this->assertIsNumeric($progress);
        $this->assertGreaterThanOrEqual(1, $progress);
    }

    public function test_education_manager_can_calculate_kurikulum_pelatihan_detail(): void
    {
        $currentYear = now()->year;

        $materi1 = Materi::create([
            'nama_materi' => 'Kurikulum Frontend NextJS',
            'kode_materi' => 'KUR-FE-01',
            'status' => 'Aktif',
        ]);
        $materi1->created_at = Carbon::create($currentYear, 1, 15, 10, 0, 0);
        $materi1->save();

        $materi2 = Materi::create([
            'nama_materi' => 'Kurikulum Backend Go',
            'kode_materi' => 'KUR-BE-01',
            'status' => 'Aktif',
        ]);
        $materi2->created_at = Carbon::create($currentYear, 2, 15, 10, 0, 0);
        $materi2->save();

        $itemDetail = new targetKPI();
        $detail = new DetailTargetKPI();
        $detail->detail_jangka = (string) $currentYear;
        $detail->nilai_target = 12;
        $itemDetail->setRelation('detailTargetKPI', collect([$detail]));

        $result = $this->service->calculatePengembanganKurikulumPelatihanDetail($itemDetail);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('progress', $result);
        $this->assertArrayHasKey('gap', $result);
        $this->assertArrayHasKey('pie_chart', $result);
        $this->assertArrayHasKey('monthly_data', $result);
        $this->assertGreaterThanOrEqual(2, $result['progress']);
        $this->assertGreaterThanOrEqual(2, $result['pie_chart']['above']);
        $this->assertLessThanOrEqual(10, $result['pie_chart']['below']);
    }

    public function test_kurikulum_pelatihan_returns_zero_when_no_detail(): void
    {
        $item = new targetKPI();
        $item->setRelation('detailTargetKPI', collect());

        $progress = $this->service->calculatePengembanganKurikulumPelatihan($item, 1);
        $this->assertEquals(0, $progress);

        $detailResult = $this->service->calculatePengembanganKurikulumPelatihanDetail($item);
        $this->assertEquals(0, $detailResult['progress']);
    }
}
