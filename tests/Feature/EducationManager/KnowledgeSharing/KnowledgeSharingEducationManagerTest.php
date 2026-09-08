<?php

namespace Tests\Feature\EducationManager\KnowledgeSharing;

use App\Models\ActivityInstruktur;
use App\Models\DataTarget;
use App\Models\DetailTargetKPI;
use App\Models\targetKPI;
use App\Services\KPI\Jabatan\EducationManagerKPIService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeSharingEducationManagerTest extends TestCase
{
    use RefreshDatabase;

    protected EducationManagerKPIService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EducationManagerKPIService();
        session(['last_activity' => time()]);
    }

    public function test_education_manager_can_calculate_knowledge_sharing_progress(): void
    {
        $currentYear = now()->year;

        ActivityInstruktur::create([
            'user_id' => '1',
            'activity_type' => 'Sharing Knowledge',
            'activity_date' => Carbon::create($currentYear, 1, 15)->format('Y-m-d'),
        ]);

        $item = new targetKPI();
        $detail = new DetailTargetKPI();
        $detail->detail_jangka = (string) $currentYear;
        $detail->setRelation('dataTarget', new DataTarget(['nilai_target' => 10]));
        $item->setRelation('detailTargetKPI', collect([$detail]));

        $progress = $this->service->calculatePeningkatanKnowledgeSharing($item, '1');

        $this->assertIsNumeric($progress);
        $this->assertEquals(1, $progress);
    }

    public function test_education_manager_can_calculate_knowledge_sharing_detail(): void
    {
        $currentYear = now()->year;

        ActivityInstruktur::create([
            'user_id' => '1',
            'activity_type' => 'Sharing Knowledge',
            'activity_date' => Carbon::create($currentYear, 1, 15)->format('Y-m-d'),
        ]);

        ActivityInstruktur::create([
            'user_id' => '1',
            'activity_type' => 'Sharing Knowledge',
            'activity_date' => Carbon::create($currentYear, 2, 15)->format('Y-m-d'),
        ]);

        $itemDetail = new targetKPI();
        $detail = new DetailTargetKPI();
        $detail->detail_jangka = (string) $currentYear;
        $detail->setRelation('dataTarget', new DataTarget(['nilai_target' => 10]));
        $itemDetail->setRelation('detailTargetKPI', collect([$detail]));

        $result = $this->service->calculatePeningkatanKnowledgeSharingDetail($itemDetail);

        $this->assertIsArray($result);
        $this->assertEquals(2, $result['progress']);
        $this->assertArrayHasKey('pie_chart', $result);
        $this->assertEquals(2, $result['pie_chart']['above']);
    }

    public function test_knowledge_sharing_filters_by_person_id(): void
    {
        $currentYear = now()->year;

        // Instruktur 1 activity
        ActivityInstruktur::create([
            'user_id' => '1',
            'activity_type' => 'Sharing Knowledge',
            'activity_date' => Carbon::create($currentYear, 1, 15)->format('Y-m-d'),
        ]);

        // Instruktur 2 activity
        ActivityInstruktur::create([
            'user_id' => '2',
            'activity_type' => 'Sharing Knowledge',
            'activity_date' => Carbon::create($currentYear, 1, 15)->format('Y-m-d'),
        ]);

        $item = new targetKPI();
        $detail = new DetailTargetKPI();
        $detail->detail_jangka = (string) $currentYear;
        $detail->setRelation('dataTarget', new DataTarget(['nilai_target' => 10]));
        $item->setRelation('detailTargetKPI', collect([$detail]));

        $progressUser1 = $this->service->calculatePeningkatanKnowledgeSharing($item, '1');
        $progressUser2 = $this->service->calculatePeningkatanKnowledgeSharing($item, '2');

        $this->assertEquals(1, $progressUser1);
        $this->assertEquals(1, $progressUser2);
    }
}
