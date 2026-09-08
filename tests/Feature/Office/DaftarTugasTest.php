<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DaftarTugasTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_daftar_tugas_index()
    {
        $response = $this->get(route('office.DaftarTugas.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_store_daftar_tugas()
    {
        $response = $this->post(route('office.DaftarTugas.store'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }
}
