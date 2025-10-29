<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Jenssegers\Agent\Agent;
use App\Models\activityLog;

class LogActivity
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // kalau belum login, jangan dicatat
        if (!Auth::check()) {
            return $response;
        }

        $agent = new Agent();

        $userAgent = $request->header('User-Agent');
        $ip = $request->ip();
        $agent->setUserAgent($userAgent);

        $platform = $agent->platform();
        $browser = $agent->browser();
        $device = $agent->device();
        $currentUrl = $request->fullUrl();
        $arrayUrl = explode('8001', $currentUrl);
        if ($arrayUrl[1] == "/user-dropdown") {
            return $response;
        }
        $status = $request->get('activity_status', $this->getStatusFromMethod($request->method()));

        if ($request->routeIs('absensi.masuk')) {
            $status = 'Absen Masuk';
        } elseif ($request->routeIs('absensi.keluar')) {
            $status = 'Absen Keluar';
        }

        $detail = null;

        switch ($request->method()) {
            case 'POST':
                $detail = json_encode($request->except(['_token', 'password']), JSON_UNESCAPED_UNICODE);
                break;

            case 'PUT':
            case 'PATCH':
                $detail = json_encode($request->except(['_token', 'password']), JSON_UNESCAPED_UNICODE);
                break;

            case 'DELETE':
                $detail = 'Deleted ID: ' . $request->route('id');
                break;

            default:
                $detail = null;
        }

        $activityLog = new activityLog();
        $activityLog->user_id = Auth::id();
        $activityLog->status = $status;
        $activityLog->url = $currentUrl;
        $activityLog->ip = $ip;
        $activityLog->user_agent = $userAgent;
        $activityLog->platform = $platform;
        $activityLog->browser = $browser;
        $activityLog->device = $device;
        $activityLog->method = $request->method();
        $activityLog->detail = $detail; 
        $activityLog->save();

        return $response;
    }

    private function getStatusFromMethod($method)
    {
        switch ($method) {
            case 'POST':
                return 'create';
            case 'PUT':
            case 'PATCH':
                return 'update';
            case 'DELETE':
                return 'delete';
            default:
                return 'visit'; // GET
        }
    }
}
