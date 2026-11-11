<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/**
 * Letakkan di: app/Http/Controllers/AuditRemoteController.php
 * Dipasang di SERVER. Tanpa token yang cocok, endpoint ini berpura-pura tidak ada (404).
 */
class AuditRemoteController extends Controller
{
    public function code(Request $request): JsonResponse
    {
        $token = (string) config('audit.remote_token');
        abort_unless($token !== '' && hash_equals($token, (string) $request->header('X-Audit-Token')), 404);
        abort_unless(config('audit.remote_enabled') || app()->environment(['local', 'staging']), 404);

        @set_time_limit(0);

        $files = [];
        foreach ((array) $request->query('files', []) as $f) {
            $f = ltrim(str_replace('\\', '/', (string) $f), '/');
            if ($f !== '' && !str_contains($f, '..') && str_starts_with($f, 'app/')
                && str_ends_with($f, '.php') && is_file(base_path($f))) {
                $files[] = $f;
            }
        }

        Artisan::call('audit:code', $files ? ['--file' => $files, '--skip-syntax' => true] : []);

        $path = storage_path('logs/audit/code.json');
        $json = is_file($path) ? json_decode(File::get($path), true) : null;
        abort_unless(is_array($json), 500, 'code.json tidak dihasilkan');

        return response()->json($json, 200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}