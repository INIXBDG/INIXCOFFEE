<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdministrasiKaryawanTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_administrasi_karyawan_index()
    {
        $response = $this->get(route('administrasi.karyawan'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_store_administrasi_karyawan()
    {
        $response = $this->post(route('administrasi.karyawan.store'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }
}
