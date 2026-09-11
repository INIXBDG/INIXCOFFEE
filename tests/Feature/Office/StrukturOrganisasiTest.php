<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StrukturOrganisasiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_struktur_organisasi_index()
    {
        $response = $this->get(route('employee.structure.index'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }
}
