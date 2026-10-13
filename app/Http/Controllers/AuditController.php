<?php

namespace App\Http\Controllers;

use App\Support\AuditSolver;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use App\Support\AuditExporter;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use RuntimeException;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Throwable;

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
        $status['steps'] = [];
        File::put($this->dir() . '/status.json', json_encode($status, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), true);

        $php = (new PhpExecutableFinder())->find(false) ?: 'php';
        try {
            if (PHP_OS_FAMILY === 'Windows') {
                $this->launchWindowsWorker($php, $only);
            } else {
                $cmd = sprintf(
                    'nohup %s artisan audit:sync --only=%s > %s 2>&1 &',
                    escapeshellarg($php),
                    escapeshellarg($only),
                    escapeshellarg($this->dir() . '/last-run.log')
                );
                $process = Process::fromShellCommandline($cmd, base_path());
                $process->run();
                if (!$process->isSuccessful()) {
                    throw new RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput()));
                }
            }
        } catch (Throwable $e) {
            $status['state'] = 'failed';
            $status['finished_at'] = now()->toIso8601String();
            $status['message'] = 'Gagal memulai proses audit: ' . $e->getMessage();
            File::put($this->dir() . '/status.json', json_encode($status, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), true);

            return response()->json(['ok' => false, 'message' => $status['message']], 500);
        }

        return response()->json(['ok' => true]);
    }

    public function export(Request $request)
    {
        $this->guard();
        @set_time_limit(0);
        @ini_set('memory_limit', '1024M');

        $format = (string) $request->query('format', 'xlsx');
        $tab = (string) $request->query('tab', 'code');
        $scope = (string) $request->query('scope', 'filtered');

        if (!in_array($format, ['xlsx', 'pdf'], true)
            || !array_key_exists($tab, AuditExporter::TABS)
            || !in_array($scope, ['filtered', 'tab', 'all'], true)) {
            return response()->json(['ok' => false, 'message' => 'Parameter export tidak valid'], 422);
        }

        $exporter = app(AuditExporter::class);
        $tabs = $scope === 'all' ? array_keys(AuditExporter::TABS) : [$tab];
        $filters = $scope === 'filtered' ? [
            'level' => (string) $request->query('level', 'all'),
            'category' => (string) $request->query('category', 'all'),
            'feature' => (string) $request->query('feature', 'all'),
            'query' => (string) $request->query('q', ''),
        ] : [];

        $sections = $exporter->build($this->payload(), $tabs, $filters);
        $name = 'audit-' . ($scope === 'all' ? 'semua' : $tab) . '-' . now()->format('Ymd-His') . '.' . $format;

        if ($format === 'pdf') {
            return $exporter->pdf($sections)->download($name);
        }

        $book = $exporter->xlsx($sections);

        return response()->streamDownload(
            function () use ($book) {
                (new Xlsx($book))->save('php://output');
            },
            $name,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    private function launchWindowsWorker(string $php, string $only): void
    {
        $artisan = base_path('artisan');
        if (!is_file($artisan)) {
            throw new RuntimeException('File artisan tidak ditemukan: ' . $artisan);
        }

        // Di bawah Apache, PHP_BINARY bisa httpd.exe / php-cgi.exe. Pakai php.exe (CLI).
        $override = env('AUDIT_PHP_BINARY');
        if ($override) {
            $php = $override;
        } elseif (stripos(basename($php), 'php.exe') === false) {
            $guess = dirname($php) . DIRECTORY_SEPARATOR . 'php.exe';
            if (is_file($guess)) {
                $php = $guess;
            }
        }
        if (!is_file($php)) {
            throw new RuntimeException('PHP executable tidak ditemukan: ' . $php);
        }

        File::ensureDirectoryExists($this->dir());
        $log = $this->dir() . DIRECTORY_SEPARATOR . 'last-run.log';

        $cmd = sprintf(
            'start "" /B /D %s %s %s audit:sync --only=%s > %s 2>&1',
            escapeshellarg(base_path()),
            escapeshellarg($php),
            escapeshellarg($artisan),
            escapeshellarg($only),
            escapeshellarg($log)
        );

        // Pastikan variabel dasar Windows tersedia untuk proses anak
        foreach ($this->childEnv() as $k => $v) {
            putenv("$k=$v");
        }

        $handle = popen($cmd, 'r');
        if ($handle === false) {
            throw new RuntimeException('Gagal menjalankan proses audit di background.');
        }
        pclose($handle);
    }

    private function childEnv(): array
    {
        $want = ['systemroot', 'windir', 'path', 'pathext', 'comspec', 'temp', 'tmp'];
        $env = [];
        foreach (getenv() as $key => $value) {
            if (in_array(strtolower((string) $key), $want, true) && $value !== '' && $value !== false) {
                $env[$key] = $value;
            }
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $lower = array_change_key_case($env, CASE_LOWER);
            if (!isset($lower['systemroot'])) {
                $env['SystemRoot'] = 'C:\\Windows';
            }
            if (!isset($lower['windir'])) {
                $env['WINDIR'] = $lower['systemroot'] ?? 'C:\\Windows';
            }
        }

        return $env;
    }

    public function syncFile(Request $request): JsonResponse
    {
        $this->guard();

        $raw = (string) $request->input('file', '');
        $file = $this->safeAppFile($raw);
        if ($file === null) {
            return response()->json([
                'ok' => false,
                'message' => "File tidak bisa dicek ulang (hanya file PHP di app/): $raw",
            ], 422);
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
        $process = new Process($args, base_path(), $this->childEnv(), null, 300);
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

    public function syncFiles(Request $request): JsonResponse
    {
        $this->guard();

        $items = array_slice((array) $request->input('items', []), 0, 500);

        $files = [];
        foreach ($items as $it) {
            if ($file = $this->safeAppFile($it['file'] ?? '')) {
                $files[$file] = true;
            }
        }
        if (!$files) {
            return response()->json(['ok' => false, 'message' => 'Tidak ada file valid yang dipilih'], 422);
        }

        Artisan::call('audit:code', [
            '--file' => array_keys($files),
            '--skip-syntax' => true,
        ]);

        $payload = $this->payload();
        $all = collect($payload['code']['issues'] ?? []);

        $results = [];
        foreach ($items as $it) {
            $file = $this->safeAppFile($it['file'] ?? '');
            $still = $file === null || $this->stillThere(
                $all->where('file', $file)->values()->all(),
                $it['line'] ?? null,
                (string) ($it['type'] ?? ''),
                (string) ($it['message'] ?? '')
            );
            $results[] = ['file' => $file, 'fixed' => !$still];
        }

        return response()->json(['ok' => true, 'results' => $results, 'code' => $payload['code']]);
    }

    public function syncFeatures(Request $request): JsonResponse
    {
        $this->guard();
        @set_time_limit(0);

        $items = array_slice((array) $request->input('items', []), 0, 200);

        $targets = [];
        foreach ($items as $it) {
            $route = trim((string) ($it['route'] ?? ''));
            $feature = trim((string) ($it['feature'] ?? ''));
            if ($route !== '') {
                $targets['r:' . $route] = '--route=' . $route;
            } elseif ($feature !== '') {
                $targets['f:' . $feature] = '--only=' . $feature;
            }
        }
        if (!$targets) {
            return response()->json(['ok' => false, 'message' => 'route atau feature wajib'], 422);
        }

        $php = (new PhpExecutableFinder())->find(false) ?: 'php';
        $startedAt = time() - 1;
        $lastOutput = '';

        foreach ($targets as $arg) {
            $process = new Process([$php, 'artisan', 'audit:features', $arg], base_path(), $this->childEnv(), null, 300);
            $process->run();
            $lastOutput = trim($process->getErrorOutput() ?: $process->getOutput());
        }

        $resultFile = $this->dir() . '/features.json';
        if (!is_file($resultFile) || filemtime($resultFile) < $startedAt) {
            return response()->json([
                'ok' => false,
                'message' => 'Audit fitur gagal: ' . mb_substr($lastOutput, -500),
            ], 500);
        }

        $payload = $this->payload();
        $all = collect($payload['features']['issues'] ?? []);

        $results = [];
        foreach ($items as $it) {
            $route = trim((string) ($it['route'] ?? ''));
            $feature = trim((string) ($it['feature'] ?? ''));
            $scoped = $route !== '' ? $all->where('route', $route) : $all->where('feature', $feature);

            $still = $this->stillThere(
                $scoped->values()->all(),
                $it['line'] ?? null,
                (string) ($it['type'] ?? ''),
                (string) ($it['message'] ?? '')
            );
            $results[] = ['route' => $route, 'feature' => $feature, 'fixed' => !$still];
        }

        return response()->json(['ok' => true, 'results' => $results, 'features' => $payload['features']]);
    }

    public function solution(Request $request): JsonResponse
    {
        $this->guard();
        $tab = (string) $request->input('tab', '');
        if (!in_array($tab, ['code', 'js', 'assets', 'features'], true)) {
            return response()->json(['ok' => false, 'message' => 'Tab tidak valid'], 422);
        }

        return response()->json(
            ['ok' => true, 'solution' => app(AuditSolver::class)->solve($tab, (array) $request->input('issue', []))],
            200, [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE
        );
    }

    public function solutionFeedback(Request $request): JsonResponse
    {
        $this->guard();
        app(AuditSolver::class)->feedback((string) $request->input('rule', ''), (string) $request->input('verdict', ''));

        return response()->json(['ok' => true]);
    }

    private function safeAppFile($file): ?string
    {
        $file = str_replace('\\', '/', trim((string) $file));
        $base = str_replace('\\', '/', rtrim(base_path(), '/\\')) . '/';

        // path absolut proyek -> relatif
        if (str_starts_with($file, $base)) {
            $file = substr($file, strlen($base));
        }

        $file = ltrim($file, '/');
        if ($file === '' || str_contains($file, '..') || !str_starts_with($file, 'app/')) {
            return null;
        }

        $absolute = base_path($file);

        return is_file($absolute) && pathinfo($absolute, PATHINFO_EXTENSION) === 'php' ? $file : null;
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