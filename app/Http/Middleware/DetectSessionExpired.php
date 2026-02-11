<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use App\Models\ActivityLog;
use Jenssegers\Agent\Agent;

class DetectSessionExpired
{
    public function handle($request, Closure $next)
    {
        if (Session::has('last_user_id') && !Auth::check()) {
            $userId = Session::get('last_user_id');

            $lastLog = ActivityLog::where('user_id', $userId)->latest()->first();
            $agent = new Agent();
            $userAgent = $request->header('User-Agent');
            $agent->setUserAgent($userAgent);
            if ($lastLog && $lastLog->status !== 'logout') {
                ActivityLog::create([
                    'user_id' => $userId,
                    'status' => 'logout',
                    'url' => $request->fullUrl(),
                    'ip' => $request->ip(),
                    'user_agent' => $userAgent,
                    'platform' => $agent->platform(),
                    'browser' => $agent->browser(),
                    'device' => $agent->device(),
                    'method' => $request->method(),
                    'created_at' => Carbon::now(),
                ]);
            }

            Session::forget('last_user_id');
        }

        return $next($request);
    }
}
