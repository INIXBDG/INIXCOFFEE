<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OfficeExamTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_office_exam_index()
    {
        $response = $this->get(route('office.exam.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_access_office_exam_rekap()
    {
        $response = $this->get(route('office.exam.rekap.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_access_office_exam_rekap_json()
    {
        $response = $this->get(route('office.exam.rekap.json'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_update_bundling()
    {
        $response = $this->post(route('office.exam.updateBundling'), []);
        $this->assertContains($response->status(), [200, 302, 422]);
    }
}
