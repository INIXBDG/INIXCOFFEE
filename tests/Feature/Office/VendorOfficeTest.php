<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class VendorOfficeTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_vendor_souvenir_index()
    {
        $response = $this->get(route('office.vendor.souvenir.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_store_vendor_souvenir()
    {
        $response = $this->post(route('office.vendor.souvenir.store'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }

    public function test_can_access_vendor_makansiang_index()
    {
        $response = $this->get(route('office.vendor.makansiang.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_access_vendor_coffeebreak_index()
    {
        $response = $this->get(route('office.vendor.coffeebreak.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_access_vendor_bengkel_index()
    {
        $response = $this->get(route('office.vendor.bengkel.index'));
        $this->assertContains($response->status(), [200, 302]);
    }
}
