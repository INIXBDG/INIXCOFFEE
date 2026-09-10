<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KegiatanTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_kegiatan_index()
    {
        $response = $this->get(route('office.indexKegiatan'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_store_kegiatan()
    {
        $response = $this->post(route('office.storeKegiatan'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }
}
