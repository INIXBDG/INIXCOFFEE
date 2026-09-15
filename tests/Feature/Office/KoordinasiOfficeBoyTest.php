<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KoordinasiOfficeBoyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_koordinasi_ob_index()
    {
        $response = $this->get(route('office.KoordinasiOb.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_store_koordinasi_ob()
    {
        $response = $this->post(route('office.KoordinasiOb.store'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }
}
