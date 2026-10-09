<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Letakkan di: app/Console/Commands/AuditFeatures.php
 *
 * Setiap route (tambah, edit, hapus, baca, export, dll) diperiksa sepanjang rantai:
 *   Blade (view ada + form mengarah ke route) -> Route -> Controller@method -> Model/Tabel database
 * Jika satu saja langkah gagal, fitur itu berstatus ERROR.
 *
 * Analisis bersifat statis (tidak menulis data). Request langsung hanya untuk halaman GET yang aman,
 * dijalankan di dalam transaksi yang selalu di-rollback.
 */
class AuditFeatures extends Command
{
    protected $signature = 'audit:features
        {--only= : Fitur (segmen URI pertama) atau nama route, pisahkan koma}
        {--route= : Satu nama route / URI}
        {--user= : ID user login untuk uji request GET}
        {--no-request : Lewati uji request GET (hanya analisis statis)}';

    protected $description = 'Audit rantai fitur: Blade -> Route -> Controller -> Model/Database untuk semua fitur';

    private const SKIP_URI = '/^(_|telescope|horizon|livewire|storage|sanctum|pulse|up$)/i';
    private const DANGEROUS_GET = '/logout|signout|delete|destroy|hapus|remove|clear|reset|sync|approve|reject|toggle|generate|migrate|seed|backup|restore|send|cron|truncate|run/i';
    private const LAYER_LABEL = [
        'blade' => 'Blade',
        'route' => 'Route',
        'controller' => 'Controller',
        'model' => 'Model & Database',
        'request' => 'Request',
    ];

    private array $issues = [];
    private array $checks = [];
    private array $info = [];
    private array $routeFiles = [];
    private array $importCache = [];

    public function handle(): int
    {
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

        $kernel = $this->prepareKernel();

        foreach ($flows as $flow) {
            $this->checks = [];
            $this->info = ['blade' => null, 'controller' => null, 'model' => null, 'table' => null];
            try {
                $this->auditFlow($flow, $kernel);
            } catch (Throwable $e) {
                $this->chk('controller', 'error', 'Audit internal gagal dijalankan', get_class($e) . ': ' . $e->getMessage());
                $this->pushFlow($flow);
            }
        }

        $this->writeJson($this->issues, $partial ? $flows : null);

        $errors = count(array_filter($this->issues, fn ($i) => $i['level'] === 'ERROR'));
        $oks = count($this->issues) - $errors;
        $this->info("audit:features → $oks ok, $errors error");

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function prepareKernel(): ?HttpKernel
    {
        if ($this->option('no-request')) {
            return null;
        }
        $uid = $this->option('user') ?: config('audit.user_id');
        if (!$uid) {
            $this->warn('AUDIT_USER_ID belum di-set: uji request GET dilewati, hanya analisis statis.');
            return null;
        }
        Auth::loginUsingId((int) $uid);
        if (!Auth::check()) {
            $this->warn("Gagal login user ID $uid: uji request GET dilewati.");
            return null;
        }
        return app(HttpKernel::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Daftar alur (satu route = satu alur)                               */
    /* ------------------------------------------------------------------ */

    private function buildFlows(array $only, string $onlyRoute): array
    {
        $flows = [];

        foreach (Route::getRoutes() as $route) {
            $methods = array_values(array_diff($route->methods(), ['HEAD', 'OPTIONS']));
            if (!$methods) {
                continue;
            }
            $uri = trim($route->uri(), '/');
            if (preg_match(self::SKIP_URI, $uri)) {
                continue;
            }
            $name = (string) $route->getName();
            $feature = strtolower(explode('/', $uri)[0] ?: 'root');

            if ($only && !in_array($feature, $only, true) && !in_array($name, $only, true)) {
                continue;
            }
            if ($onlyRoute !== '' && $name !== $onlyRoute && $uri !== trim($onlyRoute, '/')) {
                continue;
            }

            $action = $route->getAction();
            $closure = ($action['uses'] ?? null) instanceof \Closure;
            $class = null;
            $method = null;
            if (!$closure && isset($action['controller'])) {
                try {
                    $class = $route->getControllerClass();
                    $method = $route->getActionMethod();
                } catch (Throwable $e) {
                }
            }
            // route bawaan package (vendor) tidak diaudit
            if ($class && !str_starts_with($class, 'App\\')) {
                continue;
            }

            $flows[] = [
                'feature' => $feature,
                'function' => $this->classify((string) $method, $name, $methods, $uri),
                'method' => $methods[0],
                'methods' => $methods,
                'uri' => $uri,
                'name' => $name,
                'closure' => $closure,
                'class' => $class,
                'action' => $method,
                'route' => $route,
            ];
        }

        return $flows;
    }

    private function classify(string $method, string $name, array $methods, string $uri): string
    {
        $m = strtolower($method);
        $tail = strtolower(substr(strrchr('.' . $name, '.'), 1));
        $key = "$m|$tail";
        $hay = "$m $name $uri";

        if (preg_match('/export|download|print|pdf|excel|csv|unduh|cetak/i', $hay)) {
            return 'export';
        }
        if (in_array('DELETE', $methods, true) || preg_match('/destroy|delete|hapus|remove/', $key)) {
            return 'hapus';
        }
        if (preg_match('/(^|\|)(store|save|simpan|insert)/', $key)) {
            return 'tambah_simpan';
        }
        if (array_intersect($methods, ['PUT', 'PATCH']) || preg_match('/(^|\|)update/', $key)) {
            return 'edit_simpan';
        }
        if (preg_match('/(^|\|)(create|tambah|add)/', $key)) {
            return 'tambah_form';
        }
        if (preg_match('/(^|\|)edit/', $key)) {
            return 'edit_form';
        }
        if (preg_match('/(^|\|)(show|detail|view)/', $key)) {
            return 'baca_detail';
        }
        if (in_array('GET', $methods, true)) {
            return 'baca_daftar';
        }
        return 'aksi_lainnya';
    }

    /* ------------------------------------------------------------------ */
    /*  Audit satu alur                                                    */
    /* ------------------------------------------------------------------ */

    private function auditFlow(array $f, ?HttpKernel $kernel): void
    {
        $this->checkRoute($f);
        $ref = $this->checkController($f);
        $this->checkBlade($f, $ref);
        $this->checkModel($f, $ref);
        $this->checkRequest($f, $kernel);
        $this->pushFlow($f);
    }

    private function chk(string $layer, string $status, string $title, string $detail = '', ?string $file = null, int $line = 0): void
    {
        $this->checks[] = [
            'layer' => $layer,
            'status' => $status, // ok | error | warn | skip
            'title' => $title,
            'detail' => $detail,
            'file' => $file,
            'line' => $line,
        ];
    }

    /* ---- Route ------------------------------------------------------- */

    private function checkRoute(array $f): void
    {
        [$file, $line] = $this->routeLocation($f);
        $sig = implode('|', $f['methods']) . ' /' . $f['uri'];

        $this->chk('route', 'ok', 'Route terdaftar', $sig . ($f['name'] !== '' ? "  (nama: {$f['name']})" : ''), $file, $line);

        if ($f['name'] === '') {
            $this->chk('route', 'warn', 'Route tidak punya nama', 'Tambahkan ->name(...) agar bisa dipakai di Blade dan redirect.', $file, $line);
        }

        try {
            $router = app('router');
            $aliases = $router->getMiddleware();
            $groups = $router->getMiddlewareGroups();
            $bad = [];
            foreach ($f['route']->gatherMiddleware() as $mw) {
                if (!is_string($mw)) {
                    continue;
                }
                $base = explode(':', $mw)[0];
                if (!isset($aliases[$base]) && !isset($groups[$base]) && !class_exists($base)) {
                    $bad[] = $base;
                }
            }
            if ($bad) {
                $this->chk('route', 'error', 'Middleware tidak terdaftar', implode(', ', $bad), $file, $line);
            } else {
                $this->chk('route', 'ok', 'Middleware valid', '', $file, $line);
            }
        } catch (Throwable $e) {
        }
    }

    private function routeLocation(array $f): array
    {
        if (!$this->routeFiles) {
            foreach (glob(base_path('routes/*.php')) ?: [] as $path) {
                $this->routeFiles[$path] = file($path) ?: [];
            }
        }

        $needles = [];
        if ($f['class'] && $f['action']) {
            $short = class_basename($f['class']);
            $needles[] = "$short::class, '{$f['action']}'";
            $needles[] = "$short@{$f['action']}";
        }
        if ($f['name'] !== '') {
            $needles[] = "'{$f['name']}'";
            $needles[] = "\"{$f['name']}\"";
        }
        $static = rtrim((string) strstr($f['uri'] . '{', '{', true), '/');
        $last = $static !== '' ? basename($static) : '';
        if ($last !== '') {
            $needles[] = "'$last'";
            $needles[] = "'/$last'";
            $needles[] = "\"$last\"";
        }

        foreach ($needles as $needle) {
            foreach ($this->routeFiles as $path => $lines) {
                foreach ($lines as $i => $text) {
                    if (str_contains($text, $needle) && !str_starts_with(trim($text), '//')) {
                        return [$this->toRel($path), $i + 1];
                    }
                }
            }
        }
        return [null, 0];
    }

    /* ---- Controller -------------------------------------------------- */

    private function checkController(array $f): ?ReflectionMethod
    {
        [$file, $line] = $this->routeLocation($f);

        if ($f['closure'] || !$f['class']) {
            $this->chk('controller', 'skip', 'Route memakai Closure', 'Tidak ada controller yang bisa diperiksa.', $file, $line);
            return null;
        }

        $class = $f['class'];
        $method = $f['action'] ?: '__invoke';

        if (!class_exists($class)) {
            $this->chk('controller', 'error', 'Controller tidak ditemukan', "'$class' tidak ada atau huruf besar-kecilnya berbeda.", $file, $line);
            return null;
        }
        $actual = (new ReflectionClass($class))->getName();
        if ($actual !== $class) {
            $this->chk('controller', 'error', 'Huruf class berbeda', "Route menulis '$class', seharusnya '$actual'.", $file, $line);
        }
        if (!method_exists($class, $method)) {
            $this->chk('controller', 'error', 'Method tidak ada', "{$class}::{$method}() tidak ditemukan.", $file, $line);
            return null;
        }

        $ref = new ReflectionMethod($class, $method);
        $this->info['controller'] = class_basename($class) . '@' . $method;
        $this->chk('controller', 'ok', 'Controller dan method ada', "{$class}@{$method}", $this->toRel((string) $ref->getFileName()), (int) $ref->getStartLine());

        // Class Export (Maatwebsite dsb.) harus ada
        if ($f['function'] === 'export') {
            $src = $this->src($ref);
            if (preg_match_all('/\bnew\s+(\\\\?[A-Za-z_][\w\\\\]*Export\w*)/', $src, $m)) {
                $imports = $this->importsOf((string) $ref->getFileName());
                $ns = $ref->getDeclaringClass()->getNamespaceName();
                foreach (array_unique($m[1]) as $n) {
                    $fqcn = $this->resolveName($n, $ns, $imports);
                    if (class_exists($fqcn)) {
                        $this->chk('controller', 'ok', 'Class export ada', $fqcn, $this->toRel((string) $ref->getFileName()), (int) $ref->getStartLine());
                    } else {
                        $this->chk('controller', 'error', 'Class export tidak ditemukan', $fqcn, $this->toRel((string) $ref->getFileName()), (int) $ref->getStartLine());
                    }
                }
            }
        }

        return $ref;
    }

    /* ---- Blade ------------------------------------------------------- */

    private function checkBlade(array $f, ?ReflectionMethod $ref): void
    {
        $fn = $f['function'];

        if (!$ref) {
            $this->chk('blade', 'skip', 'Dilewati', 'Controller tidak tersedia.');
            return;
        }
        if ($fn === 'hapus') {
            $this->chk('blade', 'skip', 'Aksi tanpa halaman', 'Hapus tidak memiliki file Blade sendiri.');
            return;
        }

        $isSubmit = in_array($fn, ['tambah_simpan', 'edit_simpan'], true);
        $views = [];

        if ($isSubmit) {
            $formMethod = $fn === 'tambah_simpan' ? 'create' : 'edit';
            if (method_exists($f['class'], $formMethod)) {
                $views = $this->viewsOfSource($this->src(new ReflectionMethod($f['class'], $formMethod)));
            }
            if (!$views) {
                $this->chk('blade', 'skip', 'Halaman form tidak ditemukan', "Tidak ada method {$formMethod}() yang memanggil view(); form tidak bisa diperiksa.");
                return;
            }
        } else {
            $views = $this->viewsOfSource($this->src($ref));
            if (!$views) {
                $this->chk('blade', 'skip', 'Tidak memanggil view()', 'Method mengembalikan redirect/JSON/file, bukan halaman Blade.');
                return;
            }
        }

        $existing = [];
        foreach ($views as $name) {
            $path = $this->viewPath($name);
            if ($path === null || !is_file($path)) {
                $this->chk('blade', 'error', 'File Blade tidak ditemukan', "view('$name') tidak ada di resources/views.");
            } else {
                $existing[] = $name;
                $this->chk('blade', 'ok', 'File Blade ada', "$name → " . $this->toRel($path), $this->toRel($path), 1);
            }
        }
        $this->info['blade'] = implode(', ', $views);

        if ($isSubmit && $existing) {
            $text = '';
            foreach ($existing as $name) {
                $text .= "\n" . $this->viewText($name, 0);
            }
            $firstPath = $this->toRel((string) $this->viewPath($existing[0]));

            $needles = [];
            if ($f['name'] !== '') {
                $needles[] = "route('{$f['name']}'";
                $needles[] = "route(\"{$f['name']}\"";
            }
            $static = rtrim((string) strstr($f['uri'] . '{', '{', true), '/');
            if ($static !== '') {
                $needles[] = "/$static";
                $needles[] = "'$static'";
                $needles[] = "\"$static\"";
            }
            $found = false;
            foreach ($needles as $n) {
                if (str_contains($text, $n)) {
                    $found = true;
                    break;
                }
            }
            if ($found) {
                $this->chk('blade', 'ok', 'Form mengarah ke route ini', $f['name'] !== '' ? $f['name'] : '/' . $f['uri'], $firstPath, 1);
            } else {
                $this->chk('blade', 'warn', 'Form tidak terlihat memakai route ini', "Tidak ada route('{$f['name']}') / URL /{$static} di Blade (termasuk @include). Mungkin dikirim lewat JavaScript.", $firstPath, 1);
            }

            if (stripos($text, '<form') !== false
                && !preg_match('/@csrf|csrf_field\(|name=["\']_token|csrf-token|X-CSRF/i', $text)) {
                $this->chk('blade', 'warn', 'Form tanpa @csrf', 'Submit POST berpotensi gagal 419 (Page Expired).', $firstPath, 1);
            }
        }
    }

    private function viewsOfSource(string $src): array
    {
        $names = [];
        if (preg_match_all('/\b(?:view|View::make)\(\s*[\'"]([^\'"$\{]+)[\'"]/', $src, $m)) {
            $names = array_merge($names, $m[1]);
        }
        return array_values(array_unique($names));
    }

    private function viewPath(string $name): ?string
    {
        try {
            return app('view')->getFinder()->find($name);
        } catch (Throwable $e) {
            return null;
        }
    }

    private function viewText(string $name, int $depth): string
    {
        $path = $this->viewPath($name);
        if (!$path || !is_file($path)) {
            return '';
        }
        $text = (string) file_get_contents($path);
        if ($depth < 3 && preg_match_all('/@include(?:If)?\(\s*[\'"]([\w.\-:]+)[\'"]/', $text, $m)) {
            foreach (array_unique($m[1]) as $inc) {
                $text .= "\n" . $this->viewText($inc, $depth + 1);
            }
        }
        return $text;
    }

    /* ---- Model & Database -------------------------------------------- */

    private function checkModel(array $f, ?ReflectionMethod $ref): void
    {
        $fn = $f['function'];
        $isWrite = in_array($fn, ['tambah_simpan', 'edit_simpan', 'hapus'], true);

        if (!$ref) {
            $this->chk('model', 'skip', 'Dilewati', 'Controller tidak tersedia.');
            return;
        }

        $src = $this->src($ref);
        $models = $this->modelsIn($ref, $src);
        $tables = [];
        if (preg_match_all('/DB::table\(\s*[\'"]([\w.]+)[\'"]/', $src, $m)) {
            $tables = array_values(array_unique($m[1]));
        }

        if (!$models && !$tables) {
            if ($isWrite) {
                $this->chk('model', 'warn', 'Model / tabel tidak terdeteksi', 'Method tidak memanggil Model atau DB::table secara langsung (mungkin lewat Service/Repository), jadi penyimpanan ke database tidak bisa diverifikasi.');
            } else {
                $this->chk('model', 'skip', 'Tidak memakai Model', 'Method ini tidak mengakses database secara langsung.');
            }
            return;
        }

        $ctrlFile = $this->toRel((string) $ref->getFileName());
        $ctrlLine = (int) $ref->getStartLine();

        $infoModels = [];
        $infoTables = [];

        foreach ($models as $cls) {
            $rc = new ReflectionClass($cls);
            $file = $this->toRel((string) $rc->getFileName());
            $line = (int) $rc->getStartLine();
            $infoModels[] = class_basename($cls);

            if (!$rc->isInstantiable()) {
                $this->chk('model', 'warn', 'Model tidak bisa diinstansiasi', $cls, $file, $line);
                continue;
            }

            try {
                $m = new $cls();
                $table = $m->getTable();
                $conn = $m->getConnection();
                $schema = $conn->getSchemaBuilder();
                $hasTable = $schema->hasTable($table);
                $dbName = $conn->getDatabaseName();
            } catch (Throwable $e) {
                $this->chk('model', 'error', 'Koneksi database / model gagal', $cls . ': ' . mb_substr($e->getMessage(), 0, 250), $file, $line);
                continue;
            }
            $infoTables[] = $table;

            $this->chk('model', 'ok', 'Model valid', $cls, $file, $line);

            if (!$hasTable) {
                $this->chk('model', 'error', 'Tabel tidak ada', "Tabel '$table' (model " . class_basename($cls) . ") tidak ada di database '$dbName'. Jalankan migration?", $file, $line);
                continue;
            }
            $this->chk('model', 'ok', 'Tabel ada', "'$table' di database '$dbName'", $file, $line);

            try {
                $columns = $schema->getColumnListing($table);
            } catch (Throwable $e) {
                $columns = [];
            }

            if ($columns) {
                $missing = array_values(array_diff($m->getFillable(), $columns));
                if ($missing) {
                    $this->chk('model', 'error', 'Kolom $fillable tidak ada di tabel', "Tabel '$table' tidak punya kolom: " . implode(', ', $missing), $file, $line);
                } elseif ($m->getFillable()) {
                    $this->chk('model', 'ok', 'Kolom $fillable sesuai tabel', implode(', ', $m->getFillable()), $file, $line);
                }
            }

            $short = class_basename($cls);
            $q = preg_quote($short, '/');
            $isParam = false;
            foreach ($ref->getParameters() as $p) {
                $t = $p->getType();
                if ($t instanceof ReflectionNamedType && ltrim($t->getName(), '\\') === ltrim($cls, '\\')) {
                    $isParam = true;
                }
            }
            $creates      = (bool) preg_match('/\b' . $q . '::(create|firstOrCreate|updateOrCreate|forceCreate)\s*\(/', $src);
            $instantiated = preg_match('/\bnew\s+\\\\?' . $q . '\b/', $src) && preg_match('/->fill\s*\(/', $src);
            $updatesBound = $isParam && preg_match('/->(update|fill)\s*\(/', $src);
            $massAssign   = $creates || $instantiated || $updatesBound;
            $writesNew    = $creates || preg_match('/\bnew\s+\\\\?' . $q . '\b/', $src);

            if (in_array($fn, ['tambah_simpan', 'edit_simpan'], true)) {
                    if ($fn === 'tambah_simpan' && $writesNew && $m->getFillable() && method_exists($schema, 'getColumns')) {                    try {
                        $required = [];
                        foreach ($schema->getColumns($table) as $col) {
                            $n = $col['name'];
                            if (!empty($col['auto_increment']) || !empty($col['nullable']) || $col['default'] !== null) {
                                continue;
                            }
                            if (in_array($n, ['created_at', 'updated_at', 'deleted_at'], true)) {
                                continue;
                            }
                            if (!in_array($n, $m->getFillable(), true)) {
                                $required[] = $n;
                            }
                        }
                        if ($required) {
                            $this->chk('model', 'warn', 'Kolom wajib tidak ada di $fillable', "NOT NULL tanpa default: " . implode(', ', $required) . ". Aman bila diisi manual di controller.", $file, $line);
                        }
                    } catch (Throwable $e) {
                    }
                }
            }
        }

        foreach ($tables as $t) {
            $infoTables[] = $t;
            try {
                if (Schema::hasTable($t)) {
                    $this->chk('model', 'ok', 'Tabel DB::table ada', "'$t'", $ctrlFile, $ctrlLine);
                } else {
                    $this->chk('model', 'error', 'Tabel tidak ada', "DB::table('$t') merujuk tabel yang tidak ada.", $ctrlFile, $ctrlLine);
                }
            } catch (Throwable $e) {
                $this->chk('model', 'error', 'Koneksi database gagal', mb_substr($e->getMessage(), 0, 250), $ctrlFile, $ctrlLine);
            }
        }

        $this->info['model'] = implode(', ', array_unique($infoModels)) ?: null;
        $this->info['table'] = implode(', ', array_unique($infoTables)) ?: null;

        // Operasi tulis yang diharapkan
        $expect = match ($fn) {
            'tambah_simpan' => ['create', 'insert', 'insertgetid', 'save', 'firstorcreate', 'updateorcreate', 'upsert', 'forcecreate', 'createmany'],
            'edit_simpan' => ['update', 'save', 'fill', 'updateorcreate', 'upsert', 'increment', 'decrement', 'sync'],
            'hapus' => ['delete', 'destroy', 'forcedelete'],
            default => null,
        };
        if ($expect) {
            $found = [];
            if (preg_match_all('/(?:::|->)\s*(create|insertGetId|insert|firstOrCreate|updateOrCreate|upsert|forceCreate|createMany|save|update|fill|delete|destroy|forceDelete|increment|decrement|sync)\s*\(/i', $src, $mm)) {
                $found = array_values(array_unique(array_map('strtolower', $mm[1])));
            }
            $hit = array_values(array_intersect($found, $expect));
            if ($hit) {
                $this->chk('model', 'ok', 'Operasi database ditemukan', implode(', ', $hit) . '()', $ctrlFile, $ctrlLine);
            } else {
                $this->chk('model', 'warn', 'Operasi tulis tidak ditemukan', 'Tidak ada ' . implode('/', $expect) . '() langsung di method ini (mungkin lewat Service).', $ctrlFile, $ctrlLine);
            }
        }
    }

    private function modelsIn(ReflectionMethod $ref, string $src): array
    {
        $file = (string) $ref->getFileName();
        $ns = $ref->getDeclaringClass()->getNamespaceName();
        $imports = $this->importsOf($file);
        $names = [];

        foreach ($ref->getParameters() as $p) {
            $t = $p->getType();
            if ($t instanceof ReflectionNamedType && !$t->isBuiltin()) {
                $names[] = $t->getName();
            }
        }
        if (preg_match_all('/(?<![\w\\\\$>])((?:\\\\?[A-Za-z_]\w*\\\\)*[A-Za-z_]\w*)::/', $src, $m)) {
            $names = array_merge($names, $m[1]);
        }
        if (preg_match_all('/\bnew\s+(\\\\?[A-Za-z_][\w\\\\]*)/', $src, $m)) {
            $names = array_merge($names, $m[1]);
        }

        $out = [];
        foreach (array_unique($names) as $n) {
            if (in_array(strtolower($n), ['self', 'static', 'parent'], true)) {
                continue;
            }
            $fqcn = $this->resolveName($n, $ns, $imports);
            try {
                if (class_exists($fqcn) && is_subclass_of($fqcn, Model::class)) {
                    $out[$fqcn] = $fqcn;
                }
            } catch (Throwable $e) {
            }
        }
        return array_values($out);
    }

    private function resolveName(string $name, string $ns, array $imports): string
    {
        if (str_starts_with($name, '\\')) {
            return ltrim($name, '\\');
        }
        if (str_contains($name, '\\')) {
            $first = strtolower(strstr($name, '\\', true));
            return isset($imports[$first]) ? $imports[$first] . strstr($name, '\\') : "$ns\\$name";
        }
        $low = strtolower($name);
        if (isset($imports[$low])) {
            return $imports[$low];
        }
        $same = $ns !== '' ? "$ns\\$name" : $name;
        return class_exists($same) ? $same : $name;
    }

    private function importsOf(string $file): array
    {
        if (isset($this->importCache[$file])) {
            return $this->importCache[$file];
        }
        $out = [];
        $code = is_file($file) ? (string) file_get_contents($file) : '';
        $head = preg_split('/\b(?:abstract\s+|final\s+)?(?:class|trait|interface|enum)\s+\w+/', $code, 2)[0] ?? '';
        preg_match_all('/^\s*use\s+([^;]+);/m', $head, $m);

        $add = function (string $item) use (&$out) {
            if (preg_match('/^(\\\\?[\w\\\\]+)(?:\s+as\s+(\w+))?$/i', trim($item), $mm)) {
                $fqcn = ltrim($mm[1], '\\');
                $alias = $mm[2] ?? substr(strrchr('\\' . $fqcn, '\\'), 1);
                $out[strtolower($alias)] = $fqcn;
            }
        };

        foreach ($m[1] as $spec) {
            $spec = trim($spec);
            if (preg_match('/^(function|const)\s/i', $spec)) {
                continue;
            }
            if (preg_match('/^(.+?)\\\\\{(.+)\}$/s', $spec, $g)) {
                foreach (explode(',', $g[2]) as $it) {
                    $add(trim($g[1]) . '\\' . trim($it));
                }
            } else {
                foreach (explode(',', $spec) as $it) {
                    $add($it);
                }
            }
        }
        return $this->importCache[$file] = $out;
    }

    /* ---- Request langsung (hanya GET aman) ---------------------------- */

    private function checkRequest(array $f, ?HttpKernel $kernel): void
    {
        $uri = '/' . ltrim($f['uri'], '/');

        if (!$kernel) {
            $this->chk('request', 'skip', 'Uji request dilewati', 'Dijalankan tanpa user login (AUDIT_USER_ID) atau memakai --no-request; hanya analisis statis.');
            return;
        }
        if (!in_array('GET', $f['methods'], true) || str_contains($uri, '{')) {
            $this->chk('request', 'skip', 'Tidak diuji langsung', 'Butuh parameter URL atau mengubah data (POST/PUT/DELETE). Alur diverifikasi lewat Blade → Route → Controller → Model.');
            return;
        }
        if (!in_array($f['function'], ['baca_daftar', 'tambah_form'], true) || preg_match(self::DANGEROUS_GET, $uri . ' ' . $f['name'])) {
            $this->chk('request', 'skip', 'Dilewati demi keamanan', 'Hanya halaman daftar dan form tambah yang diuji lewat request.');
            return;
        }

        try {
            DB::beginTransaction();
        } catch (Throwable $e) {
        }

        try {
            $request = Request::create($uri, 'GET');
            $response = $kernel->handle($request);
            $this->inspectResponse($response, $uri);
            $kernel->terminate($request, $response);
        } catch (Throwable $e) {
            if ($this->isAuthOrSessionNoise($e, 0)) {
                $this->chk('request', 'skip', 'Butuh login / izin', 'Diabaikan (bukan error kode).');
            } else {
                [$rel, $line] = $this->appFileFromException($e);
                $this->chk('request', 'error', 'Exception saat request', get_class($e) . ': ' . mb_substr($e->getMessage(), 0, 300), $rel, $line);
            }
        } finally {
            try {
                while (DB::transactionLevel() > 0) {
                    DB::rollBack();
                }
            } catch (Throwable $e) {
            }
        }
    }

    private function inspectResponse(Response $response, string $uri): void
    {
        $code = $response->getStatusCode();
        $body = method_exists($response, 'getContent') ? (string) $response->getContent() : '';

        if (in_array($code, [401, 403, 419], true)) {
            $this->chk('request', 'skip', "HTTP $code", 'Butuh login / izin; diabaikan.');
            return;
        }

        if ($code >= 500) {
            $ex = property_exists($response, 'exception') ? $response->exception : null;
            if ($this->isAuthOrSessionNoise($ex instanceof Throwable ? $ex : null, $code, $body)) {
                $this->chk('request', 'skip', 'Butuh login / izin', 'Diabaikan (bukan error kode).');
                return;
            }
            if ($ex instanceof Throwable) {
                [$rel, $line] = $this->appFileFromException($ex);
                $this->chk('request', 'error', "HTTP $code", get_class($ex) . ': ' . mb_substr($ex->getMessage(), 0, 300), $rel, $line);
            } else {
                $this->chk('request', 'error', "HTTP $code", $this->snippet($body) ?: "GET $uri mengembalikan $code");
            }
            return;
        }

        if ($code >= 300 && $code < 400) {
            $this->chk('request', 'ok', "Redirect $code", "GET $uri berjalan dan mengalihkan halaman.");
            return;
        }
        if (in_array($code, [404, 405, 422], true)) {
            $this->chk('request', 'warn', "HTTP $code", "GET $uri mengembalikan $code.");
            return;
        }
        if ($this->bodyLooksLikeServerError($body) && !$this->isAuthOrSessionNoise(null, 500, $body)) {
            $this->chk('request', 'error', 'Halaman memuat pesan error server', $this->snippet($body));
            return;
        }

        $this->chk('request', 'ok', "HTTP $code", "GET $uri berhasil dimuat.");
    }

    private function isAuthOrSessionNoise(?Throwable $e, int $code, string $body = ''): bool
    {
        if (in_array($code, [401, 403, 419], true)) {
            return true;
        }
        $hay = strtolower(($e ? $e->getMessage() : '') . ' ' . $body);
        foreach ([
            'unauthenticated', 'please login', 'silakan login', 'harus login', 'not logged in',
            'csrf token mismatch', 'page expired', 'session store not set', 'session has expired',
            'authenticationexception', 'authorizationexception', 'this action is unauthorized',
            'redirecting to login', 'route [login]',
        ] as $n) {
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
        return $body !== '' && (bool) preg_match(
            '/SQLSTATE|QueryException|ErrorException|Integrity constraint|View \[.*?\] not found|Illuminate\\\\Database|Illuminate\\\\View\\\\|Fatal error/i',
            $body
        );
    }

    private function snippet(string $body): string
    {
        return mb_substr(trim(preg_replace('/\s+/', ' ', strip_tags($body))), 0, 400);
    }

    private function appFileFromException(Throwable $e): array
    {
        $base = str_replace('\\', '/', realpath(base_path()) ?: base_path());
        $candidates = [[$e->getFile(), $e->getLine()]];
        foreach ($e->getTrace() as $frame) {
            if (!empty($frame['file'])) {
                $candidates[] = [$frame['file'], $frame['line'] ?? 0];
            }
        }
        foreach ($candidates as [$path, $line]) {
            $norm = str_replace('\\', '/', realpath($path) ?: $path);
            if (!str_starts_with($norm, $base)) {
                continue;
            }
            $rel = ltrim(substr($norm, strlen($base)), '/');
            if (str_starts_with($rel, 'vendor/')) {
                continue;
            }
            return [$rel, (int) $line];
        }
        return [$this->toRel($e->getFile()), (int) $e->getLine()];
    }

    /* ------------------------------------------------------------------ */
    /*  Util                                                               */
    /* ------------------------------------------------------------------ */

    private function src(ReflectionMethod $m): string
    {
        $file = $m->getFileName();
        if (!$file || !is_file($file)) {
            return '';
        }
        $lines = file($file) ?: [];
        $code = implode('', array_slice($lines, $m->getStartLine() - 1, $m->getEndLine() - $m->getStartLine() + 1));

        $out = '';
        foreach (token_get_all('<?php ' . $code) as $t) {
            if (is_array($t)) {
                if (in_array($t[0], [T_COMMENT, T_DOC_COMMENT], true)) {
                    $out .= str_repeat("\n", substr_count($t[1], "\n"));
                    continue;
                }
                $out .= $t[1];
            } else {
                $out .= $t;
            }
        }
        return substr($out, 6);
    }

    private function toRel(string $path): string
    {
        $base = rtrim(str_replace('\\', '/', base_path()), '/') . '/';
        $path = str_replace('\\', '/', $path);
        return str_starts_with($path, $base) ? substr($path, strlen($base)) : ltrim($path, '/');
    }

    /* ------------------------------------------------------------------ */
    /*  Hasil                                                              */
    /* ------------------------------------------------------------------ */

    private function pushFlow(array $f): void
    {
        $errors = array_values(array_filter($this->checks, fn ($c) => $c['status'] === 'error'));
        $warns  = array_values(array_filter($this->checks, fn ($c) => $c['status'] === 'warn'));
        $level  = $errors ? 'ERROR' : ($warns ? 'WARNING' : 'OK');
        $pool   = $errors ?: $warns;
            
        $file = null;
        $line = 0;
        if ($errors) {
            foreach ($errors as $c) {
                if (!empty($c['file'])) {
                    $file = $c['file'];
                    $line = (int) $c['line'];
                    break;
                }
            }
        }
        if ($file === null) {
            foreach ($this->checks as $c) {
                if ($c['layer'] === 'controller' && !empty($c['file'])) {
                    $file = $c['file'];
                    $line = (int) $c['line'];
                    break;
                }
            }
        }

        if ($errors) {
            $layers = [];
            foreach ($errors as $c) {
                $layers[self::LAYER_LABEL[$c['layer']]] = true;
            }
            $type = 'Gagal: ' . implode(', ', array_keys($layers));
            $first = $errors[0];
            $message = '[' . self::LAYER_LABEL[$first['layer']] . '] ' . $first['title']
                . ($first['detail'] !== '' ? ' — ' . $first['detail'] : '');
            if (count($errors) > 1) {
                $message .= ' (+' . (count($errors) - 1) . ' masalah lain)';
            }
        } elseif ($warns) {
            $type = 'Peringatan: ' . implode(', ', array_unique(array_map(fn ($c) => self::LAYER_LABEL[$c['layer']], $warns)));
            $first = $warns[0];
            $message = '[' . self::LAYER_LABEL[$first['layer']] . '] ' . $first['title']
                . ($first['detail'] !== '' ? ' — ' . $first['detail'] : '');
            if (count($warns) > 1) {
                $message .= ' (+' . (count($warns) - 1) . ' peringatan lain)';
            }
        } else {
            $type = 'Alur OK';
            $parts = [];
            if ($this->info['blade']) {
                $parts[] = 'Blade: ' . $this->info['blade'];
            }
            $parts[] = 'Route: ' . ($f['name'] !== '' ? $f['name'] : '/' . $f['uri']);
            if ($this->info['controller']) {
                $parts[] = 'Controller: ' . $this->info['controller'];
            }
            if ($this->info['model']) {
                $parts[] = 'Model: ' . $this->info['model'] . ($this->info['table'] ? " (tabel {$this->info['table']})" : '');
            } elseif ($this->info['table']) {
                $parts[] = 'Tabel: ' . $this->info['table'];
            }
            $message = 'Semua langkah berhasil. ' . implode(' → ', $parts);
        }

        $this->issues[] = [
            'level' => $level,
            'feature' => $f['feature'],
            'function' => $f['function'],
            'type' => $type,
            'file' => $file,
            'line' => $line,
            'route' => $f['name'] !== '' ? $f['name'] : null,
            'method' => $f['method'],
            'uri' => $f['uri'],
            'url' => $f['method'] . ' /' . $f['uri'],
            'status' => $level === 'OK' ? 'ok' : 'failed',
            'message' => $message,
            'blade' => $this->info['blade'],
            'controller' => $this->info['controller'],
            'model' => $this->info['model'],
            'table' => $this->info['table'],
            'checks' => $this->checks,
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
                $touched[$flow['feature'] . '|' . $flow['function'] . '|' . $flow['uri']] = true;
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