<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Minishlink\WebPush\VAPID;

class GenerateVapidKeys extends Command
{
    protected $signature = 'webpush:generate-keys';
    protected $description = 'Generate VAPID keys for Web Push';

    public function handle()
    {
        $keys = VAPID::createVapidKeys();
        
        $this->info('Public Key: ' . $keys['publicKey']);
        $this->info('Private Key: ' . $keys['privateKey']);
        
        $this->info("\nAdd these to your .env file:");
        $this->info("VAPID_PUBLIC_KEY={$keys['publicKey']}");
        $this->info("VAPID_PRIVATE_KEY={$keys['privateKey']}");
        
        return 0;
    }
}