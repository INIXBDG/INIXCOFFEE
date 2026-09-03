<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardSouvenirTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_dashboard_souvenir_index()
    {
        $response = $this->get(route('dashboard.souvenir'));
        $this->assertContains($response->status(), [200, 302]);
    }
}
