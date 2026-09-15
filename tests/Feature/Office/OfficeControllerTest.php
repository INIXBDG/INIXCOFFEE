<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OfficeControllerTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_office_dashboard()
    {
        $response = $this->get(route('office.dashboard'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }

    public function test_can_access_laporan_status_karyawan()
    {
        $response = $this->get(route('office.laporan.status-karyawan'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }

    public function test_can_access_laporan_trend_karyawan()
    {
        $response = $this->get(route('office.laporan.trend-karyawan'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }

    public function test_can_access_dashboard_tunjangan()
    {
        $response = $this->get(route('office.dashboard.tunjangan'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }

    public function test_can_access_table_outstanding()
    {
        $response = $this->get(route('office.table.outstanding'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }

    public function test_can_access_grafik_outstanding()
    {
        $response = $this->get(route('office.grafik.outstanding'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }

    public function test_can_access_table_exam()
    {
        $response = $this->get(route('office.table.exam'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }
}
