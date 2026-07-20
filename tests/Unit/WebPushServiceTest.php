<?php

namespace Tests\Unit;

use App\Services\WebPushService;
use Tests\TestCase;

class WebPushServiceTest extends TestCase
{
    public function test_it_disables_webpush_when_vapid_keys_are_missing(): void
    {
        putenv('VAPID_PUBLIC_KEY');
        putenv('VAPID_PRIVATE_KEY');

        $service = new WebPushService();

        $this->assertFalse($service->isEnabled());
        $this->assertNull($service->getVapidPublicKey());
    }
}
