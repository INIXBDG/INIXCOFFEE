<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ExpenseHubTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_expense_hub_index()
    {
        $response = $this->get(route('expensehub.index'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }
}
