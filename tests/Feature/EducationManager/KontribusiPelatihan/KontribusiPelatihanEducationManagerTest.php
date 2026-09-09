<?php

namespace Tests\Feature\EducationManager\KontribusiPelatihan;

use App\Models\DetailTargetKPI;
use App\Models\RKM;
use App\Models\targetKPI;
use App\Services\KPI\Jabatan\EducationManagerKPIService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KontribusiPelatihanEducationManagerTest extends TestCase
{
    use DatabaseTransactions;

    protected EducationManagerKPIService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EducationManagerKPIService();
        session(['last_activity' => time()]);
    }

    public function test_education_manager_can_calculate_kontribusi_pelatihan(): void
    {
        RKM::create([
            'sales_key' => 'SALES01',
            'perusahaan_key' => 1,
            'materi_key' => 1,
            'harga_jual' => 1000000,
            'pax' => 10,
            'isi_pax' => '10',
            'tanggal_awal' => now()->startOfYear()->format('Y-m-d'),
            'tanggal_akhir' => now()->startOfYear()->addDays(3)->format('Y-m-d'),
            'instruktur_key' => 'INS01',
            'status' => '0',
        ]);

        $item = new targetKPI();
        $detail = new DetailTargetKPI();
        $detail->detail_jangka = (string) now()->year;
        $item->setRelation('detailTargetKPI', collect([$detail]));

        $progress = $this->service->calculatePeningkatanKontribusiPelatihan($item);

        $this->assertIsNumeric($progress);
        $this->assertGreaterThanOrEqual(0, $progress);
    }

    public function test_education_manager_can_calculate_kontribusi_pelatihan_detail(): void
    {
        // Internal instructor RKM
        RKM::create([
            'sales_key' => 'SALES01',
            'perusahaan_key' => 1,
            'materi_key' => 1,
            'harga_jual' => 1000000,
            'pax' => 10,
            'isi_pax' => '10',
            'tanggal_awal' => now()->startOfYear()->format('Y-m-d'),
            'tanggal_akhir' => now()->startOfYear()->addDays(3)->format('Y-m-d'),
            'instruktur_key' => 'INS_INTERNAL',
            'status' => '0',
        ]);

        // Freelance instructor RKM
        RKM::create([
            'sales_key' => 'SALES02',
            'perusahaan_key' => 1,
            'materi_key' => 2,
            'harga_jual' => 2000000,
            'pax' => 5,
            'isi_pax' => '5',
            'tanggal_awal' => now()->startOfYear()->addDays(5)->format('Y-m-d'),
            'tanggal_akhir' => now()->startOfYear()->addDays(7)->format('Y-m-d'),
            'instruktur_key' => 'OL',
            'status' => '0',
        ]);

        $itemDetail = new targetKPI();
        $detail = new DetailTargetKPI();
        $detail->detail_jangka = (string) now()->year;
        $itemDetail->setRelation('detailTargetKPI', collect([$detail]));

        $result = $this->service->calculatePeningkatanKontribusiPelatihanDetail($itemDetail);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('class_breakdown', $result);
        $this->assertGreaterThanOrEqual(1, $result['class_breakdown']['internal']);
        $this->assertGreaterThanOrEqual(1, $result['class_breakdown']['freelance']);
    }
}
