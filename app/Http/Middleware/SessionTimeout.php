<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;

class SessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $timeout = 1800;
        $now = time();

        if (!session()->has('last_activity')) {
            session(['last_activity' => $now]);
            return $next($request);
        }

        if (Auth::check() && $now - session('last_activity') > $timeout) {
            $agent = new Agent();
            $user = Auth::user();

            $agent->setUserAgent($request->header('User-Agent'));

            \App\Models\activityLog::create([
                'user_id' => $user->id,
                'status' => 'logout',
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->header('User-Agent'),
                'platform' => $agent->platform(),
                'browser' => $agent->browser(),
                'device' => $agent->device(),
                'method' => $request->method(),
            ]);

            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            return redirect('/login')->withErrors(['Session habis karena tidak ada aktivitas']);
        }

        session(['last_activity' => $now]);

        return $next($request);
    }
}
