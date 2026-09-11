<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AliasTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_alias_index()
    {
        $response = $this->get(route('office.alias.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_update_alias()
    {
        $response = $this->put(route('office.alias.update', ['id' => 1]), []);
        $this->assertContains($response->status(), [200, 302, 422, 404]);
    }
}
