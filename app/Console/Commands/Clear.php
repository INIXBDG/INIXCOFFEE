<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Clear extends Command
{

    protected $signature = 'app:clear';

    protected $description = 'Clear config, cache, route, view, and optimized files';

    public function handle()
    {
        $this->info('🔄 Clearing application cache and optimizations...');

        $this->call('config:clear');
        $this->call('cache:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        $this->call('clear-compiled');
        $this->call('optimize:clear');

        $this->info('✅ Cleared successfully!');
    }
}
