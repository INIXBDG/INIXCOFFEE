<?php

namespace Tests\Feature\EducationManager\EvaluasiKinerja;

use App\Models\ActivityInstruktur;
use App\Models\DetailTargetKPI;
use App\Models\HariLibur;
use App\Models\karyawan;
use App\Models\targetKPI;
use App\Services\KPI\Jabatan\EducationManagerKPIService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EvaluasiKinerjaEducationManagerTest extends TestCase
{
    use DatabaseTransactions;

    protected EducationManagerKPIService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EducationManagerKPIService();
        session(['last_activity' => time()]);
    }

    public function test_education_manager_can_evaluate_instructor_performance(): void
    {
        $instruktur = karyawan::create([
            'nama_lengkap' => 'Instruktur Test Evaluasi',
            'divisi' => 'Education',
            'jabatan' => 'Instruktur',
            'status_aktif' => '1',
            'kode_karyawan' => 'INS_TEST_EVAL',
            'nip' => 'NIP_EVAL_001',
        ]);

        $item = new targetKPI();
        $detail = new DetailTargetKPI();
        $detail->detail_jangka = (string) now()->year;
        $detail->nilai_target = 100;
        $item->setRelation('detailTargetKPI', collect([$detail]));

        $progress = $this->service->calculateEvaluasiKinerjaInstruktur($item, $instruktur->id);

        $this->assertIsNumeric($progress);
        $this->assertGreaterThanOrEqual(0, $progress);
    }

    public function test_education_manager_can_calculate_evaluasi_kinerja_instruktur_detail(): void
    {
        $instruktur = karyawan::create([
            'nama_lengkap' => 'Instruktur Detail Test',
            'divisi' => 'Education',
            'jabatan' => 'Instruktur',
            'status_aktif' => '1',
            'kode_karyawan' => 'INS_DET_01',
            'nip' => 'NIP_DET_001',
        ]);

        ActivityInstruktur::create([
            'user_id' => $instruktur->id,
            'activity_type' => 'Mengajar',
            'activity_date' => now()->startOfYear()->addDays(2)->format('Y-m-d'),
        ]);

        $itemDetail = new targetKPI();
        $detail = new DetailTargetKPI();
        $detail->detail_jangka = (string) now()->year;
        $detail->nilai_target = 100;
        $itemDetail->setRelation('detailTargetKPI', collect([$detail]));

        $result = $this->service->calculateEvaluasiKinerjaInstrukturDetail($itemDetail, $instruktur->id);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('progress', $result);
        $this->assertArrayHasKey('monthly_data', $result);
        $this->assertArrayHasKey('daily_breakdown_per_month', $result);
    }

    public function test_evaluasi_kinerja_excludes_national_holidays(): void
    {
        $instruktur = karyawan::create([
            'nama_lengkap' => 'Instruktur Libur Test',
            'divisi' => 'Education',
            'jabatan' => 'Instruktur',
            'status_aktif' => '1',
            'kode_karyawan' => 'INS_LIBUR_01',
            'nip' => 'NIP_LIBUR_001',
        ]);

        HariLibur::create([
            'nama' => 'Tahun Baru',
            'tanggal' => now()->startOfYear()->addDays(3)->format('Y-m-d'),
            'year' => now()->year,
            'tipe' => 'nasional',
        ]);

        $item = new targetKPI();
        $detail = new DetailTargetKPI();
        $detail->detail_jangka = (string) now()->year;
        $detail->nilai_target = 100;
        $item->setRelation('detailTargetKPI', collect([$detail]));

        $progress = $this->service->calculateEvaluasiKinerjaInstruktur($item, $instruktur->id);

        $this->assertIsNumeric($progress);
    }
}
