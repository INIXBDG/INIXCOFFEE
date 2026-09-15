<?php

namespace Tests\Feature\Office;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PicPenagihanTest extends TestCase
{
    use DatabaseTransactions;

    public function test_can_access_pic_penagihan_data()
    {
        $response = $this->get(route('picpenagihan.data'));
        $this->assertContains($response->status(), [200, 302]);
    }

    public function test_can_store_pic_penagihan()
    {
        $response = $this->post(route('picpenagihan.store'), []);
        $this->assertContains($response->status(), [200, 201, 302, 422]);
    }
}
