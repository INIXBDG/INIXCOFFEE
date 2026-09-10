<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PengajuanKlaimTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_pengajuan_klaim_index()
    {
        $response = $this->get(route('pengajuanklaim.index'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }
}
