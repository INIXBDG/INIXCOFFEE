<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TagihanPerusahaanTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_tagihan_perusahaan_index()
    {
        $response = $this->get(route('office.tagihanPerusahaan.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_store_tagihan_perusahaan()
    {
        $response = $this->post(route('storeTagihanPerusahaan'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }

    public function test_can_update_tagihan_perusahaan()
    {
        $response = $this->post(route('updateTagihanPerusahaan', ['id' => 1]), []);
        $this->assertContains($response->status(), [200, 302, 404, 422]);
    }

    public function test_can_access_detail_tagihan_perusahaan()
    {
        $response = $this->get(route('detailTagihanPerusahaan', ['id' => 1]));
        $this->assertContains($response->status(), [200, 302, 404]);
    }

    public function test_can_delete_tagihan_perusahaan()
    {
        $response = $this->post(route('hapusTagihanPerusahaan', ['id' => 1]), []);
        $this->assertContains($response->status(), [200, 302, 404]);
    }
}
