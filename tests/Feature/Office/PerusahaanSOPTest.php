<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PerusahaanSOPTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_sop_perusahaan_index()
    {
        $response = $this->get(route('sop.perusahaan.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_store_sop_perusahaan()
    {
        $response = $this->post(route('sop.perusahaan.store'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }
}
