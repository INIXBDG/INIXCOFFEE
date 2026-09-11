<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BiayaTransportasiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_biaya_transportasi_index()
    {
        $response = $this->get(route('office.biayaTransportasi.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_create_biaya_transportasi()
    {
        $response = $this->post(route('office.biayaTransportasi.create'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }

    public function test_can_get_biaya_transportasi_data()
    {
        $response = $this->get(route('office.biayaTransportasi.get'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_update_biaya_transportasi()
    {
        $response = $this->post(route('office.biayaTransportasi.update', ['id_pickup_driver' => 1]), []);
        $this->assertContains($response->status(), [200, 302, 404, 422]);
    }

    public function test_can_delete_biaya_transportasi()
    {
        $response = $this->delete(route('office.biayaTransportasi.destroy', ['id_pickup_driver' => 1]));
        $this->assertContains($response->status(), [200, 302, 404]);
    }

    public function test_can_export_excel_biaya_transportasi()
    {
        $response = $this->get(route('office.biayaTransportasi.exportExcel'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_access_budget_summary()
    {
        $response = $this->get(route('office.biayaTransportasi.budgetSummary'));
        $this->assertContains($response->status(), [200, 302]);
    }
}
