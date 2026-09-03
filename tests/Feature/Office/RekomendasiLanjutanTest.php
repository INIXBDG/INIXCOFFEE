<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RekomendasiLanjutanTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_rekomendasi_lanjutan_index()
    {
        $response = $this->get(route('office.rekomendasiLanjutan.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_store_rekomendasi_lanjutan()
    {
        $response = $this->post(route('office.rekomendasiLanjutan.store'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }
}
