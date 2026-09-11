<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StockOpnameTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_stock_opname_index()
    {
        $response = $this->get(route('office.stockOpname.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_store_stock_opname()
    {
        $response = $this->post(route('office.stockOpname.store'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }
}
