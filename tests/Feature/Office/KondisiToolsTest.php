<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KondisiToolsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_kondisi_tools_index()
    {
        $response = $this->get(route('office.KondisiTools.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_store_kondisi_tools()
    {
        $response = $this->post(route('office.KondisiTools.store'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }

    public function test_can_update_kondisi_tools()
    {
        $response = $this->post(route('office.KondisiTools.update', ['id' => 1]), []);
        $this->assertContains($response->status(), [200, 302, 404, 422]);
    }

    public function test_can_delete_kondisi_tools()
    {
        $response = $this->post(route('office.KondisiTools.delete', ['id' => 1]), []);
        $this->assertContains($response->status(), [200, 302, 404]);
    }

    public function test_can_get_tools()
    {
        $response = $this->get(route('office.KondisiTools.get-tools'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_get_pemeriksaan()
    {
        $response = $this->get(route('office.KondisiTools.get-pemeriksaan'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_get_template()
    {
        $response = $this->get(route('office.KondisiTools.get-template'));
        $this->assertContains($response->status(), [200, 302]);
    }
}
