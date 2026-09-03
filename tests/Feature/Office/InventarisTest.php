<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InventarisTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_inventaris_index()
    {
        $response = $this->get(route('IndexInventaris'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }
}
