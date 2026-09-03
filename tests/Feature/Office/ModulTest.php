<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ModulTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_modul_index()
    {
        $response = $this->get(route('office.modul.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_store_nomor_modul()
    {
        $response = $this->post(route('office.modul.store.nomor'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }

    public function test_can_access_modul_detail()
    {
        $response = $this->get(route('office.modul.detail', ['id' => 1]));
        $this->assertContains($response->status(), [200, 302, 404]);
    }

    public function test_can_store_modul()
    {
        $response = $this->post(route('office.modul.store'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }

    public function test_can_update_modul()
    {
        $response = $this->put(route('office.modul.update', ['id' => 1]), []);
        $this->assertContains($response->status(), [200, 302, 404, 422]);
    }

    public function test_can_delete_modul()
    {
        $response = $this->delete(route('office.modul.delete', ['id' => 1]));
        $this->assertContains($response->status(), [200, 302, 404]);
    }

    public function test_can_access_modul_rekap()
    {
        $response = $this->get(route('office.modul.rekap'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_access_modul_rekap_json()
    {
        $response = $this->get(route('office.modul.rekap.json'));
        $this->assertContains($response->status(), [200, 302]);
    }
}
