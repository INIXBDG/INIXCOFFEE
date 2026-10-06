<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AuditFeatures extends Command
{
    protected $signature = 'audit:features
        {--only= : Fitur (segmen URI / nama), koma}
        {--route= : Satu nama route}
        {--user= : ID user login untuk request}';

    protected $description = 'Audit alur fitur: buka daftar, form tambah, submit, edit — hanya flag 500/exception';

    private array $issues = [];
    private array $seen = [];

    public function handle(): int
    {
        $uid = $this->option('user') ?: config('audit.user_id');
        if (!$uid) {
            $this->error('Set --user= atau AUDIT_USER_ID di .env agar audit fitur tidak kena error sesi');
            return self::FAILURE;
        }
        Auth::loginUsingId((int) $uid);
        if (!Auth::check()) {
            $this->error("Gagal login user ID $uid");
            return self::FAILURE;
        }

        $only = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('only')))));
        $onlyRoute = trim((string) $this->option('route'));
        $partial = $only !== [] || $onlyRoute !== '';
        $flows = $this->buildFlows($only, $onlyRoute);

        if (!$flows) {
            $this->warn('Tidak ada alur yang diaudit.');
            if (!$partial) {
                $this->writeJson([], null);
            }
            return self::SUCCESS;
        }

        $kernel = app(HttpKernel::class);

        foreach ($flows as $flow) {
            $this->runFlow($kernel, $flow);
        }

        $this->writeJson($this->issues, $partial ? $flows : null);
        $errors = count(array_filter($this->issues, fn ($i) => $i['level'] === 'ERROR'));
        $oks = count(array_filter($this->issues, fn ($i) => $i['level'] === 'OK'));
        $this->info("audit:features → $oks ok, $errors error");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function buildFlows(array $only, string $onlyRoute): array
    {
        $skip = '/^(_|telescope|horizon|livewire|storage|sanctum|up$)|logout|signout|delete|destroy|download|export|print/i';
        $by = [];

        foreach (Route::getRoutes() as $route) {
            $methods = array_diff($route->methods(), ['HEAD', 'OPTIONS']);
            if (!$methods) {
                continue;
            }
            $uri = trim($route->uri(), '/');
            if ($uri === '' || str_contains($uri, '{') || preg_match($skip, $uri)) {
                continue;
            }
            $name = (string) $route->getName();
            $feature = strtolower(explode('/', $uri)[0] ?: 'root');
            if ($only && !in_array($feature, $only, true) && !in_array($name, $only, true)) {
                continue;
            }
            if ($onlyRoute !== '' && $name !== $onlyRoute && $uri !== $onlyRoute) {
                continue;
            }
            $by[$feature][] = [
                'uri' => $uri,
                'name' => $name,
                'methods' => array_values($methods),
                'route' => $route,
            ];
        }

        $flows = [];
        foreach ($by as $feature => $list) {
            $index = null;
            foreach ($list as $r) {
                if (in_array('GET', $r['methods'], true) && preg_match('/index$/i', $r['name'])) {
                    $index = $r;
                    break;
                }
            }
            if (!$index) {
                foreach ($list as $r) {
                    if (in_array('GET', $r['methods'], true) && $r['uri'] === $feature) {
                        $index = $r;
                        break;
                    }
                }
            }
            if (!$index) {
                foreach ($list as $r) {
                    if (in_array('GET', $r['methods'], true) && substr_count($r['uri'], '/') <= 1) {
                        $index = $r;
                        break;
                    }
                }
            }

            $create = null;
            foreach ($list as $r) {
                if (in_array('GET', $r['methods'], true) && (preg_match('#(^|/)create$#i', $r['uri']) || preg_match('/create$/i', $r['name']))) {
                    $create = $r;
                    break;
                }
            }

            $store = null;
            foreach ($list as $r) {
                if (array_intersect($r['methods'], ['POST']) && (preg_match('/store$/i', $r['name']) || $r['uri'] === ($create['uri'] ?? '') || $r['uri'] === $feature || $r['uri'] === ($index['uri'] ?? ''))) {
                    if (preg_match('/store$/i', $r['name']) || ($create && $r['uri'] === preg_replace('#/create$#', '', $create['uri']))) {
                        $store = $r;
                        break;
                    }
                }
            }
            if (!$store) {
                foreach ($list as $r) {
                    if (in_array('POST', $r['methods'], true) && preg_match('/store$/i', $r['name'])) {
                        $store = $r;
                        break;
                    }
                }
            }

            if ($index) {
                $flows[] = ['feature' => $feature, 'step' => 'buka_daftar', 'method' => 'GET', 'uri' => $index['uri'], 'name' => $index['name']];
            }
            if ($create) {
                $flows[] = ['feature' => $feature, 'step' => 'buka_form_tambah', 'method' => 'GET', 'uri' => $create['uri'], 'name' => $create['name']];
            }
            if ($store) {
                $flows[] = ['feature' => $feature, 'step' => 'submit_tambah', 'method' => 'POST', 'uri' => $store['uri'], 'name' => $store['name']];
            } elseif ($create) {
                $flows[] = ['feature' => $feature, 'step' => 'submit_tambah', 'method' => 'POST', 'uri' => preg_replace('#/create$#', '', $create['uri']), 'name' => $create['name']];
            }
        }

        return $flows;
    }

    private function runFlow(HttpKernel $kernel, array $flow): void
    {
        $uri = '/' . ltrim($flow['uri'], '/');
        $method = $flow['method'];
        $label = "$method $uri";

        try {
            $request = Request::create($uri, $method, $method === 'POST' ? ['_token' => 'audit'] : []);
            if ($method === 'POST') {
                $request->headers->set('Accept', 'text/html,application/json');
            }
            $response = $kernel->handle($request);
            $this->inspectResponse($flow, $label, $response);
            $kernel->terminate($request, $response);
        } catch (Throwable $e) {
            if ($this->isAuthOrSessionNoise($e, 0)) {
                return;
            }
            $this->addError($flow, $label, $e);
        }
    }

    private function inspectResponse(array $flow, string $label, Response $response): void
    {
        $code = $response->getStatusCode();

        if (in_array($code, [401, 403, 404, 405, 419, 422, 301, 302, 303, 307, 308], true)) {
            if ($code >= 300 && $code < 400) {
                $this->addOk($flow, $label, "Redirect $code (alur lanjut)");
            }
            return;
        }

        if ($code >= 500) {
            $ex = property_exists($response, 'exception') ? $response->exception : null;
            $body = method_exists($response, 'getContent') ? (string) $response->getContent() : '';
            if ($this->isAuthOrSessionNoise($ex instanceof Throwable ? $ex : null, $code, $body)) {
                return;
            }
            if ($ex instanceof Throwable) {
                $this->addError($flow, $label, $ex, $code);
            } else {
                $snippet = $this->snippet($body);
                $this->push('ERROR', $flow, $this->typeFromBody($snippet, $code), $label, $snippet ?: "HTTP $code");
            }
            return;
        }

        $body = method_exists($response, 'getContent') ? (string) $response->getContent() : '';
        if ($this->bodyLooksLikeServerError($body)) {
            if ($this->isAuthOrSessionNoise(null, 500, $body)) {
                return;
            }
            $this->push('ERROR', $flow, $this->typeFromBody($body, 500), $label, $this->snippet($body));
            return;
        }

        $this->addOk($flow, $label, "HTTP $code");
    }

    private function isAuthOrSessionNoise(?Throwable $e, int $code, string $body = ''): bool
    {
        if (in_array($code, [401, 403, 419], true)) {
            return true;
        }

        $hay = strtolower(($e ? $e->getMessage() : '') . ' ' . $body);
        $needles = [
            'unauthenticated',
            'please login',
            'silakan login',
            'harus login',
            'not logged in',
            'csrf token mismatch',
            'page expired',
            'session store not set',
            'session has expired',
            'authenticationexception',
            'authorizationexception',
            'this action is unauthorized',
            'redirecting to login',
            'route [login]',
        ];
        foreach ($needles as $n) {
            if (str_contains($hay, $n)) {
                return true;
            }
        }

        if ($e) {
            $cls = strtolower(get_class($e));
            if (str_contains($cls, 'authentication') || str_contains($cls, 'authorization')) {
                return true;
            }
        }

        return false;
    }

    private function bodyLooksLikeServerError(string $body): bool
    {
        if ($body === '') {
            return false;
        }
        return (bool) preg_match(
            '/SQLSTATE|QueryException|ErrorException|Integrity constraint|cannot be null|NOT NULL|View \[.*?\] not found|Illuminate\\\\Database|Illuminate\\\\View\\\\|Fatal error/i',
            $body
        );
    }

    private function typeFromBody(string $body, int $code): string
    {
        $t = strtolower($body);
        if ($code >= 500 || str_contains($t, 'sqlstate') || str_contains($t, 'queryexception') || str_contains($t, 'cannot be null')) {
            return 'HTTP 500 / Database';
        }
        if (str_contains($t, 'view [') && str_contains($t, 'not found')) {
            return 'View tidak ditemukan';
        }
        if (str_contains($t, 'errorexception') || str_contains($t, 'exception')) {
            return 'Exception';
        }
        return 'HTTP 500';
    }

    private function snippet(string $body): string
    {
        $body = trim(preg_replace('/\s+/', ' ', strip_tags($body)));
        return mb_substr($body, 0, 400);
    }

    private function addError(array $flow, string $label, Throwable $e, int $code = 500): void
    {
        $file = $this->appFileFromException($e);
        $line = $file['line'];
        $rel = $file['rel'];

        $msg = get_class($e) . ': ' . mb_substr($e->getMessage(), 0, 300);
        if ($rel) {
            $msg .= " @ {$rel}:{$line}";
        }

        $this->push(
            'ERROR',
            $flow,
            $this->typeFromBody($e->getMessage(), $code),
            $label,
            $msg,
            $rel ?: null,
            $line
        );
    }

    private function appFileFromException(Throwable $e): array
    {
        $base = realpath(base_path()) ?: base_path();
        $candidates = [[$e->getFile(), $e->getLine()]];

        foreach ($e->getTrace() as $frame) {
            if (!empty($frame['file'])) {
                $candidates[] = [$frame['file'], $frame['line'] ?? 0];
            }
        }

        foreach ($candidates as [$path, $line]) {
            $real = realpath($path) ?: $path;
            $norm = str_replace('\\', '/', $real);
            $baseNorm = str_replace('\\', '/', $base);
            if (!str_starts_with($norm, $baseNorm)) {
                continue;
            }
            $rel = ltrim(substr($norm, strlen($baseNorm)), '/');
            if (str_starts_with($rel, 'vendor/')) {
                continue;
            }
            return ['rel' => $rel, 'line' => (int) $line];
        }

        $rel = str_replace([base_path() . DIRECTORY_SEPARATOR, '\\'], ['', '/'], $e->getFile());
        return ['rel' => $rel, 'line' => (int) $e->getLine()];
    }

    private function addOk(array $flow, string $label, string $detail): void
    {
        $this->push('OK', $flow, 'Alur OK', $label, "Berhasil: {$flow['step']} ($detail)");
    }

    private function push(
        string $level,
        array $flow,
        string $type,
        string $label,
        string $message,
        ?string $file = null,
        int $line = 0
    ): void {
        $key = implode('|', [$level, $flow['feature'], $flow['step'], $type, $message]);
        if (isset($this->seen[$key])) {
            return;
        }
        $this->seen[$key] = true;
        $this->issues[] = [
            'level' => $level,
            'feature' => $flow['feature'],
            'function' => $flow['step'],
            'type' => $type,
            'file' => $file,
            'line' => $line,
            'route' => $flow['name'] ?: null,
            'method' => $flow['method'],
            'uri' => $flow['uri'],
            'url' => $label,
            'status' => $level === 'OK' ? 'ok' : 'failed',
            'message' => $message,
        ];
    }

    private function writeJson(array $issues, ?array $flows): void
    {
        $dir = storage_path('logs/audit');
        File::ensureDirectoryExists($dir);
        $path = "$dir/features.json";

        if ($flows !== null) {
            $touched = [];
            foreach ($flows as $flow) {
                $touched[$flow['feature'] . '|' . $flow['step'] . '|' . $flow['uri']] = true;
            }

            $prev = is_file($path) ? (json_decode(File::get($path), true) ?: []) : [];
            $kept = array_values(array_filter(
                $prev['issues'] ?? [],
                fn ($i) => !isset($touched[($i['feature'] ?? '') . '|' . ($i['function'] ?? '') . '|' . ($i['uri'] ?? '')])
            ));

            $issues = array_merge($kept, $issues);
        }

        $errors = count(array_filter($issues, fn ($i) => ($i['level'] ?? '') === 'ERROR'));
        $oks = count(array_filter($issues, fn ($i) => ($i['level'] ?? '') === 'OK'));

        File::put($path, json_encode([
            'kind' => 'features',
            'generated_at' => now()->toIso8601String(),
            'summary' => ['errors' => $errors, 'warnings' => 0, 'ok' => $oks],
            'issues' => array_values($issues),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    }
}