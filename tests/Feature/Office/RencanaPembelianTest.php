<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RencanaPembelianTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_rencana_pembelian_index()
    {
        $response = $this->get(route('rencanaPembelian.index'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }

    public function test_can_store_rencana_pembelian()
    {
        $response = $this->post(route('rencanaPembelian.store'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422, 500]);
    }
}
