<?php

namespace Tests\Unit;

use App\Models\ActivityInstruktur;
use App\Services\KPI\Jabatan\EducationManagerKPIService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EducationKPIServiceTest extends TestCase
{
    private EducationManagerKPIService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat tabel activity_instrukturs di database testing (SQLite in-memory)
        Schema::dropIfExists('activity_instrukturs');
        Schema::create('activity_instrukturs', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('activity_type')->nullable();
            $table->string('activity')->nullable();
            $table->text('desc')->nullable();
            $table->string('doc')->nullable();
            $table->date('activity_date')->nullable();
            $table->string('status')->default('On Progres');
            $table->timestamp('on_progress_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('is_locked')->default(0);
            $table->string('id_rkm')->nullable();
            $table->timestamps();
        });

        $this->service = new EducationManagerKPIService();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('activity_instrukturs');
        parent::tearDown();
    }

    /**
     * Helper untuk membuat objek dummy itemDetail dengan detailTargetKPI.
     */
    private function createDummyItemDetail(?string $tahun = '2026', float|int|string|null $nilaiTarget = 10)
    {
        $detail = new \stdClass();
        $detail->detail_jangka = $tahun;
        $detail->nilai_target = $nilaiTarget;

        $itemDetail = new \stdClass();
        $itemDetail->detailTargetKPI = collect([$detail]);

        return $itemDetail;
    }

    /**
     * Test skenario normal tanpa filter personId (mengisi beberapa aktivitas di minggu berbeda).
     */
    public function test_calculate_peningkatan_knowledge_sharing_detail_normal_scenario(): void
    {
        $tahun = 2026;
        $itemDetail = $this->createDummyItemDetail((string)$tahun, 10);

        // Buat data aktivitas Sharing Knowledge pada 2 minggu berbeda
        ActivityInstruktur::create([
            'user_id' => 1,
            'activity_type' => 'Sharing Knowledge',
            'activity_date' => "$tahun-01-15", // Minggu ke-3
        ]);

        ActivityInstruktur::create([
            'user_id' => 2,
            'activity_type' => 'Sharing Knowledge',
            'activity_date' => "$tahun-01-20", // Minggu ke-4
        ]);

        $result = $this->service->calculatePeningkatanKnowledgeSharingDetail($itemDetail);

        $totalMinggu = Carbon::create($tahun, 1, 1)->weeksInYear;

        // Progress = 2 minggu terisi
        $this->assertEquals(2, $result['progress']);
        // Gap = progress (2) - nilai_target (10) = -8
        $this->assertEquals(-8, $result['gap']);
        // Pie Chart: above = 2, below = totalMinggu - 2
        $this->assertEquals(2, $result['pie_chart']['above']);
        $this->assertEquals($totalMinggu - 2, $result['pie_chart']['below']);
        // Chart & breakdown
        $this->assertArrayHasKey("$tahun-01", $result['monthly_data']);
        $this->assertEquals(2, $result['monthly_data']["$tahun-01"]);
        $this->assertEquals(2, $result['monthly_progress']["$tahun-01"]);
        $this->assertArrayHasKey("$tahun-01-15", $result['daily_breakdown_per_month']["$tahun-01"]);
        $this->assertArrayHasKey("$tahun-01-20", $result['daily_breakdown_per_month']["$tahun-01"]);
    }

    /**
     * Test skenario dengan filter personId.
     */
    public function test_calculate_peningkatan_knowledge_sharing_detail_with_person_id_filter(): void
    {
        $tahun = 2026;
        $itemDetail = $this->createDummyItemDetail((string)$tahun, 5);

        // Activity user 1
        ActivityInstruktur::create([
            'user_id' => 1,
            'activity_type' => 'Sharing Knowledge',
            'activity_date' => "$tahun-02-10",
        ]);

        // Activity user 2 (tidak boleh terhitung saat filter personId = 1)
        ActivityInstruktur::create([
            'user_id' => 2,
            'activity_type' => 'Sharing Knowledge',
            'activity_date' => "$tahun-02-15",
        ]);

        $resultUser1 = $this->service->calculatePeningkatanKnowledgeSharingDetail($itemDetail, 1);
        $resultUser2 = $this->service->calculatePeningkatanKnowledgeSharingDetail($itemDetail, 2);

        $this->assertEquals(1, $resultUser1['progress']);
        $this->assertArrayHasKey("$tahun-02-10", $resultUser1['daily_breakdown_per_month']["$tahun-02"]);
        $this->assertArrayNotHasKey("$tahun-02-15", $resultUser1['daily_breakdown_per_month']["$tahun-02"]);

        $this->assertEquals(1, $resultUser2['progress']);
        $this->assertArrayHasKey("$tahun-02-15", $resultUser2['daily_breakdown_per_month']["$tahun-02"]);
    }

    /**
     * Test skenario data kosong (tidak ada aktivitas Sharing Knowledge).
     */
    public function test_calculate_peningkatan_knowledge_sharing_detail_empty_data(): void
    {
        $tahun = 2026;
        $target = 10;
        $itemDetail = $this->createDummyItemDetail((string)$tahun, $target);

        $result = $this->service->calculatePeningkatanKnowledgeSharingDetail($itemDetail);

        $totalMinggu = Carbon::create($tahun, 1, 1)->weeksInYear;

        $this->assertEquals(0, $result['progress']);
        $this->assertEquals('-10', $result['gap']);
        $this->assertEquals(0, $result['pie_chart']['above']);
        $this->assertEquals($totalMinggu, $result['pie_chart']['below']);
        $this->assertEmpty($result['monthly_data']);
        $this->assertEmpty($result['daily_breakdown_per_month']);
        $this->assertEmpty($result['monthly_progress']);
        $this->assertEmpty($result['daily_progress_per_month']);
    }

    /**
     * Test skenario mengabaikan aktivitas yang bukan 'Sharing Knowledge'.
     */
    public function test_calculate_peningkatan_knowledge_sharing_detail_ignores_other_activity_types(): void
    {
        $tahun = 2026;
        $itemDetail = $this->createDummyItemDetail((string)$tahun, 5);

        ActivityInstruktur::create([
            'user_id' => 1,
            'activity_type' => 'Workshop', // Bukan Sharing Knowledge
            'activity_date' => "$tahun-03-01",
        ]);

        $result = $this->service->calculatePeningkatanKnowledgeSharingDetail($itemDetail);

        $this->assertEquals(0, $result['progress']);
        $this->assertEmpty($result['monthly_data']);
    }

    /**
     * Test skenario multiple aktivitas pada minggu dan tanggal yang sama.
     */
    public function test_calculate_peningkatan_knowledge_sharing_detail_multiple_activities_same_day(): void
    {
        $tahun = 2026;
        $itemDetail = $this->createDummyItemDetail((string)$tahun, 5);

        // 2 Aktivitas di tanggal yang sama (minggu yang sama)
        ActivityInstruktur::create([
            'user_id' => 1,
            'activity_type' => 'Sharing Knowledge',
            'activity_date' => "$tahun-04-10",
        ]);
        ActivityInstruktur::create([
            'user_id' => 1,
            'activity_type' => 'Sharing Knowledge',
            'activity_date' => "$tahun-04-10",
        ]);

        $result = $this->service->calculatePeningkatanKnowledgeSharingDetail($itemDetail);

        // Jumlah minggu terisi tetap 1
        $this->assertEquals(1, $result['progress']);
        // Sesi harian & bulanan terakumulasi menjadi 2
        $this->assertEquals(2, $result['monthly_data']["$tahun-04"]);
        $this->assertEquals(2, $result['daily_breakdown_per_month']["$tahun-04"]["$tahun-04-10"]);
    }

    /**
     * Test edge cases: detail target null / tidak valid.
     */
    public function test_calculate_peningkatan_knowledge_sharing_detail_invalid_target_details(): void
    {
        // 1. detailTargetKPI kosong / null
        $itemDetailNoDetail = new \stdClass();
        $itemDetailNoDetail->detailTargetKPI = collect([]);

        $res1 = $this->service->calculatePeningkatanKnowledgeSharingDetail($itemDetailNoDetail);
        $this->assertEquals(0, $res1['progress']);
        $this->assertEquals(0, $res1['gap']);

        // 2. detail_jangka null
        $itemDetailNoJangka = $this->createDummyItemDetail(null, 10);
        $res2 = $this->service->calculatePeningkatanKnowledgeSharingDetail($itemDetailNoJangka);
        $this->assertEquals(0, $res2['progress']);

        // 3. nilai_target non-numeric atau <= 0
        $itemDetailInvalidTarget = $this->createDummyItemDetail('2026', 0);
        $res3 = $this->service->calculatePeningkatanKnowledgeSharingDetail($itemDetailInvalidTarget);
        $this->assertEquals(0, $res3['progress']);

        // 4. tahun out of valid range (< 2000 atau > now()->year + 5)
        $itemDetailInvalidYear = $this->createDummyItemDetail('1999', 10);
        $res4 = $this->service->calculatePeningkatanKnowledgeSharingDetail($itemDetailInvalidYear);
        $this->assertEquals(0, $res4['progress']);
    }
}
