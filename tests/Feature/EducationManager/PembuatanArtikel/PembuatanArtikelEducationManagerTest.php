<?php

namespace Tests\Feature\EducationManager\PembuatanArtikel;

use App\Models\DetailTargetKPI;
use App\Models\karyawan;
use App\Models\targetKPI;
use App\Models\User;
use App\Services\KPI\Jabatan\EducationManagerKPIService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PembuatanArtikelEducationManagerTest extends TestCase
{
    use DatabaseTransactions;

    protected EducationManagerKPIService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EducationManagerKPIService();
        session(['last_activity' => time()]);
    }

    public function test_education_manager_can_calculate_pembuatan_artikel_progress(): void
    {
        $karyawan = karyawan::create([
            'nama_lengkap' => 'Instruktur Author Test',
            'divisi' => 'Education',
            'jabatan' => 'Instruktur',
            'status_aktif' => '1',
            'kode_karyawan' => 'INS_ART_01',
            'nip' => 'NIP_ART_001',
        ]);

        User::create([
            'username' => 'author_test',
            'jabatan' => 'Instruktur',
            'status_akun' => '1',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawan->id,
            'id_instruktur' => $karyawan->id,
        ]);

        Http::fake([
            '*inixindobdg.co.id/api/articles*' => Http::response([
                'status' => 'success',
                'data' => [
                    [
                        'tanggal' => now()->format('Y-m-d'),
                        'pembuat' => 'Instruktur Author Test',
                    ],
                ],
            ], 200),
        ]);

        $item = new targetKPI();
        $detail = new DetailTargetKPI();
        $detail->detail_jangka = (string) now()->year;
        $item->setRelation('detailTargetKPI', collect([$detail]));

        $progress = $this->service->calculatePembuatanArtikel($item, 1);

        $this->assertIsNumeric($progress);
        $this->assertGreaterThan(0, $progress);
    }

    public function test_education_manager_can_calculate_pembuatan_artikel_detail(): void
    {
        $karyawan = karyawan::create([
            'nama_lengkap' => 'Instruktur Author Detail',
            'divisi' => 'Education',
            'jabatan' => 'Instruktur',
            'status_aktif' => '1',
            'kode_karyawan' => 'INS_ART_DET',
            'nip' => 'NIP_ART_DET',
        ]);

        User::create([
            'username' => 'author_detail',
            'jabatan' => 'Instruktur',
            'status_akun' => '1',
            'password' => bcrypt('password'),
            'karyawan_id' => $karyawan->id,
            'id_instruktur' => $karyawan->id,
        ]);

        Http::fake([
            '*inixindobdg.co.id/api/articles*' => Http::response([
                'status' => 'success',
                'data' => [
                    [
                        'tanggal' => now()->format('Y-m-d'),
                        'pembuat' => 'Instruktur Author Detail',
                    ],
                    [
                        'tanggal' => now()->startOfYear()->format('Y-m-d'),
                        'pembuat' => 'Instruktur Author Detail',
                    ],
                ],
            ], 200),
        ]);

        $itemDetail = new targetKPI();
        $detail = new DetailTargetKPI();
        $detail->detail_jangka = (string) now()->year;
        $itemDetail->setRelation('detailTargetKPI', collect([$detail]));

        $result = $this->service->calculatePembuatanArtikelDetail($itemDetail);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('progress', $result);
        $this->assertArrayHasKey('pie_chart', $result);
        $this->assertEquals(2, $result['pie_chart']['above']);
    }
}
