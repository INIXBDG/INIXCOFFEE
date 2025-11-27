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
        $timeout = 600; // 30 minutes

        if (Auth::check() && (time() - session('last_activity') > $timeout)) {
            $agent = new Agent();

            $user = auth()->user(); // simpan dulu user sebelum logout
            $userId = $user->id;
            $jabatan = $user->jabatan;

            $userAgent = $request->header('User-Agent');
            $ip = $request->ip();

            $agent->setUserAgent($userAgent);

            $platform = $agent->platform();
            $browser = $agent->browser();
            $device = $agent->device();
            $currentUrl = $request->fullUrl();

            $activityLog = new \App\Models\activityLog();
            $activityLog->user_id = $userId; 
            $activityLog->status = 'logout';
            $activityLog->url = $currentUrl;
            $activityLog->ip = $ip;
            $activityLog->user_agent = $userAgent;
            $activityLog->platform = $platform;
            $activityLog->browser = $browser;
            $activityLog->device = $device;
            $activityLog->method = $request->method(); 
            $activityLog->save();

            Auth::logout(); // logout setelah dicatat
            return redirect('/login')->withErrors(['Your session has expired due to inactivity.']);
        }

        session(['last_activity' => time()]);

        return $next($request);
    }
}
