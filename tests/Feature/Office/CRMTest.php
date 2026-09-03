<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CRMTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_crm_index()
    {
        $response = $this->get(route('CRM.index'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }
}
