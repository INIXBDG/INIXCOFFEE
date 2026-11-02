<?php

namespace App\Http\Middleware;

use App\Support\LegalitasVault;
use Closure;
use Illuminate\Http\Request;

class EnsureLegalitasUnlocked
{
    public function handle(Request $request, Closure $next)
    {
        if (!LegalitasVault::master()) {
            return response()->json(['message' => 'Password Legalitas belum dibuat.'], 409);
        }

        if (!LegalitasVault::unlocked()) {
            return response()->json(['message' => 'Terkunci.'], 423);
        }

        LegalitasVault::touch();

        return $next($request);
    }
}