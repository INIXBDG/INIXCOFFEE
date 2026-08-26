<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AlertCookieMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Hanya terapkan pada response yang mendukung method cookie (contoh: Response standar atau RedirectResponse)
        if (method_exists($response, 'cookie')) {
            // Cek apakah ada session success
            if (session()->has('success')) {
                // Parameter: name, value, minutes, path, domain, secure, httpOnly, raw, sameSite
                // httpOnly = false agar bisa dibaca oleh document.cookie di Javascript
                $response->cookie('swal_success', session('success'), 1, '/', null, false, false);
            }
            
            // Cek apakah ada session error
            if (session()->has('error')) {
                $response->cookie('swal_error', session('error'), 1, '/', null, false, false);
            }
        }

        return $response;
    }
}
