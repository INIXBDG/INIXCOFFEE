<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

class AuditSync extends Command
{
    protected $signature = 'audit:sync {--only=all : all|code|assets|js|features (boleh dipisah koma)}';
    protected $description = 'Jalankan audit terbaru, simpan ke storage/logs/audit, dan hapus hasil lama';

    private const LOG_PREFIX = [
        'code' => 'audit-code',
        'assets' => 'audit-assets',
        'js' => 'audit-js',
        'features' => 'audit-features',
    ];

    private const JS_ENV_MAP = [
        'AUDIT_BASE_URL' => 'base_url',
        'AUDIT_LOGIN_URL' => 'login_url',
        'AUDIT_EMAIL' => 'email',
        'AUDIT_PASSWORD' => 'password',
        'AUDIT_EMAIL_SELECTOR' => 'email_selector',
        'AUDIT_PASSWORD_SELECTOR' => 'password_selector',
        'AUDIT_CLICK' => 'click',
        'PLAYWRIGHT_BROWSERS_PATH' => 'playwright_browsers_path',
    ];

    private string $dir;
    private array $status = [];

    public function handle(): int
    {
        $only = (string) $this->option('only');
        $targets = $only === 'all'
            ? array_keys(self::LOG_PREFIX)
            : array_values(array_intersect(array_map('trim', explode(',', $only)), array_keys(self::LOG_PREFIX)));

        if (!$targets) {
            $this->error('Nilai --only tidak valid. Pilih: all, code, assets, js, features');
            return self::FAILURE;
        }

        $this->dir = storage_path('logs/audit');
        File::ensureDirectoryExists($this->dir);

        $this->status = (File::exists("$this->dir/status.json") ? json_decode(File::get("$this->dir/status.json"), true) : null) ?: ['steps' => []];
        $this->status['state'] = 'running';
        $this->status['only'] = $only;
        $this->status['started_at'] = now()->toIso8601String();
        $this->status['finished_at'] = null;
        foreach ($targets as $t) {
            $this->status['steps'][$t] = ['state' => 'pending'];
        }
        $this->save();

        $failed = false;
        foreach ($targets as $t) {
            $this->status['steps'][$t] = ['state' => 'running', 'started_at' => now()->toIso8601String()];
            $this->save();
            $this->line("→ audit $t ...");

            try {
                $startedAt = time() - 1;
                $message = match ($t) {
                    'code' => $this->runArtisan('audit:code', 'code', $startedAt),
                    'assets' => $this->runArtisan('audit:assets', 'assets', $startedAt),
                    'js' => $this->runJs($startedAt),
                    'features' => $this->runFeatures($startedAt),
                };
                $this->deleteOldLogs($t);
                $this->status['steps'][$t] = ['state' => 'done', 'finished_at' => now()->toIso8601String(), 'message' => $message];
                $this->info("  $t: $message");
            } catch (Throwable $e) {
                $failed = true;
                $this->status['steps'][$t] = ['state' => 'failed', 'finished_at' => now()->toIso8601String(), 'message' => $e->getMessage()];
                $this->error("  $t gagal: " . $e->getMessage());
            }
            $this->save();
        }

        $this->status['state'] = $failed ? 'failed' : 'done';
        $this->status['finished_at'] = now()->toIso8601String();
        $this->save();

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function runFeatures(int $startedAt): string
    {
        $params = [];
        if ($uid = config('audit.user_id')) {
            $params['--user'] = $uid;
        }
        Artisan::call('audit:features', $params);
        return $this->summaryOf('features', $startedAt);
    }

    private function save(): void
    {
        File::put("$this->dir/status.json", json_encode($this->status, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), true);
    }

    private function runArtisan(string $command, string $name, int $startedAt): string
    {
        Artisan::call($command);
        return $this->summaryOf($name, $startedAt);
    }

    private function runJs(int $startedAt): string
    {
        if (!is_file(base_path('scripts/audit-js.mjs'))) {
            throw new RuntimeException('scripts/audit-js.mjs tidak ditemukan');
        }

        $env = [];
        foreach (self::JS_ENV_MAP as $envKey => $configKey) {
            $value = config("audit.$configKey");
            if ($value !== null && $value !== '') {
                $env[$envKey] = (string) $value;
            }
        }
        $env['AUDIT_BASE_URL'] ??= (string) config('app.url');
        if (!isset($env['PLAYWRIGHT_BROWSERS_PATH']) && is_dir(base_path('.playwright'))) {
            $env['PLAYWRIGHT_BROWSERS_PATH'] = base_path('.playwright');
        }
        $home = storage_path('app/audit-home');
        File::ensureDirectoryExists($home);
        $env['HOME'] = $home;

        if (empty($env['PLAYWRIGHT_BROWSERS_PATH'])) {
            $candidates = array_merge(
                [
                    storage_path('app/audit-home/.cache/ms-playwright'),
                    base_path('.playwright'),
                    (getenv('HOME') ?: '') . '/.cache/ms-playwright',
                ],
                (array) config('audit.playwright_search_paths', [])
            );
            foreach ($candidates as $dir) {
                if ($dir && is_dir($dir) && (glob($dir . '/chromium*') || glob($dir . '/chromium_headless_shell*'))) {
                    $env['PLAYWRIGHT_BROWSERS_PATH'] = $dir;
                    break;
                }
            }
        }

        $p = new Process(['node', 'scripts/audit-js.mjs'], base_path(), $env, null, 900);
        $p->run();

        $json = "$this->dir/js.json";
        if (!is_file($json) || filemtime($json) < $startedAt) {
            $err = trim($p->getErrorOutput() ?: $p->getOutput());
            throw new RuntimeException('audit-js gagal dijalankan: ' . mb_substr($err, -700));
        }
        return $this->summaryOf('js', $startedAt);
    }

    private function summaryOf(string $name, int $startedAt): string
    {
        $file = "$this->dir/$name.json";
        if (!is_file($file) || filemtime($file) < $startedAt) {
            throw new RuntimeException("audit:$name tidak menghasilkan file hasil baru");
        }
        $d = json_decode(File::get($file), true);
        $ok = $d['summary']['ok'] ?? 0;
        $err = $d['summary']['errors'] ?? 0;
        $warn = $d['summary']['warnings'] ?? 0;
        return "$err error, $warn warning" . ($ok ? ", $ok ok" : '');
    }

    private function deleteOldLogs(string $name): void
    {
        $keep = storage_path('logs/' . self::LOG_PREFIX[$name] . '-' . now()->format('Y-m-d') . '.log');
        foreach (glob(storage_path('logs/' . self::LOG_PREFIX[$name] . '-*.log')) ?: [] as $f) {
            if ($f !== $keep) {
                @unlink($f);
            }
        }
    }
}