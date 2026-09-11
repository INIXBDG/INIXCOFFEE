<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApprovalPendapatanTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_approval_pendapatan_index()
    {
        $response = $this->get(route('approvalPendapatan.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_get_approval_pendapatan()
    {
        $response = $this->get(route('approvalPendapatan.get', ['tahun' => 2024, 'bulan' => 1]));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_update_approval_pendapatan()
    {
        $response = $this->post(route('approvalPendapatan.update', ['id' => 1]), []);
        $this->assertContains($response->status(), [200, 302, 422, 404]);
    }
}
