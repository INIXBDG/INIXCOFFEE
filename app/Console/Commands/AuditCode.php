<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use Throwable;

/**
 * Letakkan di: app/Console/Commands/AuditCode.php
 *
 * Jalankan manual : php artisan audit:code
 * Lewati cek syntax: php artisan audit:code --skip-syntax
 */
class AuditCode extends Command
{
    protected $signature = 'audit:code {--skip-syntax : Lewati pengecekan php -l}';
    protected $description = 'Cek route, nama class/model (huruf besar-kecil), dan controller; tampilkan file + baris kesalahan';

    private const BUILTIN = [
        'int', 'float', 'string', 'bool', 'array', 'callable', 'iterable', 'object',
        'mixed', 'void', 'null', 'never', 'self', 'static', 'parent', 'false', 'true',
    ];

    private array $issues = [];
    private array $classMap = [];      // fqcn lowercase => fqcn asli (sesuai nama file)
    private array $routedMethods = []; // "class::method" lowercase => true
    private array $seen = [];

    public function handle(): int
    {
        $this->buildClassMap();

        foreach (File::allFiles(app_path()) as $file) {
            if ($file->getExtension() === 'php') {
                $this->checkFile($file->getPathname());
            }
        }

        $this->checkRoutes();
        $this->checkControllers();
        $this->report();

        return collect($this->issues)->contains('level', 'ERROR') ? self::FAILURE : self::SUCCESS;
    }

    /* ------------------------------------------------------------------ */
    /*  Util                                                               */
    /* ------------------------------------------------------------------ */

    private function add(string $level, string $type, string $path, int $line, string $message): void
    {
        $rel = str_replace([base_path() . DIRECTORY_SEPARATOR, '\\'], ['', '/'], $path);
        $key = "$level|$type|$rel|$line|$message";
        if (isset($this->seen[$key])) {
            return;
        }
        $this->seen[$key] = true;
        $this->issues[] = compact('level', 'type', 'rel', 'line', 'message');
    }

    private function buildClassMap(): void
    {
        foreach (File::allFiles(app_path()) as $f) {
            if ($f->getExtension() !== 'php') {
                continue;
            }
            $rel = substr($f->getRelativePathname(), 0, -4);
            $fqcn = 'App\\' . str_replace(['/', DIRECTORY_SEPARATOR], '\\', $rel);
            $this->classMap[strtolower($fqcn)] = $fqcn;
        }
    }

    private function typeExists(string $fqcn): ?string
    {
        $fqcn = ltrim($fqcn, '\\');
        try {
            if (class_exists($fqcn) || interface_exists($fqcn) || trait_exists($fqcn) || enum_exists($fqcn)) {
                return (new ReflectionClass($fqcn))->getName();
            }
        } catch (Throwable $e) {
        }
        return null;
    }

    private function clean(string $code, bool $stripStrings): string
    {
        $out = '';
        foreach (token_get_all($code) as $t) {
            if (!is_array($t)) {
                $out .= $t;
                continue;
            }
            [$id, $text] = $t;
            $nl = str_repeat("\n", substr_count($text, "\n"));
            if (in_array($id, [T_COMMENT, T_DOC_COMMENT, T_INLINE_HTML], true)) {
                $out .= $nl;
            } elseif ($stripStrings && $id === T_CONSTANT_ENCAPSED_STRING) {
                $out .= "''" . $nl;
            } elseif ($stripStrings && $id === T_ENCAPSED_AND_WHITESPACE) {
                $out .= $nl;
            } else {
                $out .= $text;
            }
        }
        return $out;
    }

    private function lineAt(string $text, int $offset): int
    {
        return substr_count(substr($text, 0, $offset), "\n") + 1;
    }

    /* ------------------------------------------------------------------ */
    /*  1. Cek per file di app/ : syntax, import, nama class, view, route  */
    /* ------------------------------------------------------------------ */

    private function checkFile(string $path): void
    {
        if (!$this->option('skip-syntax')) {
            exec('php -l ' . escapeshellarg($path) . ' 2>&1', $out, $code);
            if ($code !== 0) {
                $msg = trim(implode(' ', $out));
                preg_match('/on line (\d+)/', $msg, $m);
                $this->add('ERROR', 'Syntax', $path, (int) ($m[1] ?? 0), $msg);
                return;
            }
            unset($out);
        }

        $raw = file_get_contents($path);
        $code = $this->clean($raw, true);          // tanpa komentar & isi string
        $noComment = $this->clean($raw, false);    // tanpa komentar saja (untuk view/route)

        $ns = preg_match('/^\s*namespace\s+([^;{\s]+)/m', $code, $m) ? $m[1] : '';
        $classPos = preg_match('/\b(?:class|trait|interface|enum)\s+\w+/', $code, $m, PREG_OFFSET_CAPTURE)
            ? $m[0][1] : strlen($code);

        // ---- import (use ...;) ----
        $imports = []; // alias lowercase => [alias, fqcn]
        $head = substr($code, 0, $classPos);
        preg_match_all('/^\s*use\s+([^;]+);/m', $head, $uses, PREG_OFFSET_CAPTURE);
        foreach ($uses[1] as [$text, $off]) {
            $spec = trim($text);
            $line = $this->lineAt($code, $off);
            if (preg_match('/^(function|const)\s/i', $spec)) {
                continue;
            }
            if (preg_match('/^(.+?)\\\\\{(.+)\}$/s', $spec, $g)) {
                foreach (explode(',', $g[2]) as $item) {
                    $this->registerImport(trim($g[1]) . '\\' . trim($item), $path, $line, $imports);
                }
            } else {
                foreach (explode(',', $spec) as $item) {
                    $this->registerImport(trim($item), $path, $line, $imports);
                }
            }
        }

        // ---- kumpulkan nama class yang dipakai: [nama, offset] ----
        $refs = [];
        $collect = function (string $regex, int $group = 1) use ($code, &$refs) {
            preg_match_all($regex, $code, $mm, PREG_OFFSET_CAPTURE);
            foreach ($mm[$group] as [$text, $off]) {
                $refs[] = [$text, $off];
            }
        };

        $collect('/(?<![\w\\\\$>])([A-Za-z_]\w*)::/');                 // User::find, user::find
        $collect('/\bnew\s+([A-Za-z_]\w*)/');                           // new User
        $collect('/\binstanceof\s+([A-Za-z_]\w*)/');
        $collect('/\bpublic\s+(?:static\s+|readonly\s+)*\??([A-Za-z_]\w*)\s+\$/');
        $collect('/\bprotected\s+(?:static\s+|readonly\s+)*\??([A-Za-z_]\w*)\s+\$/');
        $collect('/\bprivate\s+(?:static\s+|readonly\s+)*\??([A-Za-z_]\w*)\s+\$/');

        // extends / implements
        preg_match_all('/\b(?:extends|implements)\s+([A-Za-z_\\\\][\w\\\\,\s]*?)\s*\{/s', $code, $mm, PREG_OFFSET_CAPTURE);
        foreach ($mm[1] as [$text, $off]) {
            foreach (explode(',', $text) as $n) {
                $n = trim($n);
                if ($n !== '' && !str_contains($n, '\\')) {
                    $refs[] = [$n, $off];
                }
            }
        }

        // catch (A | B $e)
        preg_match_all('/\bcatch\s*\(\s*([A-Za-z_\\\\|\s]+?)\s*(?:\$\w+)?\s*\)/', $code, $mm, PREG_OFFSET_CAPTURE);
        foreach ($mm[1] as [$text, $off]) {
            foreach (explode('|', $text) as $n) {
                $n = trim($n);
                if ($n !== '' && !str_contains($n, '\\')) {
                    $refs[] = [$n, $off];
                }
            }
        }

        // parameter & return type function / fn
        preg_match_all('/\b(?:function\s*&?\s*\w*|fn)\s*\(([^)]*)\)\s*(?::\s*\??([A-Za-z_]\w*))?/', $code, $mm, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($mm as $set) {
            $off = $set[0][1];
            foreach (explode(',', $set[1][0]) as $p) {
                if (preg_match('/^\s*(?:(?:public|protected|private|readonly)\s+)*\??([A-Za-z_]\w*(?:\|[A-Za-z_]\w*)*)\s+&?(?:\.\.\.)?\$/', $p, $pm)) {
                    foreach (explode('|', $pm[1]) as $n) {
                        $refs[] = [$n, $off];
                    }
                }
            }
            if (!empty($set[2][0])) {
                $refs[] = [$set[2][0], $off];
            }
        }

        // trait: use TraitA, TraitB;  (di dalam class)
        $body = substr($code, $classPos);
        preg_match_all('/^\s*use\s+([^;{(]+)[;{]/m', $body, $mm, PREG_OFFSET_CAPTURE);
        foreach ($mm[1] as [$text, $off]) {
            foreach (explode(',', $text) as $n) {
                $n = trim($n);
                if ($n !== '' && !str_contains($n, '\\')) {
                    $refs[] = [$n, $classPos + $off];
                }
            }
        }

        foreach ($refs as [$name, $off]) {
            $this->resolveShort($name, $ns, $imports, $path, $this->lineAt($code, $off));
        }

        // ---- view('...') & route('...') ----
        preg_match_all('/\bview\(\s*[\'"]([^\'"$\{:]+)[\'"]/', $noComment, $mm, PREG_OFFSET_CAPTURE);
        foreach ($mm[1] as [$name, $off]) {
            try {
                if (!view()->exists($name)) {
                    $this->add('ERROR', 'View', $path, $this->lineAt($noComment, $off), "View '$name' tidak ditemukan");
                }
            } catch (Throwable $e) {
            }
        }

        preg_match_all('/(?:(?<![\w>$])route|redirect\(\)->route|to_route)\(\s*[\'"]([^\'"$\{]+)[\'"]/', $noComment, $mm, PREG_OFFSET_CAPTURE);
        foreach ($mm[1] as [$name, $off]) {
            if (!Route::has($name)) {
                $this->add('ERROR', 'Route name', $path, $this->lineAt($noComment, $off), "Route bernama '$name' tidak terdaftar");
            }
        }
    }

    private function registerImport(string $item, string $path, int $line, array &$imports): void
    {
        if (!preg_match('/^(\\\\?[\w\\\\]+)(?:\s+as\s+(\w+))?$/i', $item, $m)) {
            return;
        }
        $fqcn = ltrim($m[1], '\\');
        $alias = $m[2] ?? substr(strrchr('\\' . $fqcn, '\\'), 1);
        $imports[strtolower($alias)] = [$alias, $fqcn];

        if (str_starts_with($fqcn, 'App\\')) {
            $actual = $this->classMap[strtolower($fqcn)] ?? null;
            if ($actual === null) {
                $this->add('ERROR', 'Import', $path, $line, "Class '$fqcn' tidak ditemukan di app/");
            } elseif ($actual !== $fqcn) {
                $this->add('ERROR', 'Huruf berbeda', $path, $line, "Ditulis '$fqcn', seharusnya '$actual'");
            }
        } else {
            $actual = $this->typeExists($fqcn);
            if ($actual === null) {
                $this->add('ERROR', 'Import', $path, $line, "Class '$fqcn' tidak ditemukan (cek namespace / composer)");
            } elseif ($actual !== $fqcn && strcasecmp($actual, $fqcn) === 0) {
                $this->add('ERROR', 'Huruf berbeda', $path, $line, "Ditulis '$fqcn', seharusnya '$actual'");
            }
        }
    }

    private function resolveShort(string $name, string $ns, array $imports, string $path, int $line): void
    {
        $low = strtolower($name);
        if (in_array($low, self::BUILTIN, true)) {
            return;
        }

        // 1) sudah di-import
        if (isset($imports[$low])) {
            if ($imports[$low][0] !== $name) {
                $this->add('ERROR', 'Huruf berbeda', $path, $line,
                    "Memanggil '$name' padahal di-import sebagai '{$imports[$low][0]}'");
            }
            return;
        }

        // 2) satu namespace dengan file ini
        $fqcn = $ns !== '' ? "$ns\\$name" : $name;
        if (isset($this->classMap[strtolower($fqcn)])) {
            $actual = $this->classMap[strtolower($fqcn)];
            if ($actual !== $fqcn) {
                $this->add('ERROR', 'Huruf berbeda', $path, $line, "Memanggil '$name', seharusnya '" . substr(strrchr('\\' . $actual, '\\'), 1) . "'");
            }
            return;
        }

        // 3) file tanpa namespace -> class global
        if ($ns === '' && $this->typeExists($name) !== null) {
            return;
        }

        $hint = $this->typeExists($name) !== null ? " (class global, tambahkan 'use \\$name;')" : '';
        $this->add('ERROR', 'Class tidak ditemukan', $path, $line, "'$name' belum di-import atau tidak ada$hint");
    }

    /* ------------------------------------------------------------------ */
    /*  2. Cek route                                                       */
    /* ------------------------------------------------------------------ */

    private function checkRoutes(): void
    {
        $router = app('router');
        $aliases = $router->getMiddleware();
        $groups = $router->getMiddlewareGroups();
        $names = [];
        $signatures = [];

        foreach (Route::getRoutes() as $route) {
            $uri = '/' . ltrim($route->uri(), '/');
            $verbs = implode('|', array_diff($route->methods(), ['HEAD']));

            // duplikat
            $sig = "$verbs $uri";
            if (isset($signatures[$sig])) {
                [$f, $l] = $this->locateInRoutes([$route->uri()]);
                $this->add('WARNING', 'Route duplikat', $f, $l, "$sig terdaftar lebih dari sekali");
            }
            $signatures[$sig] = true;

            if ($name = $route->getName()) {
                if (isset($names[$name])) {
                    [$f, $l] = $this->locateInRoutes(["'$name'", "\"$name\""]);
                    $this->add('ERROR', 'Nama route duplikat', $f, $l, "Nama '$name' dipakai lebih dari satu route");
                }
                $names[$name] = true;
            }

            // controller & method
            $action = $route->getAction();
            if (isset($action['controller'])) {
                $class = $route->getControllerClass();
                $method = $route->getActionMethod();
                $short = class_basename((string) $class);
                [$f, $l] = $this->locateInRoutes([$short . '::class', $short . '@', "'$short'", $route->uri()]);

                $exists = str_starts_with((string) $class, 'App\\')
                    ? (($this->classMap[strtolower($class)] ?? null) === $class && $this->typeExists($class))
                    : $this->typeExists((string) $class);

                if (!$exists) {
                    $this->add('ERROR', 'Route -> Controller', $f, $l, "$sig memanggil controller '$class' yang tidak ada / huruf berbeda");
                } else {
                    $this->routedMethods[strtolower("$class::$method")] = true;
                    if (!method_exists($class, $method)) {
                        $this->add('ERROR', 'Route -> Method', $f, $l, "$sig memanggil method '$class::$method()' yang tidak ada");
                    }
                }
            }

            // middleware
            try {
                foreach ($route->gatherMiddleware() as $mw) {
                    if (!is_string($mw)) {
                        continue;
                    }
                    $base = explode(':', $mw)[0];
                    if (!isset($aliases[$base]) && !isset($groups[$base]) && !class_exists($base)) {
                        [$f, $l] = $this->locateInRoutes(["'$base", "\"$base", $route->uri()]);
                        $this->add('ERROR', 'Middleware', $f, $l, "$sig memakai middleware '$base' yang tidak terdaftar");
                    }
                }
            } catch (Throwable $e) {
            }
        }
    }

    private function locateInRoutes(array $needles): array
    {
        foreach ($needles as $needle) {
            foreach (glob(base_path('routes/*.php')) as $file) {
                foreach (file($file) as $i => $text) {
                    if (str_contains($text, $needle) && !str_starts_with(trim($text), '//')) {
                        return [$file, $i + 1];
                    }
                }
            }
        }
        return [base_path('routes/web.php'), 0];
    }

    /* ------------------------------------------------------------------ */
    /*  3. Cek controller                                                  */
    /* ------------------------------------------------------------------ */

    private function checkControllers(): void
    {
        $dir = app_path('Http/Controllers');
        if (!is_dir($dir)) {
            return;
        }

        foreach (File::allFiles($dir) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $path = $file->getPathname();
            $code = $this->clean(file_get_contents($path), true);
            $base = $file->getBasename('.php');

            // nama class harus = nama file
            if (preg_match('/^\s*(?:abstract\s+|final\s+)?class\s+(\w+)/m', $code, $m, PREG_OFFSET_CAPTURE)) {
                if ($m[1][0] !== $base) {
                    $this->add('ERROR', 'Nama class != nama file', $path, $this->lineAt($code, $m[0][1]),
                        "Class '{$m[1][0]}' di file '$base.php'");
                }
            } else {
                $this->add('ERROR', 'Controller', $path, 1, 'Tidak ada deklarasi class');
                continue;
            }

            $rel = substr($file->getRelativePathname(), 0, -4);
            $fqcn = 'App\\Http\\Controllers\\' . str_replace(['/', DIRECTORY_SEPARATOR], '\\', $rel);

            $actual = $this->typeExists($fqcn);
            if ($actual === null) {
                $this->add('ERROR', 'Namespace', $path, 1, "Class '$fqcn' tidak bisa di-load (namespace tidak sesuai folder?)");
                continue;
            }

            // method public yang tidak punya route
            $ref = new ReflectionClass($actual);
            if ($ref->isAbstract() || $base === 'Controller') {
                continue;
            }
            foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                if ($method->getDeclaringClass()->getName() !== $actual
                    || $method->isStatic()
                    || str_starts_with($method->getName(), '__') && $method->getName() !== '__invoke') {
                    continue;
                }
                if (!isset($this->routedMethods[strtolower($actual . '::' . $method->getName())])) {
                    $this->add('WARNING', 'Method tanpa route', $path, $method->getStartLine(),
                        "{$base}::{$method->getName()}() tidak dipanggil oleh route manapun");
                }
            }
        }
    }

    /* ------------------------------------------------------------------ */
    /*  Laporan                                                            */
    /* ------------------------------------------------------------------ */

    private function report(): void
    {
        usort($this->issues, fn ($a, $b) => [$a['rel'], $a['line']] <=> [$b['rel'], $b['line']]);

        if (!$this->issues) {
            $this->info('Tidak ada masalah ditemukan.');
            return;
        }

        $this->table(
            ['Level', 'Jenis', 'File:Baris', 'Pesan'],
            array_map(fn ($i) => [$i['level'], $i['type'], $i['rel'] . ':' . $i['line'], $i['message']], $this->issues)
        );

        $errors = count(array_filter($this->issues, fn ($i) => $i['level'] === 'ERROR'));
        $warnings = count($this->issues) - $errors;
        $this->error("Total: $errors error, $warnings warning");

        $log = '[' . now()->toDateTimeString() . "] audit:code - $errors error, $warnings warning\n";
        foreach ($this->issues as $i) {
            $log .= sprintf("  [%s] %s | %s:%d | %s\n", $i['level'], $i['type'], $i['rel'], $i['line'], $i['message']);
        }
        File::put(storage_path('logs/audit-code-' . now()->format('Y-m-d') . '.log'), $log);

        if ($errors > 0) {
            Log::error("audit:code menemukan $errors error. Lihat storage/logs/audit-code-" . now()->format('Y-m-d') . '.log');
        }
    }
}