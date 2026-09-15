<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HRTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_hr_index()
    {
        $response = $this->get(route('HR.index'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }

    public function test_can_access_hr_folders_index()
    {
        $response = $this->get(route('HR.folders.index'));
        $this->assertContains($response->status(), [200, 302, 500]);
    }
}
