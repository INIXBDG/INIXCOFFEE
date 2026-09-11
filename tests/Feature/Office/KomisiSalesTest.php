<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KomisiSalesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_komisi_sales_index()
    {
        $response = $this->get(route('komisiSales.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_get_komisi_sales()
    {
        $response = $this->get(route('komisiSales.get', ['tahun' => 2024, 'quartal' => 1]));
        $this->assertContains($response->status(), [200, 302]);
    }
}
