<?php

namespace App\Http\Middleware;

use Closure;

class LogViewerAccessMiddleware
{
    public function handle($request, Closure $next)
    {
        // Cek apakah user login dan memiliki jabatan "Programmer"
        if (auth()->check() && auth()->user()->can('Akses Development')) {
            return $next($request);
        }

        // Jika tidak memenuhi syarat, kembalikan 403 Unauthorized
        return abort(403, 'Unauthorized access');
    }
}
