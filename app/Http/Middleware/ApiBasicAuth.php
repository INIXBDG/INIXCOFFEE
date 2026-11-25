<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiBasicAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Mengambil kredensial dari request HTTP Basic Auth
        $username = (string) ($request->getUser() ?? '');
        $password = (string) ($request->getPassword() ?? '');

        // Mengambil kredensial dari config (aman untuk php artisan config:cache)
        $validUsername = (string) config('services.internal_api.username', '');
        $validPassword = (string) config('services.internal_api.password', '');

        if ($validUsername === '' || $validPassword === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'API Basic Auth belum dikonfigurasi pada server.',
            ], 500);
        }

        if (!hash_equals($validUsername, $username) || !hash_equals($validPassword, $password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Kredensial Basic Auth tidak valid.',
            ], 401);
        }

        return $next($request);
    }
}
