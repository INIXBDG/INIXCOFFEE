<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;
use Carbon\Carbon;

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
        $lastActivity = session('last_activity');

        if ($lastActivity instanceof \Carbon\Carbon) {
            $inactiveSeconds = now()->diffInSeconds($lastActivity);
        } else {
            $inactiveSeconds = time() - (int) $lastActivity;
        }

        if (Auth::check() && $inactiveSeconds > $timeout) {
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
