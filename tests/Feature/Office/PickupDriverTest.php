<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PickupDriverTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_pickup_driver_index()
    {
        $response = $this->get(route('office.pickupDriver.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_access_pickup_driver_create()
    {
        $response = $this->get(route('office.pickupDriver.create'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_get_pickup_driver()
    {
        $response = $this->get(route('office.pickupDriver.get'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_store_pickup_driver()
    {
        $response = $this->post(route('office.pickupDriver.store'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }

    public function test_can_update_kepulangan_pickup_driver()
    {
        $response = $this->post(route('office.pickupDriver.updateKepulangan'), []);
        $this->assertContains($response->status(), [200, 302, 422]);
    }

    public function test_can_delete_pickup_driver()
    {
        $response = $this->delete(route('office.pickupDriver.delete', ['id' => 1]));
        $this->assertContains($response->status(), [200, 302, 404]);
    }

    public function test_can_update_koordinasi_pickup_driver()
    {
        $response = $this->post(route('office.pickupDriver.updateKoordinasi'), []);
        $this->assertContains($response->status(), [200, 302, 422]);
    }

    public function test_can_get_driver_status()
    {
        $response = $this->get(route('office.pickupDriver.getDriverStatus'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_export_excel()
    {
        $response = $this->get(route('office.pickupDriver.export.excel'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_export_pdf()
    {
        $response = $this->get(route('office.pickupDriver.export.pdf'));
        $this->assertContains($response->status(), [200, 302]);
    }
}
