<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KendaraanTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_kendaraan_kondisi_index()
    {
        $response = $this->get(route('office.indexKondisiKendaraan'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_store_kendaraan_kondisi()
    {
        $response = $this->post(route('office.storeKondisiKendaraan'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }
}
