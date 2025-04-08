<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $timeout = 1800; // 30 minutes

        if (Auth::check() && (time() - session('last_activity') > $timeout)) {
            Auth::logout();
            return redirect('/login')->withErrors(['Your session has expired due to inactivity.']);
        }

        session(['last_activity' => time()]);

        return $next($request);
    }
}
