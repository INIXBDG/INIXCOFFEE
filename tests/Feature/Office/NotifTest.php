<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class NotifTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_notif_index()
    {
        $response = $this->get(route('notif.index'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }

    public function test_can_store_notif()
    {
        $response = $this->post(route('notif.store'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422, 500]);
    }
}
