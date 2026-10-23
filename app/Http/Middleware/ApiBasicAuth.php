<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ApiBasicAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Mengambil kredensial dari request HTTP Basic Auth
        $username = $request->getUser();
        $password = $request->getPassword();

        // Mencocokkan dengan yang ada di .env
        $validUsername = env('API_USERNAME');
        $validPassword = env('API_PASSWORD');

        if ($username !== $validUsername || $password !== $validPassword) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized. Kredensial Basic Auth tidak valid.'
            ], 401);
        }

        return $next($request);
    }
}
