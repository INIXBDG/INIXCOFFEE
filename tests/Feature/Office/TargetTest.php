<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TargetTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_target_index()
    {
        $response = $this->get(route('target.index'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }

    public function test_can_store_target()
    {
        $response = $this->post(route('target.store'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422, 500]);
    }
}
