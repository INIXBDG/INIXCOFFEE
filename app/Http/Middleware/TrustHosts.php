<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

class TrustHosts extends Middleware
{
    /**
     * Get the host patterns that should be trusted.
     *
     * @return array<int, string|null>
     */
    public function hosts(): array
    {
        $hosts = array_filter([
            $this->allSubdomainsOfApplicationUrl(),
        ]);

        $configured = (string) config('app.trusted_hosts', '');
        foreach (array_filter(array_map('trim', explode(',', $configured))) as $host) {
            $hosts[] = '^' . preg_quote($host, '/') . '$';
        }

        if (!app()->isProduction()) {
            $hosts[] = '^localhost$';
            $hosts[] = '^127\.0\.0\.1$';
            $hosts[] = '^192\.168\.\d{1,3}\.\d{1,3}$';
            $hosts[] = '^10\.\d{1,3}\.\d{1,3}\.\d{1,3}$';
        }

        return $hosts;
    }
}
