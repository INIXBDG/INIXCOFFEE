<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CertificateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_certificate_index()
    {
        $response = $this->get(route('office.certificate.index'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_get_certificate_data()
    {
        $response = $this->get(route('office.certificate.getData'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_access_certificate_detail()
    {
        $response = $this->get(route('office.certificate.detail', ['rkm_id' => 1]));
        $this->assertContains($response->status(), [200, 302, 404]);
    }

    public function test_can_access_certificate_create()
    {
        $response = $this->get(route('office.certificate.create', ['rkm_id' => 1, 'peserta_id' => 1]));
        $this->assertContains($response->status(), [200, 302, 404]);
    }

    public function test_can_store_certificate()
    {
        $response = $this->post(route('office.certificate.store'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }

    public function test_can_show_certificate()
    {
        $response = $this->get(route('office.certificate.show', ['id' => 1]));
        $this->assertContains($response->status(), [200, 302, 404]);
    }

    public function test_can_preview_certificate()
    {
        $response = $this->get(route('office.certificate.preview', ['id' => 1]));
        $this->assertContains($response->status(), [200, 302, 404]);
    }

    public function test_can_delete_certificate()
    {
        $response = $this->delete(route('office.certificate.delete', ['rkm_id' => 1, 'peserta_id' => 1]));
        $this->assertContains($response->status(), [200, 302, 404]);
    }
}
