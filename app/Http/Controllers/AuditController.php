<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class AuditController extends Controller
{
    private const STALE_MINUTES = 20;

    public function index()
    {
        $this->guard();

        return view('audit.index', ['payload' => $this->payload(), 'base' => base_path()]);
    }

    public function data(): JsonResponse
    {
        $this->guard();

        return response()->json($this->payload());
    }

    public function status(): JsonResponse
    {
        $this->guard();

        return response()->json($this->readStatus());
    }

    public function sync(Request $request): JsonResponse
    {
        $this->guard();

        $only = (string) $request->input('only', 'all');
        if (!in_array($only, ['all', 'code', 'assets', 'js', 'features'], true)) {
            return response()->json(['ok' => false, 'message' => 'Parameter only tidak valid'], 422);
        }
        if (($this->readStatus()['state'] ?? '') === 'running') {
            return response()->json(['ok' => false, 'message' => 'Sinkronisasi sedang berjalan'], 409);
        }

        File::ensureDirectoryExists($this->dir());
        $status = $this->readStatus();
        $status['state'] = 'running';
        $status['only'] = $only;
        $status['started_at'] = now()->toIso8601String();
        $status['finished_at'] = null;
        File::put($this->dir() . '/status.json', json_encode($status, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), true);

        $php = (new PhpExecutableFinder())->find(false) ?: 'php';
        $cmd = sprintf(
            'nohup %s artisan audit:sync --only=%s > %s 2>&1 &',
            escapeshellarg($php),
            escapeshellarg($only),
            escapeshellarg($this->dir() . '/last-run.log')
        );
        Process::fromShellCommandline($cmd, base_path())->run();

        return response()->json(['ok' => true]);
    }

    public function syncFile(Request $request): JsonResponse
    {
        $this->guard();

        $file = str_replace('\\', '/', ltrim((string) $request->input('file', ''), '/'));
        if ($file === '' || str_contains($file, '..') || !str_starts_with($file, 'app/')) {
            return response()->json(['ok' => false, 'message' => 'Path file tidak valid'], 422);
        }
        $absolute = base_path($file);
        if (!is_file($absolute) || pathinfo($absolute, PATHINFO_EXTENSION) !== 'php') {
            return response()->json(['ok' => false, 'message' => "File tidak ditemukan: $file"], 404);
        }

        Artisan::call('audit:code', [
            '--file' => [$file],
            '--skip-syntax' => true,
        ]);

        $payload = $this->payload();
        $issues = collect($payload['code']['issues'] ?? [])->where('file', $file)->values()->all();

        $stillThere = $this->stillThere(
            $issues,
            $request->input('line'),
            (string) $request->input('type', ''),
            (string) $request->input('message', '')
        );

        return response()->json([
            'ok' => true,
            'file' => $file,
            'fixed' => !$stillThere,
            'code' => $payload['code'],
        ]);
    }

    public function syncFeature(Request $request): JsonResponse
    {
        $this->guard();

        $routeName = trim((string) $request->input('route', ''));
        $feature = trim((string) $request->input('feature', ''));

        if ($routeName === '' && $feature === '') {
            return response()->json(['ok' => false, 'message' => 'route atau feature wajib'], 422);
        }

        $php = (new PhpExecutableFinder())->find(false) ?: 'php';
        $args = [$php, 'artisan', 'audit:features'];
        $args[] = $routeName !== '' ? '--route=' . $routeName : '--only=' . $feature;

        $startedAt = time() - 1;
        $process = new Process($args, base_path(), null, null, 300);
        $process->run();

        $resultFile = $this->dir() . '/features.json';
        if (!is_file($resultFile) || filemtime($resultFile) < $startedAt) {
            $output = trim($process->getErrorOutput() ?: $process->getOutput());

            return response()->json([
                'ok' => false,
                'message' => 'Audit fitur gagal: ' . mb_substr($output, -500),
            ], 500);
        }

        $payload = $this->payload();
        $issues = collect($payload['features']['issues'] ?? []);
        $issues = $routeName !== ''
            ? $issues->where('route', $routeName)
            : $issues->where('feature', $feature);

        $stillThere = $this->stillThere(
            $issues->values()->all(),
            $request->input('line'),
            (string) $request->input('type', ''),
            (string) $request->input('message', '')
        );

        return response()->json([
            'ok' => true,
            'fixed' => !$stillThere,
            'features' => $payload['features'],
        ]);
    }

    private function stillThere(array $issues, $line, string $type, string $message): bool
    {
        return collect($issues)->contains(function ($i) use ($line, $type, $message) {
            if ($type !== '' && ($i['type'] ?? '') !== $type) {
                return false;
            }
            if ($line !== null && $line !== '' && (int) ($i['line'] ?? 0) !== (int) $line) {
                return false;
            }
            if ($message !== '' && ($i['message'] ?? '') !== $message) {
                return false;
            }
            return true;
        });
    }

    private function guard(): void
    {
        abort_unless(app()->environment(['local', 'staging']), 403, 'Halaman audit hanya tersedia di local/staging.');
    }

    private function dir(): string
    {
        return storage_path('logs/audit');
    }

    private function read(string $name): ?array
    {
        $file = $this->dir() . "/$name.json";

        return is_file($file) ? (json_decode(File::get($file), true) ?: null) : null;
    }

    private function readStatus(): array
    {
        $s = $this->read('status') ?? ['state' => 'idle', 'steps' => []];

        if (($s['state'] ?? '') === 'running' && !empty($s['started_at'])
            && Carbon::parse($s['started_at'])->diffInMinutes(now(), false) > self::STALE_MINUTES) {
            $s['state'] = 'failed';
            $s['finished_at'] = now()->toIso8601String();
            foreach ($s['steps'] ?? [] as $k => $step) {
                if (in_array($step['state'] ?? '', ['running', 'pending'], true)) {
                    $s['steps'][$k] = ['state' => 'failed', 'message' => 'Proses terhenti / melebihi ' . self::STALE_MINUTES . ' menit'];
                }
            }
        }

        return $s;
    }

    public function payload(): array
    {
        return [
            'code' => $this->read('code'),
            'js' => $this->read('js'),
            'assets' => $this->read('assets'),
            'features' => $this->read('features'),
            'status' => $this->readStatus(),
        ];
    }
}