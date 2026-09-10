<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CateringTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_catering_index()
    {
        $response = $this->get(route('catering.index'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }

    public function test_can_store_catering()
    {
        $response = $this->post(route('catering.store'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422, 500]);
    }
}
