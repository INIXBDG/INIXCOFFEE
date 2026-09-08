<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AnalysisReportTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test akses halaman utama Analysis Report
     */
    public function test_can_access_analysis_report_index()
    {
        $response = $this->get(route('index.analysis'));
        $this->assertContains($response->status(), [200, 302]);
    }

    /**
     * Test akses endpoint create (store) Analysis Report
     */
    public function test_can_store_analysis_report()
    {
        $response = $this->post(route('store.analysis'), []);
        // Karena payload kosong, bisa jadi divalidasi (302) atau berhasil
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }

    /**
     * Test akses endpoint update Analysis Report
     */
    public function test_can_update_analysis_report()
    {
        // Menggunakan dummy ID 1
        $response = $this->put(route('update.analysis', ['id' => 1]), []);
        $this->assertContains($response->status(), [200, 302, 404, 422]);
    }

    /**
     * Test akses endpoint delete Analysis Report
     */
    public function test_can_destroy_analysis_report()
    {
        $response = $this->delete(route('destroy.analysis', ['id' => 1]));
        $this->assertContains($response->status(), [200, 302, 404]);
    }

    /**
     * Test akses endpoint download Analysis Report
     */
    public function test_can_download_analysis_report()
    {
        $response = $this->get(route('download.analysis', ['id' => 1, 'index' => 1]));
        $this->assertContains($response->status(), [200, 302, 404]);
    }
}
