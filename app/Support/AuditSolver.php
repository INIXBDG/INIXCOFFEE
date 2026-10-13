<?php

namespace App\Support;

use FilesystemIterator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use App\Support\Concerns\ProjectAwareSolutions;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

class AuditSolver
{
    use ProjectAwareSolutions;

    private const TABS = ['code', 'assets', 'js', 'features'];

    private ?array $rules = null;
    private ?array $classIdx = null;
    private ?array $viewIdx = null;
    private ?array $idIdx = null;
    private ?array $jsFnIdx = null;
    private ?array $publicIdx = null;
    private ?array $knowledge = null;
    private array $reports = [];

    public function solve(string $tab, array $issue): array
    {
        $c = $this->context($tab, $issue);
        [$rule, $sol] = $this->match($c);

        $extras = [];
        foreach (array_slice($c['others'] ?? [], 0, 2) as $o) {
            $oc = $c;
            $oc['type'] = (string) ($o['title'] ?? '');
            $oc['message'] = (string) ($o['detail'] ?? '');
            [, $os] = $this->match($oc);
            $extras[] = $this->ex(
                'Masalah lain di alur ini: ' . $oc['type'],
                $os['fix']['title'] . (isset($os['fix']['steps'][0]) ? ' — ' . $os['fix']['steps'][0] : '')
            );
        }

        $sol['extras'] = $this->fillExtras($c, array_merge($extras, $sol['extras'] ?? []));
        $sol['rule'] = $rule['id'];
        $sol['rule_label'] = $rule['label'];
        $sol['level'] = $c['level'];
        $sol['file'] = $c['file'];
        $sol['line'] = $c['line'];
        $sol['context'] = $this->snippet($c, 3);
        $sol['insights'] = $this->insights($c, $rule);
        $sol['confidence'] = $this->confidence($rule);

        $k = $this->knowledge();
        $k['rules'][$rule['id']]['seen'] = ($k['rules'][$rule['id']]['seen'] ?? 0) + 1;
        $k['rules'][$rule['id']]['last_seen'] = now()->toIso8601String();
        $this->saveKnowledge($k);

        return $sol;
    }

    public function ingest(): void
    {
        $k = $this->knowledge();
        $now = now()->toIso8601String();
        $active = [];

        foreach ($this->allIssues() as $i) {
            $c = $this->context($i['tab'], $i);
            $rule = $this->pick($c)[0];
            $sig = $this->signature($c['type'], $c['message']);
            $key = $i['tab'] . '|' . $sig . '|' . ($c['file'] ?? '');
            $active[$key] = $rule['id'];

            $p = $k['patterns'][$sig] ?? ['count' => 0, 'first_seen' => $now];
            if (!isset($k['active'][$key])) {
                $p['count']++;
            }
            $p['rule'] = $rule['id'];
            $p['last_seen'] = $now;
            $k['patterns'][$sig] = $p;
        }

        foreach ($k['active'] ?? [] as $key => $ruleId) {
            $tab = explode('|', $key)[0];
            if (!isset($active[$key]) && $this->report($tab) !== null) {
                $k['rules'][$ruleId]['fixed'] = ($k['rules'][$ruleId]['fixed'] ?? 0) + 1;
            }
        }

        $k['active'] = $active;

        if (count($k['patterns']) > 2000) {
            uasort($k['patterns'], fn ($a, $b) => $b['count'] <=> $a['count']);
            $k['patterns'] = array_slice($k['patterns'], 0, 1500, true);
        }

        $this->saveKnowledge($k);
    }

    public function feedback(string $rule, string $verdict): void
    {
        if (!in_array($verdict, ['helpful', 'unhelpful', 'fixed'], true) || !preg_match('/^[\w.\-]{1,60}$/', $rule)) {
            return;
        }
        $k = $this->knowledge();
        $k['rules'][$rule][$verdict] = ($k['rules'][$rule][$verdict] ?? 0) + 1;
        $this->saveKnowledge($k);
    }

    private function context(string $tab, array $i): array
    {
        $c = [
            'tab' => $tab,
            'level' => (string) ($i['level'] ?? 'ERROR'),
            'type' => (string) ($i['type'] ?? ''),
            'message' => (string) ($i['message'] ?? ''),
            'file' => $this->safeRel($i['file'] ?? null),
            'line' => (int) ($i['line'] ?? 0),
            'feature' => (string) ($i['feature'] ?? ''),
            'function' => (string) ($i['function'] ?? ''),
            'url' => (string) ($i['url'] ?? ''),
            'route' => (string) ($i['route'] ?? ''),
            'others' => [],
        ];

        if ($tab === 'features' && !empty($i['checks']) && is_array($i['checks'])) {
            $pick = null;
            foreach (['error', 'warn'] as $st) {
                foreach ($i['checks'] as $ch) {
                    if (($ch['status'] ?? '') === $st) {
                        $pick = $ch;
                        break 2;
                    }
                }
            }
            if ($pick) {
                $c['type'] = (string) ($pick['title'] ?? '');
                $c['message'] = (string) ($pick['detail'] ?? '');
                $c['file'] = $this->safeRel($pick['file'] ?? ($i['file'] ?? null));
                $c['line'] = (int) ($pick['line'] ?? ($i['line'] ?? 0));
                $c['level'] = ($pick['status'] ?? '') === 'error' ? 'ERROR' : 'WARNING';
                foreach ($i['checks'] as $ch) {
                    if ($ch !== $pick && in_array($ch['status'] ?? '', ['error', 'warn'], true)) {
                        $c['others'][] = $ch;
                    }
                }
            }
        }

        return $c;
    }

    private function safeRel($f): ?string
    {
        if (!is_string($f) || $f === '') {
            return null;
        }
        $f = ltrim(str_replace('\\', '/', $f), '/');
        if (str_contains($f, '..') || !preg_match('#^(app|resources|routes|public|config|database|bootstrap|scripts)/#', $f)) {
            return null;
        }
        return $f;
    }

    private function pick(array $c): array
    {
        $best = null;
        $bestScore = -1;
        $bestM = [];
        foreach ($this->rules() as $r) {
            if ($r['tabs'] && !in_array($c['tab'], $r['tabs'], true)) {
                continue;
            }
            $score = 0;
            $m = [];
            if ($r['type']) {
                if (!preg_match($r['type'], $c['type'])) {
                    continue;
                }
                $score += 2;
            }
            if ($r['msg']) {
                if (!preg_match($r['msg'], $c['message'], $m)) {
                    continue;
                }
                $score += 3;
            }
            if ($r['tabs']) {
                $score++;
            }
            if ($score > $bestScore) {
                $best = $r;
                $bestScore = $score;
                $bestM = $m;
            }
        }

        if (!$best) {
            $best = ['id' => 'generic.' . $c['tab'], 'label' => 'Umum', 'base' => 40, 'fn' => 'rGeneric'];
        }

        return [$best, $bestM];
    }

    private function match(array $c): array
    {
        [$rule, $m] = $this->pick($c);
        return [$rule, $this->{$rule['fn']}($c, $m)];
    }

    private function confidence(array $rule): array
    {
        $s = $this->knowledge()['rules'][$rule['id']] ?? [];
        $good = ($s['helpful'] ?? 0) + ($s['fixed'] ?? 0);
        $bad = $s['unhelpful'] ?? 0;
        $score = max(20, min(99, $rule['base'] + min(15, $good * 2) - min(30, $bad * 5)));
        return ['score' => $score, 'good' => $good, 'bad' => $bad];
    }

    private function insights(array $c, array $rule): array
    {
        $out = [];
        $sig = $this->signature($c['type'], $c['message']);
        $tok = $this->q($c['message'])[0] ?? '';
        $same = 0;
        $files = [];
        $related = 0;
        $inFile = 0;

        foreach ($this->allIssues() as $i) {
            $ic = $this->context($i['tab'], $i);
            if ($this->signature($ic['type'], $ic['message']) === $sig) {
                $same++;
                if ($ic['file']) {
                    $files[$ic['file']] = true;
                }
            } elseif (strlen($tok) > 2 && str_contains($ic['message'], $tok)) {
                $related++;
            }
            if ($c['file'] && $ic['file'] === $c['file']) {
                $inFile++;
            }
        }

        if ($same > 1) {
            $out[] = 'Pola yang sama muncul ' . $same . ' kali' . (count($files) ? ' di ' . count($files) . ' file' : '')
                . '. Perbaiki satu per satu dengan cara yang sama, lalu gunakan "Cek ulang terpilih".';
        }
        if ($related > 0) {
            $out[] = "Nama '$tok' juga terlibat di $related masalah lain. Memperbaiki akar masalahnya kemungkinan ikut membereskan masalah tersebut.";
        }
        if ($c['file'] && $inFile > 1) {
            $out[] = "File ini punya $inFile temuan. Perbaiki semuanya sekaligus agar hanya perlu satu kali cek ulang.";
        }

        $k = $this->knowledge();
        $p = $k['patterns'][$sig] ?? null;
        if ($p && $p['count'] > 1) {
            $out[] = 'Sepanjang riwayat audit, pola ini sudah muncul ' . $p['count'] . ' kali (pertama: ' . substr($p['first_seen'], 0, 10) . '). Pertimbangkan pencegahan, bukan hanya perbaikan.';
        }

        $s = $k['rules'][$rule['id']] ?? [];
        if (($s['fixed'] ?? 0) > 0) {
            $out[] = 'Jenis masalah ini sudah ' . $s['fixed'] . ' kali hilang pada audit berikutnya setelah diperbaiki.';
        }

        return $out;
    }

    private function signature(string $type, string $msg): string
    {
        $m = preg_replace(["/'[^']*'/", '/"[^"]*"/', '/\d+/'], ["''", '""', '#'], $msg);
        return strtolower($type) . '|' . mb_substr((string) $m, 0, 160);
    }

    private function allIssues(): array
    {
        $out = [];
        foreach (self::TABS as $tab) {
            foreach (($this->report($tab)['issues'] ?? []) as $i) {
                if (($i['level'] ?? '') === 'OK') {
                    continue;
                }
                $i['tab'] = $tab;
                $out[] = $i;
            }
        }
        return $out;
    }

    private function report(string $tab): ?array
    {
        if (!array_key_exists($tab, $this->reports)) {
            $f = storage_path('logs/audit/' . $tab . '.json');
            $this->reports[$tab] = is_file($f) ? (json_decode(File::get($f), true) ?: null) : null;
        }
        return $this->reports[$tab];
    }

    private function knowledge(): array
    {
        if ($this->knowledge === null) {
            $f = storage_path('logs/audit/knowledge.json');
            $k = is_file($f) ? (json_decode(File::get($f), true) ?: []) : [];
            $k['rules'] = $k['rules'] ?? [];
            $k['patterns'] = $k['patterns'] ?? [];
            $k['active'] = $k['active'] ?? [];
            $this->knowledge = $k;
        }
        return $this->knowledge;
    }

    private function saveKnowledge(array $k): void
    {
        $this->knowledge = $k;
        $k['updated_at'] = now()->toIso8601String();
        File::ensureDirectoryExists(storage_path('logs/audit'));
        File::put(
            storage_path('logs/audit/knowledge.json'),
            json_encode($k, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
            true
        );
    }

    private function rules(): array
    {
        if ($this->rules !== null) {
            return $this->rules;
        }

        $r = [];
        $add = function (string $id, string $label, ?array $tabs, ?string $type, ?string $msg, int $base, string $fn) use (&$r) {
            $r[] = compact('id', 'label', 'tabs', 'type', 'msg', 'base', 'fn');
        };

        $add('code.syntax', 'Syntax PHP', ['code'], '/^Syntax$/', null, 90, 'rSyntax');
        $add('code.import', 'Import (use) salah', ['code'], '/^Import$/', null, 88, 'rImport');
        $add('case.mismatch', 'Huruf besar-kecil berbeda', null, '/^Huruf (berbeda|class berbeda)$/', null, 95, 'rCase');
        $add('code.class_missing', 'Class belum di-import / tidak ada', ['code'], '/^Class tidak ditemukan$/', null, 88, 'rClassMissing');
        $add('code.view', 'View tidak ditemukan', null, '/^(View|File Blade tidak ditemukan)$/', null, 90, 'rViewMissing');
        $add('route.name_missing', 'Nama route tidak terdaftar', ['code', 'assets'], '/^Route name$/', null, 88, 'rRouteName');
        $add('code.route_dup', 'Route duplikat', ['code'], '/^Route duplikat$/', null, 85, 'rRouteDup');
        $add('code.route_name_dup', 'Nama route duplikat', ['code'], '/^Nama route duplikat$/', null, 88, 'rRouteNameDup');
        $add('route.controller', 'Route -> Controller tidak ada', null, '/^(Route -> Controller|Controller tidak ditemukan)$/', null, 88, 'rRouteController');
        $add('route.method', 'Route -> Method tidak ada', null, '/^(Route -> Method|Method tidak ada)$/', null, 90, 'rRouteMethod');
        $add('route.middleware', 'Middleware tidak terdaftar', null, '/^(Middleware|Middleware tidak terdaftar)$/', null, 88, 'rMiddleware');
        $add('code.class_file', 'Nama class tidak sama dengan nama file', ['code'], '/^Nama class != nama file$/', null, 92, 'rClassFile');
        $add('code.namespace', 'Namespace tidak sesuai folder', ['code'], '/^Namespace$/', null, 85, 'rNamespace');
        $add('code.controller_decl', 'Controller tanpa deklarasi class', ['code'], '/^Controller$/', null, 80, 'rControllerDecl');
        $add('code.method_no_route', 'Method controller tanpa route', ['code'], '/^Method tanpa route$/', null, 80, 'rMethodNoRoute');

        $add('asset.missing', 'Asset tidak ditemukan', ['assets'], '/^(Asset tidak ada|Asset CSS)$/', null, 88, 'rAssetMissing');
        $add('asset.unreadable', 'Asset tidak dapat dibaca', ['assets'], '/^Asset tidak dapat dibaca$/', null, 80, 'rAssetUnreadable');
        $add('asset.empty', 'Asset kosong', ['assets'], '/^Asset kosong$/', null, 75, 'rAssetEmpty');
        $add('asset.vite', 'Vite', ['assets'], '/^Vite/', null, 85, 'rVite');
        $add('asset.mix', 'Laravel Mix', ['assets'], '/^Mix/', null, 85, 'rMix');
        $add('asset.external', 'Asset eksternal (CDN) gagal', ['assets'], '/^Asset eksternal$/', null, 80, 'rExternal');
        $add('js.syntax_static', 'Syntax JavaScript', ['assets'], '/^JS syntax/', null, 85, 'rJsSyntax');
        $add('js.element', 'JS mencari elemen yang tidak ada', ['assets'], '/^JS elemen$/', null, 85, 'rJsElement');
        $add('js.scope', 'Fungsi JS bukan global', ['assets'], '/^JS scope$/', null, 90, 'rJsScope');
        $add('js.not_loaded', 'File JS tidak dimuat di halaman', ['assets'], '/^JS tidak dimuat$/', null, 92, 'rJsNotLoaded');
        $add('js.fn_missing', 'Fungsi JS tidak didefinisikan', ['assets'], '/^JS fungsi$/', null, 80, 'rJsFn');
        $add('js.lib_missing', 'Library JS tidak dimuat', ['assets'], '/^JS library$/', null, 92, 'rJsLib');
        $add('js.csrf', 'AJAX tanpa token CSRF', ['assets'], '/^CSRF$/', null, 88, 'rCsrfAjax');
        $add('js.route', 'route() di JS tidak terdaftar', ['assets'], '/^JS route$/', null, 88, 'rRouteName');
        $add('js.ajax', 'URL/method AJAX tidak cocok', ['assets'], '/^JS ajax$/', null, 85, 'rJsAjax');
        $add('render.error', 'Halaman gagal dirender', ['assets'], '/^Render/', null, 75, 'rRender');
        $add('node.missing', 'Node.js tidak tersedia', ['assets'], '/^Node$/', null, 90, 'rNode');

        $add('rt.undefined', 'Variabel/fungsi JS tidak terdefinisi', ['js'], '/^JS exception$/', '/(\S+) is not defined/', 90, 'rRtUndefined');
        $add('rt.exception', 'Exception JavaScript', ['js'], '/^JS exception$/', null, 75, 'rRtException');
        $add('rt.console', 'console.error', ['js'], '/^console\.error$/', null, 70, 'rRtConsole');
        $add('rt.netfail', 'Request gagal', ['js'], '/^Request gagal$/', null, 75, 'rRtNetFail');
        $add('http.status', 'Respons HTTP error', null, '/^HTTP \d{3}$/', null, 78, 'rHttpStatus');
        $add('rt.page', 'Halaman / navigasi gagal', ['js'], '/^(Halaman|Navigasi)$/', null, 75, 'rRtPage');
        $add('rt.login', 'Login audit gagal', ['js'], '/^Login$/', null, 80, 'rRtLogin');

        $add('feat.noname', 'Route tanpa nama', ['features'], '/^Route tidak punya nama$/', null, 90, 'rRouteNoName');
        $add('feat.export', 'Class export tidak ada', ['features'], '/^Class export tidak ditemukan$/', null, 85, 'rExport');
        $add('feat.form_route', 'Form tidak mengarah ke route', ['features'], '/^Form tidak terlihat memakai route ini$/', null, 70, 'rFormRoute');
        $add('feat.form_csrf', 'Form tanpa @csrf', ['features'], '/^Form tanpa @csrf$/', null, 92, 'rFormCsrf');
        $add('feat.table', 'Tabel database tidak ada', ['features'], '/^Tabel tidak ada$/', null, 90, 'rTableMissing');
        $add('feat.fillable_cols', 'Kolom $fillable tidak ada di tabel', ['features'], '/^Kolom \$fillable tidak ada di tabel$/', null, 90, 'rFillableCols');
        $add('feat.mass', 'Mass assignment diblokir', ['features'], '/^Mass assignment diblokir$/', null, 92, 'rMassAssign');
        $add('feat.required', 'Kolom wajib tidak ada di $fillable', ['features'], '/^Kolom wajib tidak ada di \$fillable$/', null, 80, 'rRequiredCols');
        $add('feat.no_model', 'Model/tabel tidak terdeteksi', ['features'], '/^Model \/ tabel tidak terdeteksi$/', null, 60, 'rNoModel');
        $add('feat.no_write', 'Operasi tulis tidak ditemukan', ['features'], '/^Operasi tulis tidak ditemukan$/', null, 65, 'rNoWrite');
        $add('feat.model_inst', 'Model tidak bisa diinstansiasi', ['features'], '/^Model tidak bisa diinstansiasi$/', null, 70, 'rModelInst');
        $add('server.error', 'Error server (500)', ['features', 'assets'], '/^(Exception saat request|Halaman memuat pesan error server|Koneksi database|Audit internal gagal)/', null, 80, 'rServerError');

        return $this->rules = $r;
    }

    private function sol(string $title, string $detail, string $why, string $impact, string $fixTitle, array $steps, ?string $code = null, array $extras = [], ?array $diff = null): array
    {
        return [
            'problem' => ['title' => $title, 'detail' => $detail, 'why' => $why, 'impact' => $impact],
            'fix' => ['title' => $fixTitle, 'steps' => array_values($steps), 'code' => $code, 'diff' => $diff],
            'extras' => $extras,
        ];
    }

    private function ex(string $title, string $detail): array
    {
        return ['title' => $title, 'detail' => $detail];
    }

    private function q(string $s): array
    {
        preg_match_all("/'([^']+)'/", $s, $m);
        return $m[1];
    }

    private function diff(?string $line, string $from, string $to): ?array
    {
        if ($line === null || $from === '' || !str_contains($line, $from)) {
            return null;
        }
        return ['before' => trim($line), 'after' => trim(str_replace($from, $to, $line))];
    }

    private function snippet(array $c, int $r): array
    {
        if (!$c['file'] || $c['line'] < 1) {
            return [];
        }
        $abs = base_path($c['file']);
        if (!is_file($abs) || filesize($abs) > 1500000) {
            return [];
        }
        $lines = file($abs, FILE_IGNORE_NEW_LINES) ?: [];
        $out = [];
        $to = min(count($lines), $c['line'] + $r);
        for ($n = max(1, $c['line'] - $r); $n <= $to; $n++) {
            $out[] = ['n' => $n, 't' => mb_scrub(mb_substr($lines[$n - 1], 0, 220)), 'hit' => $n === $c['line']];
        }
        return $out;
    }

    private function lineText(array $c): ?string
    {
        $s = $this->snippet($c, 0);
        return $s ? $s[0]['t'] : null;
    }

    function closestOld(string $needle, array $hay, int $n = 3): array
    {
        $needle = strtolower($needle);
        $scored = [];
        foreach (array_unique($hay) as $h) {
            similar_text($needle, strtolower($h), $pct);
            if ($pct >= 55 && strtolower($h) !== $needle) {
                $scored[] = [$h, $pct];
            }
        }
        usort($scored, fn ($a, $b) => $b[1] <=> $a[1]);
        return array_slice(array_column($scored, 0), 0, $n);
    }

    private function classIndex(): array
    {
        if ($this->classIdx === null) {
            $this->classIdx = [];
            try {
                foreach (File::allFiles(app_path()) as $f) {
                    if ($f->getExtension() !== 'php') {
                        continue;
                    }
                    $fq = 'App\\' . str_replace(['/', DIRECTORY_SEPARATOR], '\\', substr($f->getRelativePathname(), 0, -4));
                    $this->classIdx[strtolower(class_basename($fq))][] = $fq;
                }
            } catch (Throwable $e) {
            }
        }
        return $this->classIdx;
    }

    private function classNames(): array
    {
        $o = [];
        foreach ($this->classIndex() as $list) {
            foreach ($list as $fq) {
                $o[] = class_basename($fq);
            }
        }
        return $o;
    }

    private function viewNames(): array
    {
        if ($this->viewIdx === null) {
            $this->viewIdx = [];
            try {
                foreach (File::allFiles(resource_path('views')) as $f) {
                    if (str_ends_with($f->getFilename(), '.blade.php')) {
                        $this->viewIdx[] = str_replace(['/', DIRECTORY_SEPARATOR], '.', substr($f->getRelativePathname(), 0, -10));
                    }
                }
            } catch (Throwable $e) {
            }
        }
        return $this->viewIdx;
    }

    private function routeNames(): array
    {
        return array_keys(Route::getRoutes()->getRoutesByName());
    }

    private function routeUris(): array
    {
        $o = [];
        foreach (Route::getRoutes()->getRoutes() as $r) {
            $o[] = '/' . ltrim($r->uri(), '/');
        }
        return array_values(array_unique($o));
    }

    private function tables(): array
    {
        try {
            return Schema::getTableListing();
        } catch (Throwable $e) {
            return [];
        }
    }

    private function columns(string $table): array
    {
        try {
            return Schema::hasTable($table) ? Schema::getColumnListing($table) : [];
        } catch (Throwable $e) {
            return [];
        }
    }

    private function publicMethods(string $class): array
    {
        try {
            if (!class_exists($class)) {
                return [];
            }
            $o = [];
            foreach ((new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC) as $mm) {
                if ($mm->class === ltrim($class, '\\') && !str_starts_with($mm->name, '__')) {
                    $o[] = $mm->name;
                }
            }
            return $o;
        } catch (Throwable $e) {
            return [];
        }
    }

    private function publicFiles(): array
    {
        if ($this->publicIdx === null) {
            $this->publicIdx = [];
            try {
                $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(public_path(), FilesystemIterator::SKIP_DOTS));
                $n = 0;
                foreach ($it as $f) {
                    if (++$n > 20000) {
                        break;
                    }
                    if ($f->isFile() || $f->isLink()) {
                        $this->publicIdx[] = ltrim(str_replace('\\', '/', substr($f->getPathname(), strlen(public_path()))), '/');
                    }
                }
            } catch (Throwable $e) {
            }
        }
        return $this->publicIdx;
    }

    private function viewIds(): array
    {
        if ($this->idIdx === null) {
            $this->idIdx = [];
            try {
                $n = 0;
                foreach (File::allFiles(resource_path('views')) as $f) {
                    if (++$n > 800 || !str_ends_with($f->getFilename(), '.blade.php')) {
                        continue;
                    }
                    $name = str_replace(['/', DIRECTORY_SEPARATOR], '.', substr($f->getRelativePathname(), 0, -10));
                    if (preg_match_all('/\bid\s*=\s*["\']([^"\'{$]+)["\']/', (string) file_get_contents($f->getPathname()), $m)) {
                        foreach ($m[1] as $id) {
                            $this->idIdx[$id][$name] = true;
                        }
                    }
                }
            } catch (Throwable $e) {
            }
        }
        return $this->idIdx;
    }

    private function jsFunctions(): array
    {
        if ($this->jsFnIdx === null) {
            $this->jsFnIdx = [];
            $n = 0;
            foreach ([public_path(), resource_path('js')] as $dir) {
                if (!is_dir($dir)) {
                    continue;
                }
                try {
                    foreach (File::allFiles($dir) as $f) {
                        if (++$n > 300 || !in_array($f->getExtension(), ['js', 'mjs'], true)
                            || preg_match('/(\.min\.|vendor|node_modules|plugins?\/|libs?\/|\/build\/)/i', str_replace('\\', '/', $f->getPathname()))
                            || $f->getSize() > 400000) {
                            continue;
                        }
                        if (preg_match_all('/\bfunction\s+([A-Za-z_$][\w$]*)\s*\(|\b(?:var|let|const)\s+([A-Za-z_$][\w$]*)\s*=\s*(?:async\s*)?(?:function|\([^)]*\)\s*=>)/', (string) file_get_contents($f->getPathname()), $m)) {
                            foreach (array_merge($m[1], $m[2]) as $name) {
                                if ($name !== '') {
                                    $this->jsFnIdx[] = $name;
                                }
                            }
                        }
                    }
                } catch (Throwable $e) {
                }
            }
        }
        return array_values(array_unique($this->jsFnIdx));
    }

    private function knownClass(string $n): ?string
    {
        $facades = ['DB', 'Auth', 'Log', 'Cache', 'Storage', 'Hash', 'Validator', 'Http', 'Session', 'Gate', 'Mail', 'Queue', 'Schema',
            'File', 'Route', 'URL', 'Cookie', 'Redirect', 'Response', 'Artisan', 'Config', 'Event', 'Lang', 'View', 'Bus', 'Crypt',
            'Notification', 'Password', 'RateLimiter', 'Process'];
        if (in_array($n, $facades, true)) {
            return 'Illuminate\\Support\\Facades\\' . $n;
        }
        return [
            'Request' => 'Illuminate\\Http\\Request',
            'Str' => 'Illuminate\\Support\\Str',
            'Arr' => 'Illuminate\\Support\\Arr',
            'Collection' => 'Illuminate\\Support\\Collection',
            'Carbon' => 'Carbon\\Carbon',
            'Model' => 'Illuminate\\Database\\Eloquent\\Model',
            'Exception' => 'Exception',
            'Throwable' => 'Throwable',
            'Closure' => 'Closure',
        ][$n] ?? null;
    }

    private function makeCmd(string $fq): string
    {
        $fq = ltrim($fq, '\\');
        $b = class_basename($fq);
        $name = $b;
        if (preg_match('/\\\\(?:Models|Http\\\\Controllers|Http\\\\Requests|Http\\\\Middleware|Exports|Providers)\\\\(.+)$/', $fq, $mm)) {
            $name = str_replace('\\', '/', $mm[1]);
        }
        return match (true) {
            str_contains($fq, '\\Models\\') => "php artisan make:model $name",
            str_contains($fq, '\\Http\\Controllers\\') => "php artisan make:controller $name",
            str_contains($fq, '\\Http\\Requests\\') => "php artisan make:request $name",
            str_contains($fq, '\\Http\\Middleware\\') => "php artisan make:middleware $name",
            str_contains($fq, '\\Exports\\') => "php artisan make:export $name",
            str_contains($fq, '\\Providers\\') => "php artisan make:provider $name",
            default => "php artisan make:class $name",
        };
    }

    function fillExtrasOld(array $c, array $extras): array
    {
        $contextualExtras = $this->generateContextualExtras($c);
        $allExtras = array_merge($extras, $contextualExtras);

        $bestPractice = $this->generateBestPractice($c);
        if ($bestPractice) {
            $allExtras[] = $bestPractice;
        }

        $pool = $this->getDefaultExtrasPool($c);
        foreach ($pool as $p) {
            if (count($allExtras) >= 6) {
                break;
            }
            $isDuplicate = false;
            foreach ($allExtras as $existing) {
                if ($existing['title'] === $p['title']) {
                    $isDuplicate = true;
                    break;
                }
            }
            if (!$isDuplicate) {
                $allExtras[] = $p;
            }
        }

        return array_slice($allExtras, 0, 6);
    }

    private function generateContextualExtras(array $c): array
    {
        $tab = $c['tab'];
        return match ($tab) {
            'code' => $this->getCodeContextualExtras($c),
            'assets' => $this->getAssetsContextualExtras($c),
            'js' => $this->getJsContextualExtras($c),
            'features' => $this->getFeaturesContextualExtras($c),
            default => [],
        };
    }

    private function getCodeContextualExtras(array $c): array
    {
        $extras = [];
        $file = $c['file'] ?: 'file.php';
        $line = $c['line'];
        $type = $c['type'];

        if (str_contains($type, 'Syntax') || str_contains($type, 'Class') || str_contains($type, 'Import')) {
            $extras[] = $this->ex(
                'Debug dengan logging',
                "Tambahkan logging sementara di baris $line untuk melacak nilai variabel:\n\n" .
                "use Illuminate\\Support\\Facades\\Log;\n\n" .
                "Log::debug('Audit debug', [\n" .
                "    'file' => '$file',\n" .
                "    'line' => $line,\n" .
                "    'data' => \$variableName,\n" .
                "]);\n\n" .
                "Lihat hasil di storage/logs/laravel.log"
            );
        }

        $extras[] = $this->ex(
            'Buat test unit untuk mencegah regresi',
            "Buat test yang mereproduksi masalah ini:\n\n" .
            "php artisan make:test " . basename($file, '.php') . "Test\n\n" .
            "Di file test:\n" .
            "public function test_" . strtolower(class_basename($file)) . "_works()\n" .
            "{\n" .
            "    \$instance = new \\" . class_basename($file) . "();\n" .
            "    \$result = \$instance->methodToTest();\n" .
            "    \$this->assertNotNull(\$result);\n" .
            "}"
        );

        $extras[] = $this->ex(
            'Jalankan static analysis',
            "Pasang dan jalankan Larastan untuk menemukan masalah serupa:\n\n" .
            "composer require --dev larastan/larastan\n\n" .
            "Tambahkan di phpstan.neon:\n" .
            "includes:\n" .
            "    - vendor/larastan/larastan/extension.neon\n\n" .
            "parameters:\n" .
            "    paths:\n" .
            "        - app/\n" .
            "    level: 6\n\n" .
            "Jalankan: vendor/bin/phpstan analyse"
        );

        $extras[] = $this->ex(
            'Checklist code review',
            "Periksa hal-hal berikut di $file:\n\n" .
            "□ Semua class yang dipakai sudah di-import (use statement)\n" .
            "□ Namespace sesuai dengan struktur folder\n" .
            "□ Nama file = nama class (PSR-4)\n" .
            "□ Tidak ada duplikasi deklarasi class/fungsi\n" .
            "□ Semua method publik memiliki dokumentasi\n" .
            "□ Error handling sudah tepat (try-catch jika perlu)"
        );

        if (str_contains($type, 'Controller') || str_contains($type, 'Model')) {
            $extras[] = $this->ex(
                'Optimasi performa',
                "Periksa apakah ada N+1 query atau operasi berat:\n\n" .
                "\$data = Model::with(['relation1', 'relation2'])\n" .
                "    ->where('status', 'active')\n" .
                "    ->paginate(15);\n\n" .
                "Atau gunakan cache untuk data yang jarang berubah:\n" .
                "\$data = Cache::remember('key', 3600, function () {\n" .
                "    return Model::all();\n" .
                "});"
            );
        }

        return $extras;
    }

    private function getAssetsContextualExtras(array $c): array
    {
        $extras = [];
        $file = $c['file'] ?: 'asset';

        $extras[] = $this->ex(
            'Periksa proses build',
            "Jika menggunakan Vite/Mix, pastikan build berjalan dengan benar:\n\n" .
            "# Development\n" .
            "npm run dev\n\n" .
            "# Production\n" .
            "npm run build\n\n" .
            "# Periksa output\n" .
            "ls -la public/build/\n\n" .
            "# Jika ada error, cek:\n" .
            "npm install\n" .
            "rm -rf node_modules package-lock.json\n" .
            "npm install"
        );

        $extras[] = $this->ex(
            'Gunakan versioning untuk cache busting',
            "Tambahkan versioning pada asset untuk menghindari cache lama:\n\n" .
            "// Di Blade:\n" .
            "<link rel=\"stylesheet\" href=\"{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}\">\n\n" .
            "// Atau gunakan Vite yang sudah handle ini otomatis:\n" .
            "@vite(['resources/css/app.css', 'resources/js/app.js'])"
        );

        if (str_contains($c['message'], 'CDN') || str_contains($c['message'], 'eksternal')) {
            $extras[] = $this->ex(
                'Siapkan fallback untuk CDN',
                "Tambahkan fallback jika CDN gagal:\n\n" .
                "<script src=\"https://cdn.example.com/library.js\"></script>\n" .
                "<script>\n" .
                "    if (typeof LibraryName === 'undefined') {\n" .
                "        document.write('<script src=\"{{ asset(\"vendor/library.js\") }}\"><\/script>');\n" .
                "    }\n" .
                "</script>"
            );
        }

        $extras[] = $this->ex(
            'Optimasi ukuran asset',
            "Kurangi ukuran file untuk performa lebih baik:\n\n" .
            "# Minify CSS/JS\n" .
            "npm install -D cssnano terser\n\n" .
            "# Di vite.config.js:\n" .
            "export default defineConfig({\n" .
            "    build: {\n" .
            "        minify: 'terser',\n" .
            "        cssMinify: true,\n" .
            "    }\n" .
            "});\n\n" .
            "# Atau gunakan Laravel Mix:\n" .
            "mix.minify('public/css/app.css');"
        );

        $extras[] = $this->ex(
            'Periksa keamanan asset',
            "Pastikan asset tidak memiliki kerentanan:\n\n" .
            "□ Tidak ada hardcoded credentials di JS\n" .
            "□ Tidak ada eval() atau innerHTML dengan user input\n" .
            "□ CORS policy sudah benar untuk asset eksternal\n" .
            "□ Content-Security-Policy header sudah diset\n" .
            "□ Tidak ada dependency yang outdated (npm audit)"
        );

        return $extras;
    }

    private function getJsContextualExtras(array $c): array
    {
        $extras = [];
        $file = $c['file'] ?: 'file.js';
        $line = $c['line'];

        $extras[] = $this->ex(
            'Tambahkan error handling yang robust',
            "Bungkus kode dengan error handling yang tepat:\n\n" .
            "try {\n" .
            "    const result = await fetch('/api/endpoint');\n" .
            "    if (!result.ok) throw new Error('HTTP ' + result.status);\n" .
            "    const data = await result.json();\n" .
            "    console.log(data);\n" .
            "} catch (error) {\n" .
            "    console.error('Error:', error.message);\n" .
            "    if (typeof Swal !== 'undefined') {\n" .
            "        Swal.fire('Error', error.message, 'error');\n" .
            "    } else {\n" .
            "        alert('Terjadi kesalahan: ' + error.message);\n" .
            "    }\n" .
            "}"
        );

        $extras[] = $this->ex(
            'Gunakan debugging tools',
            "Manfaatkan DevTools untuk debugging:\n\n" .
            "// 1. Breakpoint\n" .
            "debugger;\n\n" .
            "// 2. Console logging yang terstruktur\n" .
            "console.group('Audit Debug - $file:$line');\n" .
            "console.log('Variable:', variableName);\n" .
            "console.table(arrayData);\n" .
            "console.trace();\n" .
            "console.groupEnd();\n\n" .
            "// 3. Performance monitoring\n" .
            "console.time('operation');\n" .
            "console.timeEnd('operation');"
        );

        $extras[] = $this->ex(
            'Buat automated test',
            "Tambahkan test untuk mencegah regresi:\n\n" .
            "// Unit test dengan Jest\n" .
            "describe('Function Name', () => {\n" .
            "    it('should handle normal case', () => {\n" .
            "        const result = functionName(input);\n" .
            "        expect(result).toBe(expected);\n" .
            "    });\n" .
            "});\n\n" .
            "// E2E test dengan Cypress\n" .
            "cy.visit('/page');\n" .
            "cy.get('#element').should('be.visible');\n" .
            "cy.get('#element').click();"
        );

        $extras[] = $this->ex(
            'Tingkatkan kualitas kode',
            "Terapkan best practices:\n\n" .
            "// 1. Gunakan const/let, hindari var\n" .
            "const MAX_RETRIES = 3;\n" .
            "let retryCount = 0;\n\n" .
            "// 2. Arrow functions untuk callback\n" .
            "array.map(item => item.value);\n\n" .
            "// 3. Optional chaining\n" .
            "const value = obj?.nested?.property ?? 'default';\n\n" .
            "// 4. Destructuring\n" .
            "const { name, email } = user;\n\n" .
            "// 5. Async/await\n" .
            "async function fetchData() {\n" .
            "    const res = await fetch(url);\n" .
            "    return res.json();\n" .
            "}"
        );

        $extras[] = $this->ex(
            'Periksa kompatibilitas browser',
            "Pastikan kode bekerja di berbagai browser:\n\n" .
            "// Cek di Can I Use: https://caniuse.com\n\n" .
            "// Gunakan Babel untuk transpile jika perlu\n" .
            "npm install --save-dev @babel/core @babel/preset-env\n\n" .
            "// babel.config.js\n" .
            "module.exports = {\n" .
            "    presets: [\n" .
            "        ['@babel/preset-env', {\n" .
            "            targets: '> 0.25%, not dead'\n" .
            "        }]\n" .
            "    ]\n" .
            "};"
        );

        return $extras;
    }

    private function getFeaturesContextualExtras(array $c): array
    {
        $extras = [];
        $feature = $c['feature'] ?: 'fitur';
        $route = $c['route'] ?: 'route';

        $extras[] = $this->ex(
            'Optimasi database query',
            "Periksa dan optimasi query database:\n\n" .
            "// 1. Gunakan eager loading untuk hindari N+1\n" .
            "\$posts = Post::with(['author', 'comments.user'])->get();\n\n" .
            "// 2. Pilih kolom yang dibutuhkan saja\n" .
            "\$users = User::select('id', 'name', 'email')->get();\n\n" .
            "// 3. Gunakan index untuk kolom yang sering di-query\n" .
            "// Migration:\n" .
            "\$table->index('email');\n" .
            "\$table->index(['status', 'created_at']);\n\n" .
            "// 4. Monitor query dengan Laravel Debugbar\n" .
            "composer require barryvdh/laravel-debugbar --dev"
        );

        $extras[] = $this->ex(
            'Terapkan security best practices',
            "Pastikan fitur aman dari kerentanan umum:\n\n" .
            "// 1. Validasi input\n" .
            "\$validated = \$request->validate([\n" .
            "    'email' => 'required|email|unique:users',\n" .
            "    'password' => 'required|min:8|confirmed',\n" .
            "]);\n\n" .
            "// 2. Authorization\n" .
            "public function update(Request \$request, Post \$post)\n" .
            "{\n" .
            "    \$this->authorize('update', \$post);\n" .
            "}\n\n" .
            "// 3. Mass Assignment Protection\n" .
            "protected \$fillable = ['title', 'content'];\n" .
            "protected \$guarded = ['id', 'user_id'];"
        );

        if (str_contains($c['type'], 'Route') || str_contains($c['type'], 'Controller')) {
            $extras[] = $this->ex(
                'Dokumentasi API dengan Swagger',
                "Tambahkan dokumentasi untuk endpoint ini:\n\n" .
                "composer require darkaonline/l5-swagger\n\n" .
                "/**\n" .
                " * @OA\\Get(\n" .
                " *     path=\"/api/$route\",\n" .
                " *     summary=\"Get $feature\",\n" .
                " *     @OA\\Response(response=200, description=\"Success\")\n" .
                " * )\n" .
                " */\n" .
                "public function index()\n" .
                "{\n" .
                "    // ...\n" .
                "}\n\n" .
                "php artisan l5-swagger:generate"
            );
        }

        $extras[] = $this->ex(
            'Tambahkan monitoring dan logging',
            "Lacak penggunaan dan error fitur ini:\n\n" .
            "use Illuminate\\Support\\Facades\\Log;\n" .
            "use Illuminate\\Support\\Facades\\Event;\n\n" .
            "Log::info('$feature accessed', [\n" .
            "    'user_id' => auth()->id(),\n" .
            "    'route' => '$route',\n" .
            "    'timestamp' => now(),\n" .
            "]);\n\n" .
            "Event::dispatch(new FeatureAccessed('$feature'));\n\n" .
            "composer require laravel/telescope --dev\n" .
            "php artisan telescope:install"
        );

        $extras[] = $this->ex(
            'Implementasi caching',
            "Tingkatkan performa dengan caching:\n\n" .
            "use Illuminate\\Support\\Facades\\Cache;\n\n" .
            "\$data = Cache::remember('$feature.data', 3600, function () {\n" .
            "    return Model::where('active', true)->get();\n" .
            "});\n\n" .
            "return Cache::remember('view.$route', 1800, function () {\n" .
            "    return view('$feature.index', compact('data'));\n" .
            "});\n\n" .
            "Cache::forget('$feature.data');"
        );

        return $extras;
    }

    private function generateBestPractice(array $c): array
    {
        $tab = $c['tab'];
        $file = $c['file'] ?: 'file';

        $bestPractices = [
            'code' => [
                'title' => ' Best Practice: Code Quality & Maintainability',
                'detail' => "Terapkan standar kode yang tinggi untuk $file:\n\n" .
                    "1. **SOLID Principles**\n" .
                    "   - Single Responsibility: Satu class = satu tanggung jawab\n" .
                    "   - Dependency Injection: Inject dependencies, jangan hardcode\n\n" .
                    "2. **DRY (Don't Repeat Yourself)**\n" .
                    "   - Extract repeated code ke method/class terpisah\n" .
                    "   - Gunakan trait untuk shared functionality\n\n" .
                    "3. **Clean Code**\n" .
                    "   - Nama variabel/method yang deskriptif\n" .
                    "   - Method maksimal 20-30 baris\n" .
                    "   - Comment hanya untuk 'why', bukan 'what'\n\n" .
                    "4. **Type Hinting & Return Types**\n" .
                    "   public function getUser(int \$id): ?User\n" .
                    "   {\n" .
                    "       return User::find(\$id);\n" .
                    "   }\n\n" .
                    "5. **Error Handling**\n" .
                    "   - Gunakan custom exceptions\n" .
                    "   - Log error dengan context yang cukup\n" .
                    "   - Return user-friendly message"
            ],
            'assets' => [
                'title' => ' Best Practice: Asset Management',
                'detail' => "Kelola asset dengan efisien dan aman:\n\n" .
                    "1. **Build Process**\n" .
                    "   - Gunakan Vite untuk development modern\n" .
                    "   - Enable source maps untuk debugging\n" .
                    "   - Minify & compress untuk production\n\n" .
                    "2. **Organization**\n" .
                    "   resources/\n" .
                    "   ├── css/\n" .
                    "   │   ├── app.css\n" .
                    "   │   ── components/\n" .
                    "   ├── js/\n" .
                    "   │   ├── app.js\n" .
                    "   │   ── components/\n" .
                    "   ── views/\n\n" .
                    "3. **Performance**\n" .
                    "   - Lazy load images: loading=\"lazy\"\n" .
                    "   - Preload critical assets\n" .
                    "   - Use CDN untuk static assets\n\n" .
                    "4. **Security**\n" .
                    "   - No hardcoded secrets in JS\n" .
                    "   - CSP headers configured\n" .
                    "   - Regular dependency updates"
            ],
            'js' => [
                'title' => '🏆 Best Practice: JavaScript Development',
                'detail' => "Tulis JavaScript yang robust dan maintainable:\n\n" .
                    "1. **Modern ES6+ Features**\n" .
                    "   - const/let instead of var\n" .
                    "   - Arrow functions\n" .
                    "   - Destructuring & spread operator\n" .
                    "   - Async/await\n\n" .
                    "2. **Error Handling**\n" .
                    "   try {\n" .
                    "       const data = await fetch(url);\n" .
                    "   } catch (error) {\n" .
                    "       handleError(error);\n" .
                    "   }\n\n" .
                    "3. **Testing**\n" .
                    "   - Unit tests dengan Jest\n" .
                    "   - E2E tests dengan Cypress\n" .
                    "   - Coverage minimal 80%\n\n" .
                    "4. **Performance**\n" .
                    "   - Debounce/throttle event handlers\n" .
                    "   - Lazy load modules\n" .
                    "   - Virtual scrolling untuk list panjang\n\n" .
                    "5. **Accessibility**\n" .
                    "   - Semantic HTML\n" .
                    "   - ARIA labels\n" .
                    "   - Keyboard navigation"
            ],
            'features' => [
                'title' => '🏆 Best Practice: Feature Development',
                'detail' => "Bangun fitur yang scalable dan maintainable:\n\n" .
                    "1. **Architecture**\n" .
                    "   - Follow MVC pattern\n" .
                    "   - Use Service layer untuk complex logic\n" .
                    "   - Repository pattern untuk data access\n\n" .
                    "2. **Database**\n" .
                    "   - Proper indexing\n" .
                    "   - Eloquent relationships\n" .
                    "   - Migration & seeder untuk setiap perubahan\n\n" .
                    "3. **Security**\n" .
                    "   - Validate all input\n" .
                    "   - Authorize all actions\n" .
                    "   - Sanitize output\n\n" .
                    "4. **Testing**\n" .
                    "   - Feature tests untuk setiap endpoint\n" .
                    "   - Unit tests untuk business logic\n" .
                    "   - Integration tests untuk critical flows\n\n" .
                    "5. **Documentation**\n" .
                    "   - PHPDoc untuk public methods\n" .
                    "   - README untuk complex features\n" .
                    "   - API docs dengan Swagger"
            ],
        ];

        return $bestPractices[$tab] ?? $bestPractices['code'];
    }

    private function getDefaultExtrasPool(array $c): array
    {
        $file = $c['file'] ?: 'file terkait';
        $tab = $c['tab'];

        return match ($tab) {
            'code' => [
                $this->ex(
                    'Gunakan IDE features',
                    "Manfaatkan fitur VS Code/PHPStorm:\n\n" .
                    "• Ctrl+Click: Navigate to definition\n" .
                    "• F2: Rename symbol (refactor semua pemakaian)\n" .
                    "• Alt+F12: Peek definition\n" .
                    "• Ctrl+Shift+F: Find in files\n" .
                    "• Install: PHP Intelephense, Laravel Snippets"
                ),
                $this->ex(
                    'Periksa Git history',
                    "Lihat kapan masalah ini muncul:\n\n" .
                    "git log -p -S 'keyword' $file\n\n" .
                    "git blame $file\n\n" .
                    "Ini membantu memahami konteks perubahan dan siapa yang bisa diajak diskusi."
                ),
            ],
            'assets' => [
                $this->ex(
                    'Audit dependencies',
                    "Periksa apakah ada dependency yang outdated:\n\n" .
                    "npm outdated\n" .
                    "npm audit\n\n" .
                    "Update dengan hati-hati:\n" .
                    "npm update\n" .
                    "npm audit fix\n\n" .
                    "Test thoroughly setelah update!"
                ),
            ],
            'js' => [
                $this->ex(
                    'Performance profiling',
                    "Gunakan DevTools untuk profiling:\n\n" .
                    "1. Performance tab → Record\n" .
                    "2. Interact dengan halaman\n" .
                    "3. Stop recording\n" .
                    "4. Analisis:\n" .
                    "   - Long tasks (>50ms)\n" .
                    "   - Layout thrashing\n" .
                    "   - Memory leaks\n\n" .
                    "Optimize berdasarkan findings."
                ),
            ],
            'features' => [
                $this->ex(
                    'User acceptance testing',
                    "Buat checklist UAT:\n\n" .
                    "□ Fitur bekerja sesuai requirement\n" .
                    "□ Error handling tepat\n" .
                    "□ Loading state ada\n" .
                    "□ Success/error message jelas\n" .
                    "□ Mobile responsive\n" .
                    "□ Accessible (keyboard navigation)\n" .
                    "□ Performance acceptable (<3s load)"
                ),
            ],
            default => [],
        };
    }

    private function rSyntax(array $c, array $m): array
    {
        $msg = $c['message'];
        $cur = $this->lineText($c);
        $prev = $c['line'] > 1 ? $this->lineText(['line' => $c['line'] - 1] + $c) : null;

        if (preg_match('/unexpected end of file/i', $msg)) {
            $why = 'File berakhir sebelum semua blok ditutup: ada kurung atau kurawal yang belum ditutup.';
            $steps = ['Hitung pasangan { } ( ) [ ] dari awal method/class; aktifkan Bracket Pair Colorization di VS Code.', 'Periksa juga string, komentar blok, atau heredoc yang belum ditutup.'];
        } elseif (preg_match('/unexpected token "[}\)\]]"/i', $msg)) {
            $why = 'Ada kurung/kurawal penutup berlebih, atau tanda ; hilang tepat sebelumnya.';
            $steps = ['Lihat baris ' . $c['line'] . ' dan baris di atasnya; cari penutup yang tidak punya pembuka.', 'Pastikan setiap statement sebelum penutup berakhir dengan tanda titik koma.'];
        } elseif (preg_match('/unexpected (?:token|identifier|variable|string|integer|double)/i', $msg)) {
            $why = 'Biasanya tanda ; atau , hilang pada baris SEBELUM yang dilaporkan (PHP melapor di token berikutnya).';
            $steps = ['Periksa akhir baris ' . max(1, $c['line'] - 1) . ': tambahkan ; atau , yang hilang.', 'Periksa kutip string yang tidak berpasangan pada baris itu.'];
        } elseif (preg_match('/Cannot redeclare|already in use|Cannot declare class/i', $msg)) {
            $why = 'Ada fungsi/class/import yang dideklarasikan dua kali.';
            $steps = ['Cari deklarasi dengan nama yang sama di file ini dan file yang di-require.', 'Hapus salah satu, atau beri alias pada use: use A\\B as C;'];
        } else {
            $why = 'Ada karakter atau tanda baca yang salah di sekitar baris tersebut.';
            $steps = ['Buka baris ' . $c['line'] . ' dan baca pesan PHP dengan teliti.', 'Bandingkan dengan sintaks yang benar; cek juga baris sebelumnya.'];
        }

        $steps[] = 'Validasi: php -l ' . ($c['file'] ?: 'file.php');

        return $this->sol(
            'File PHP tidak bisa di-parse',
            $why . ' Pesan PHP: ' . $msg . ($prev ? "\nBaris sebelumnya: " . trim($prev) : '') . ($cur ? "\nBaris terlapor: " . trim($cur) : ''),
            'Kesalahan sintaks membuat PHP tidak bisa mengeksekusi file ini sama sekali.',
            'Halaman yang memakai file ini akan menghasilkan error 500 dan tidak bisa dibuka.',
            'Perbaiki sintaks di baris ' . $c['line'],
            $steps,
            'php -l ' . ($c['file'] ?: 'file.php'),
            [
                $this->ex('Format otomatis dengan Pint', 'Setelah sintaks valid, jalankan ./vendor/bin/pint ' . ($c['file'] ?: '') . ' agar indentasi rapi dan kesalahan kurung lebih mudah terlihat.'),
                $this->ex('Aktifkan linter di editor', 'Pasang ekstensi PHP Intelephense di VS Code agar kesalahan sintaks langsung bergaris merah saat mengetik.'),
            ]
        );
    }

    private function rImport(array $c, array $m): array
    {
        $fq = ltrim($this->q($c['message'])[0] ?? '', '\\');
        $base = class_basename($fq);
        $same = array_values(array_diff($this->classIndex()[strtolower($base)] ?? [], [$fq]));
        $line = $this->lineText($c);

        if ($same) {
            return $this->sol(
                'Import mengarah ke namespace yang salah',
                "Baris {$c['line']} mengimpor $fq, padahal class $base ada di {$same[0]}.",
                'Namespace pada use statement tidak cocok dengan lokasi class yang sebenarnya.',
                'Class tidak akan ter-load, menghasilkan error "Class not found" saat method dipanggil.',
                'Ganti import ke namespace yang benar',
                ["Ubah baris use menjadi: use {$same[0]};", 'Jika class sengaja dipindah, cari semua pemakaian namespace lama: grep -rn "' . $fq . '" app routes'],
                'use ' . $same[0] . ';',
                [
                    $this->ex('Cari import lama lain', 'Class yang dipindah biasanya masih diimpor dengan namespace lama di file lain. Gunakan pencarian global di VS Code untuk "' . $fq . '".'),
                    $this->ex('Segarkan autoload', 'Jalankan composer dump-autoload setelah memindahkan class.'),
                ],
                $this->diff($line, $fq, $same[0])
            );
        }

        if (str_starts_with($fq, 'App\\')) {
            $path = 'app/' . str_replace('\\', '/', substr($fq, 4)) . '.php';
            $sim = $this->closest($base, $this->classNames());
            $steps = ["Class belum ada. Buat file $path atau jalankan perintah di bawah.", 'Pastikan namespace di dalam file sama dengan folder.'];
            if ($sim) {
                array_unshift($steps, 'Jika ini salah ketik, mungkin maksudnya: ' . implode(', ', $sim) . '.');
            }
            return $this->sol(
                'Class yang diimpor tidak ada di app/',
                "$fq tidak ditemukan di folder app/.",
                'File class belum dibuat, atau nama/namespace-nya salah ketik.',
                'Setiap pemanggilan class ini akan menghasilkan "Class not found" fatal error.',
                'Buat class tersebut atau perbaiki nama import',
                $steps,
                $this->makeCmd($fq),
                [
                    $this->ex('Hapus import yang tidak terpakai', 'Jika class memang tidak lagi dipakai, hapus baris use ini agar tidak menjadi error saat deploy.'),
                    $this->ex('Periksa nama folder', 'Di Linux huruf besar-kecil folder berpengaruh: App/Models berbeda dengan App/models.'),
                ]
            );
        }

        return $this->sol(
            'Class vendor tidak ditemukan',
            "$fq tidak bisa di-load.",
            'Package yang menyediakan class ini belum terpasang, atau namespace-nya salah.',
            'Class tidak akan ter-load; kode yang memakainya akan gagal.',
            'Periksa namespace atau pasang package-nya',
            ['Periksa ejaan namespace pada dokumentasi package.', 'Jika package belum terpasang: composer require nama/package lalu composer dump-autoload.', 'Bila package sudah ada, hapus vendor/ lalu composer install.'],
            'composer dump-autoload',
            [
                $this->ex('Cek versi PHP/ekstensi', 'Beberapa package butuh ekstensi PHP tertentu (mbstring, intl, gd). Lihat hasil php -m.'),
                $this->ex('Cek composer.json', 'Pastikan package ada di bagian require, bukan hanya require-dev, jika dipakai di production.'),
            ]
        );
    }

    private function rCase(array $c, array $m): array
    {
        $q = $this->q($c['message']);
        $wrong = $q[0] ?? '?';
        $right = $q[1] ?? '?';
        $isFile = $c['tab'] === 'assets' || str_contains($right, '/');
        $to = $isFile ? preg_replace('#^public/#', '', $right) : $right;

        if ($isFile) {
            return $this->sol(
                'Nama file asset beda huruf dengan yang dipanggil',
                "Ditulis '$wrong' tetapi file asli '$right'. Di Windows jalan, di Linux (server) menghasilkan 404.",
                'Sistem file Linux peka huruf besar-kecil, berbeda dengan Windows.',
                'Di production (Linux) asset akan 404, sehingga halaman kehilangan CSS/JS/gambar.',
                'Samakan penulisan di kode dengan nama file asli',
                ["Ubah referensi menjadi '$to' (tanpa 'public/').", 'Alternatif: ganti nama file asli, di Windows dua langkah agar git mendeteksi (lihat kode).'],
                "git mv -f $right $right.tmp\ngit mv $right.tmp " . str_replace($right, strtolower($right), $right),
                [
                    $this->ex('Matikan ignorecase di git', 'Jalankan git config core.ignorecase false agar git melacak perubahan huruf pada nama file.'),
                    $this->ex('Pakai huruf kecil untuk asset', 'Konvensi huruf kecil semua mencegah masalah serupa di masa depan.'),
                ],
                $this->diff($this->lineText($c), $wrong, $to)
            );
        }

        return $this->sol(
            'Penulisan nama class beda huruf',
            "Ditulis '$wrong' tetapi seharusnya '$right'. PHP di Windows toleran, tetapi autoload di Linux membedakan huruf besar-kecil.",
            'Autoloader PSR-4 di Linux mencocokkan nama file persis dengan nama class.',
            'Di production class tidak akan ter-load, menyebabkan error "Class not found".',
            "Tulis persis '$right'",
            ['Ubah penulisan pada baris ' . $c['line'] . '.', 'Gunakan fitur rename symbol (F2) di VS Code agar semua pemakaian ikut berubah.'],
            $right,
            [
                $this->ex('Samakan nama file', 'Nama file harus sama persis dengan nama class (PSR-4). Jika file bernama beda huruf, ganti nama file dengan git mv dua langkah.'),
                $this->ex('Matikan ignorecase di git', 'git config core.ignorecase false agar perubahan huruf pada nama file tidak hilang saat commit.'),
            ],
            $this->diff($this->lineText($c), $wrong, $right)
        );
    }

    private function rClassMissing(array $c, array $m): array
    {
        $name = ltrim($this->q($c['message'])[0] ?? '', '\\');
        $base = class_basename($name);
        $line = $this->lineText($c);
        $known = $this->knownClass($base);
        $cands = $this->classIndex()[strtolower($base)] ?? [];

        if (str_contains($c['message'], 'class global') || ($known && !$cands)) {
            $fq = $known ?: $base;
            return $this->sol(
                "Class $base dipakai tanpa import",
                "File ini ada di namespace, jadi $base dicari di namespace file ini, bukan di global.",
                'PHP mencari class di namespace file saat ini karena tidak ada use statement.',
                'Saat baris ini dieksekusi akan muncul fatal error "Class not found".',
                'Tambahkan import di bagian atas file',
                ['Tambahkan baris use di bawah deklarasi namespace.', 'Atau tulis dengan backslash di depan: \\' . $base . '::...'],
                "use $fq;",
                [
                    $this->ex('Pakai auto-import editor', 'Di VS Code dengan Intelephense, arahkan kursor ke nama class lalu pilih "Import class" (Ctrl+.).'),
                    $this->ex('Cek konflik nama', "Jika ada class lain bernama $base, beri alias: use $fq as Alias$base;"),
                ]
            );
        }

        if ($cands) {
            return $this->sol(
                "Class $base belum di-import",
                "$base ada di " . implode(', ', $cands) . ' tetapi tidak di-import pada file ini.',
                'Class ada di project, tetapi file ini belum tahu lokasinya karena belum ada use statement.',
                'Pemanggilan $base akan menghasilkan "Class not found" fatal error.',
                'Tambahkan import yang sesuai',
                ['Tambahkan use di bawah namespace.', count($cands) > 1 ? 'Ada lebih dari satu kandidat, pilih yang sesuai konteks: ' . implode(', ', $cands) : 'Hanya ada satu kandidat, aman dipakai.'],
                'use ' . $cands[0] . ';',
                [
                    $this->ex('Pakai auto-import editor', 'Intelephense menawarkan "Import class" (Ctrl+.) sehingga tidak perlu mengetik manual.'),
                    $this->ex('Periksa namespace file ini', 'Jika class berada di namespace yang sama dengan file ini, import tidak perlu; pastikan baris namespace file ini benar.'),
                ]
            );
        }
        
        return $this->classMissingSmart($c, $base);
        $sim = $this->closest($base, $this->classNames());
        return $this->sol(
            "Class $base tidak ditemukan",
            "$base tidak di-import dan tidak ada di folder app/.",
            'Class belum dibuat, atau salah ketik. Tidak ada file yang mendefinisikan class ini di project.',
            'Setiap pemanggilan $base akan gagal dengan "Class not found" fatal error.',
            $sim ? 'Perbaiki salah ketik atau buat class' : 'Buat class yang dimaksud',
            array_filter([
                $sim ? 'Mungkin maksudnya: ' . implode(', ', $sim) . '.' : null,
                'Jika class memang belum ada, buat dengan perintah di bawah.',
                'Jika dari package, pasang package-nya lalu import namespace yang benar.',
            ]),
            $this->makeCmd('App\\Models\\' . $base),
            [
                $this->ex('Cek huruf besar-kecil', 'Pastikan nama class dan nama file sama persis, termasuk huruf kapital.'),
                $this->ex('Segarkan autoload', 'composer dump-autoload setelah menambah class baru.'),
            ],
            null
        );
    }

    private function rViewMissing(array $c, array $m): array
    {
        $name = $this->q($c['message'])[0] ?? '';
        $path = 'resources/views/' . str_replace('.', '/', $name) . '.blade.php';
        $views = $this->viewNames();
        $ci = null;
        foreach ($views as $v) {
            if (strcasecmp($v, $name) === 0) {
                $ci = $v;
                break;
            }
        }

        if ($ci) {
            return $this->sol(
                'Nama view beda huruf dengan file',
                "view('$name') dipanggil, file aslinya bernama '$ci'. Di Linux ini dianggap tidak ada.",
                'Sistem file Linux peka huruf besar-kecil, berbeda dengan Windows.',
                'Di production view akan 404, menghasilkan error "View not found".',
                "Panggil view dengan nama '$ci'",
                ["Ganti pemanggilan menjadi view('$ci').", 'Atau ganti nama folder/file agar sama dengan yang dipanggil.'],
                $ci,
                [$this->ex('Matikan ignorecase di git', 'git config core.ignorecase false agar perubahan huruf pada nama file terlacak.'), $this->ex('Konvensi nama', 'Gunakan huruf kecil untuk folder dan file view agar aman di semua OS.')],
                $this->diff($this->lineText($c), $name, $ci)
            );
        }

        $sim = $this->closest($name, $views);
        return $this->sol(
            'File Blade tidak ditemukan',
            "view('$name') mengarah ke $path yang tidak ada.",
            'File view belum dibuat, atau nama view salah ketik.',
            'Halaman yang memanggil view ini akan error "View not found" (500).',
            $sim ? "Arahkan ke view yang ada: '{$sim[0]}'" : 'Buat file Blade tersebut',
            array_filter([
                $sim ? 'View yang mirip: ' . implode(', ', $sim) . '.' : null,
                "Jika memang baru, buat $path.",
                'Titik pada nama view = pemisah folder (admin.user.index → admin/user/index.blade.php).',
            ]),
            "php artisan make:view $name",
            [
                $this->ex('Periksa ekstensi', 'File harus berakhiran .blade.php, bukan .php atau .blade.html.'),
                $this->ex('Bersihkan cache view', 'php artisan view:clear bila file baru dipindahkan atau diganti nama.'),
            ]
        );
    }

    private function rRouteName(array $c, array $m): array
    {
        $name = $this->q($c['message'])[0] ?? '';
        $names = $this->routeNames();
        $sim = $this->closest($name, $names);
        $prefix = explode('.', $name)[0];
        $same = array_slice(array_values(array_filter($names, fn ($n) => str_starts_with($n, $prefix . '.'))), 0, 6);
        $line = $this->lineText($c);

        return $this->sol(
            'Nama route belum terdaftar',
            "route('$name') dipanggil tetapi tidak ada route dengan nama itu, sehingga halaman akan error (RouteNotFoundException).",
            'Nama yang dipanggil tidak ada di routes/*.php, mungkin salah ketik atau route belum diberi nama.',
            'Halaman yang memanggil route() ini akan error 500 "Route not defined".',
            $sim ? "Ganti ke route yang ada: '{$sim[0]}'" : "Beri nama route lewat ->name('$name')",
            array_filter([
                $sim ? 'Route yang mirip: ' . implode(', ', $sim) . '.' : null,
                "Jika route memang ada tanpa nama, tambahkan ->name('$name') di routes/*.php.",
                'Jika route berada dalam group, periksa ->name(\'prefix.\') pada group yang membentuk nama akhir.',
                'Setelah mengubah route: php artisan route:clear',
            ]),
            "php artisan route:list --name=" . $prefix,
            [
                $this->ex('Daftar route satu fitur', $same ? 'Route yang ada untuk "' . $prefix . '": ' . implode(', ', $same) . '.' : 'Tidak ada route yang diawali "' . $prefix . '." — kemungkinan seluruh grup belum didaftarkan.'),
                $this->ex('Lindungi pemanggilan opsional', "Bila route memang opsional, bungkus: @if(Route::has('$name')) ... @endif"),
            ],
            $sim ? $this->diff($line, $name, $sim[0]) : null
        );
    }

    private function rRouteDup(array $c, array $m): array
    {
        preg_match('/\s(\/\S*)\s+terdaftar/', $c['message'], $mm);
        $uri = $mm[1] ?? '/uri';

        return $this->sol(
            'Route terdaftar lebih dari sekali',
            $c['message'] . '. Hanya salah satu yang akan dipakai Laravel, sehingga perilaku bisa mengejutkan.',
            'URI yang sama didaftarkan dua kali, biasanya karena file route di-include ganda atau resource + manual.',
            'Hanya definisi terakhir yang aktif; definisi pertama tidak pernah dipanggil.',
            'Hapus atau bedakan salah satu definisi',
            ['Cari semua definisi dengan perintah di bawah.', 'Hapus yang tidak dipakai, atau ubah URI/method salah satunya.', 'Bila berbeda middleware/domain, gabung dalam satu definisi.'],
            'php artisan route:list --path=' . ltrim($uri, '/'),
            [
                $this->ex('Periksa include route', 'Duplikat sering muncul karena file route di-require dua kali (web.php dan file lain).'),
                $this->ex('Periksa route resource', 'Route::resource sudah membuat index/create/store/show/edit/update/destroy; hapus definisi manual yang bentrok.'),
            ]
        );
    }

    private function rRouteNameDup(array $c, array $m): array
    {
        $name = $this->q($c['message'])[0] ?? '';

        return $this->sol(
            'Nama route dipakai lebih dari satu route',
            "Nama '$name' ganda, jadi route('$name') hanya akan mengarah ke salah satunya.",
            'Dua route berbeda memakai ->name() yang sama, sehingga route() tidak bisa menentukan mana yang dipanggil.',
            'route() akan selalu menunjuk ke salah satu route saja, bisa jadi yang salah.',
            'Beri nama unik pada salah satu route',
            ["Cari route dengan nama itu: php artisan route:list --name=$name", 'Ubah salah satu menjadi nama yang menggambarkan fungsinya, misalnya prefix.aksi.', 'Perbarui semua route() di Blade/controller yang memakai nama lama.'],
            "php artisan route:list --name=$name",
            [
                $this->ex('Periksa group ->name()', 'Nama akhir adalah gabungan prefix group + nama route; dua group beda dengan prefix sama menghasilkan nama ganda.'),
                $this->ex('Jalankan Cek ulang route lain', 'Setelah mengganti nama, jalankan audit asset/fitur agar semua pemanggilan nama lama ketahuan.'),
            ]
        );
    }

    private function rRouteController(array $c, array $m): array
    {
        $class = ltrim($this->q($c['message'])[0] ?? '', '\\');
        $base = class_basename($class);
        $cands = $this->classIndex()[strtolower($base)] ?? [];
        $exact = null;
        foreach ($cands as $cd) {
            if (strcasecmp($cd, $class) === 0) {
                $exact = $cd;
            }
        }
        $line = $this->lineText($c);

        if ($exact && $exact !== $class) {
            return $this->sol(
                'Nama controller di route beda huruf',
                "Route menulis $class, file aslinya $exact. Di Linux controller tidak akan ketemu.",
                'Autoloader PSR-4 di Linux peka huruf besar-kecil.',
                'Di production route akan error "Target class does not exist" (500).',
                "Tulis persis $exact",
                ["Ganti penulisan di routes dan import ke $exact."],
                'use ' . $exact . ';',
                [$this->ex('Matikan ignorecase di git', 'git config core.ignorecase false.'), $this->ex('Cek nama file', 'Nama file harus sama persis dengan nama class.')],
                $this->diff($line, $class, $exact)
            );
        }

        if ($cands) {
            return $this->sol(
                'Controller ada di namespace lain',
                "Route memakai $class, controller bernama $base ada di " . implode(', ', $cands) . '.',
                'Controller ada di project, tetapi di namespace berbeda dengan yang dipanggil route.',
                'Route akan error "Target class does not exist" (500).',
                'Arahkan route ke namespace yang benar',
                ['Tambahkan/ubah import di routes/*.php.', 'Jika controller dipindah, pastikan namespace di file controller ikut diubah.'],
                'use ' . $cands[0] . ';',
                [$this->ex('Segarkan autoload dan route', 'composer dump-autoload && php artisan route:clear'), $this->ex('Cek import ganda', 'Pastikan tidak ada dua use dengan nama controller sama dari namespace berbeda.')]
            );
        }

        $sim = $this->closest($base, $this->classNames());
        return $this->sol(
            'Controller yang dipanggil route tidak ada',
            "Route memanggil $class tetapi class itu tidak ditemukan.",
            'File controller belum dibuat, atau salah ketik nama class.',
            'Route akan error "Target class does not exist" (500).',
            $sim ? 'Perbaiki nama atau buat controller' : 'Buat controller tersebut',
            array_filter([$sim ? 'Mungkin maksudnya: ' . implode(', ', $sim) . '.' : null, 'Jika belum ada, buat dengan perintah di bawah, lalu isi method yang dipanggil route.']),
            $this->makeCmd($class),
            [
                $this->ex('Periksa import di routes', 'Route::get(..., [XController::class, ...]) butuh use XController; di bagian atas file route.'),
                $this->ex('Jalankan audit:code', 'Audit Code akan menandai juga controller dengan nama class berbeda dari nama file.'),
            ]
        );
    }

    private function skeleton(string $method): string
    {
        return match ($method) {
            'index' => "public function index()\n{\n    \$data = Model::latest()->paginate(15);\n    return view('{view}', compact('data'));\n}",
            'create' => "public function create()\n{\n    return view('{view}');\n}",
            'store' => "public function store(Request \$request)\n{\n    \$data = \$request->validate([\n        // 'kolom' => 'required|string|max:255',\n    ]);\n    Model::create(\$data);\n    return redirect()->back()->with('success', 'Data berhasil disimpan');\n}",
            'show' => "public function show(\$id)\n{\n    \$row = Model::findOrFail(\$id);\n    return view('{view}', compact('row'));\n}",
            'edit' => "public function edit(\$id)\n{\n    \$row = Model::findOrFail(\$id);\n    return view('{view}', compact('row'));\n}",
            'update' => "public function update(Request \$request, \$id)\n{\n    \$data = \$request->validate([\n        // 'kolom' => 'required|string|max:255',\n    ]);\n    Model::findOrFail(\$id)->update(\$data);\n    return redirect()->back()->with('success', 'Data berhasil diubah');\n}",
            'destroy' => "public function destroy(\$id)\n{\n    Model::findOrFail(\$id)->delete();\n    return redirect()->back()->with('success', 'Data berhasil dihapus');\n}",
            default => "public function $method()\n{\n    // TODO\n}",
        };
    }

    private function rRouteMethod(array $c, array $m): array
    {
        preg_match('/([\\\\\w]+)::(\w+)\(\)/', $c['message'], $mm);
        $class = $mm[1] ?? '';
        $method = $mm[2] ?? '';
        $have = $this->publicMethods($class);
        $sim = $this->closest($method, $have);

        if ($sim) {
            return $this->sol(
                'Method di route berbeda dengan di controller',
                "Route memanggil $class::$method() padahal di controller adanya: " . implode(', ', $sim) . '.',
                'Nama method di route tidak cocok dengan method yang ada di controller.',
                'Route akan error "Method does not exist" (500).',
                "Ganti ke method '{$sim[0]}' (atau ubah nama method di controller)",
                ["Ubah definisi route agar memakai '{$sim[0]}'.", "Jika nama di route yang benar, ubah nama method di controller menjadi $method."],
                "[" . class_basename($class) . "::class, '{$sim[0]}']",
                [
                    $this->ex('Daftar method publik', 'Method publik yang ada di controller: ' . implode(', ', $have) . '.'),
                    $this->ex('Hati-hati huruf besar-kecil', 'Nama method di PHP tidak peka huruf, tetapi tetap samakan agar konsisten dengan route:list.'),
                ]
            );
        }

        return $this->sol(
            'Method controller belum dibuat',
            "Route memanggil $class::$method() tetapi method itu tidak ada" . ($have ? '. Method yang ada: ' . implode(', ', $have) . '.' : '.'),
            'Method yang dipanggil route belum ditulis di controller.',
            'Route akan error "Method does not exist" (500).',
            "Tambahkan method $method ke controller",
            ['Buka file controller dan tambahkan method publik dengan nama persis sama dengan di route.', 'Ganti Model dan nama view pada kerangka sesuai fitur Anda.'],
            $this->skeleton($method),
            [
                $this->ex('Pastikan import Request', 'Jika memakai Request $request, tambahkan use Illuminate\\Http\\Request; di controller.'),
                $this->ex('Atau hapus route yatim', 'Jika fitur ini tidak dipakai lagi, hapus definisi routenya agar audit tidak terus melapor.'),
            ]
        );
    }

    private function rMiddleware(array $c, array $m): array
    {
        $names = $this->q($c['message']) ?: array_map('trim', explode(',', $c['message']));
        $name = $names[0] ?? '';
        $aliases = array_keys(app('router')->getMiddleware());
        $sim = $this->closest($name, $aliases);
        $new = is_file(base_path('bootstrap/app.php')) && str_contains((string) file_get_contents(base_path('bootstrap/app.php')), 'withMiddleware');

        $code = $new
            ? "// bootstrap/app.php\n->withMiddleware(function (Middleware \$middleware) {\n    \$middleware->alias([\n        '$name' => \\App\\Http\\Middleware\\NamaMiddleware::class,\n    ]);\n})"
            : "// app/Http/Kernel.php\nprotected \$middlewareAliases = [\n    '$name' => \\App\\Http\\Middleware\\NamaMiddleware::class,\n];";

        return $this->sol(
            'Middleware dipakai tetapi tidak terdaftar',
            "Middleware '$name' dipanggil oleh route, tetapi tidak ada alias dengan nama itu, sehingga request akan error.",
            'Alias middleware belum didaftarkan, atau package yang menyediakannya belum terpasang.',
            'Request akan error "Middleware not found" (500).',
            $sim ? "Ganti ke alias yang ada: '{$sim[0]}'" : 'Daftarkan alias middleware',
            array_filter([
                $sim ? 'Alias yang mirip: ' . implode(', ', $sim) . '.' : null,
                $new ? 'Daftarkan alias di bootstrap/app.php (Laravel 11+).' : 'Daftarkan alias di app/Http/Kernel.php.',
                'Pastikan class middleware ada: php artisan make:middleware NamaMiddleware',
            ]),
            $code,
            [
                $this->ex('Alias tersedia', 'Alias yang terdaftar saat ini: ' . implode(', ', array_slice($aliases, 0, 12)) . '.'),
                $this->ex('Package belum terpasang', 'Alias seperti role, permission, atau throttle berasal dari package; pastikan package terpasang dan service provider-nya aktif.'),
            ]
        );
    }

    private function rClassFile(array $c, array $m): array
    {
        $q = $this->q($c['message']);
        $cls = $q[0] ?? '';
        $file = preg_replace('/\.php$/', '', $q[1] ?? '');

        return $this->sol(
            'Nama class berbeda dengan nama file',
            "File $file.php berisi class $cls. PSR-4 butuh keduanya sama persis, kalau tidak class tidak bisa di-autoload di Linux.",
            'PSR-4 autoloader mencocokkan nama file dengan nama class secara persis.',
            'Di Linux class tidak akan ter-load, menghasilkan "Class not found" fatal error.',
            "Samakan menjadi $file",
            ["Ubah deklarasi menjadi: class $file", "Atau ganti nama file menjadi $cls.php, lalu perbarui semua import dan route yang memakainya.", 'Jalankan composer dump-autoload.'],
            "class $file extends Controller",
            [
                $this->ex('Cari pemakaian nama lama', "Cari \"$cls\" dan \"$file\" di seluruh project agar tidak ada route/import yang tertinggal."),
                $this->ex('Ganti nama file di git', 'Bila hanya huruf yang berbeda, pakai git mv dua langkah (nama sementara) agar tercatat.'),
            ],
            $this->diff($this->lineText($c), $cls, $file)
        );
    }

    private function rNamespace(array $c, array $m): array
    {
        $fq = ltrim($this->q($c['message'])[0] ?? '', '\\');
        $ns = str_contains($fq, '\\') ? substr($fq, 0, strrpos($fq, '\\')) : '';

        return $this->sol(
            'Namespace tidak sesuai dengan folder',
            "$fq tidak bisa di-load. Namespace harus mengikuti struktur folder (PSR-4 'App\\' → app/).",
            'Namespace di file tidak cocok dengan lokasi folder, sehingga autoloader tidak bisa menemukan class.',
            'Class tidak akan ter-load, menghasilkan "Class not found" fatal error.',
            "Set namespace menjadi $ns",
            ["Ubah baris namespace di file menjadi: namespace $ns;", 'Pastikan huruf besar-kecil folder sama persis.', 'Pastikan composer.json punya "autoload": {"psr-4": {"App\\\\": "app/"}} lalu composer dump-autoload.'],
            "namespace $ns;",
            [
                $this->ex('Periksa class induk', 'Class bisa gagal di-load karena extends/implements/trait yang tidak ada, bukan namespace-nya. Periksa import di atas file.'),
                $this->ex('Periksa karakter tersembunyi', 'BOM atau spasi sebelum <?php juga dapat memicu masalah. Simpan file sebagai UTF-8 tanpa BOM.'),
            ]
        );
    }

    private function rControllerDecl(array $c, array $m): array
    {
        $base = $c['file'] ? basename($c['file'], '.php') : 'NamaController';

        return $this->sol(
            'File controller tidak punya class',
            'Tidak ada deklarasi class yang valid di file ini (kosong, typo seperti "calss", atau hanya berisi fungsi).',
            'File controller tidak memiliki deklarasi class yang valid.',
            'Route yang memanggil controller ini akan error "Target class does not exist" (500).',
            'Tambahkan deklarasi class',
            ['Isi file dengan kerangka class yang namanya sama dengan nama file.', 'Atau hapus file jika memang tidak dipakai.'],
            "<?php\nnamespace App\\Http\\Controllers;\nclass $base extends Controller\n{\n    //\n}",
            [
                $this->ex('Periksa tag pembuka', 'Pastikan file diawali <?php (bukan <? atau spasi).'),
                $this->ex('Buat ulang dengan artisan', "php artisan make:controller $base lalu salin method yang masih diperlukan."),
            ]
        );
    }

    private function rMethodNoRoute(array $c, array $m): array
    {
        preg_match('/(\w+)::(\w+)\(\)/', $c['message'], $mm);
        $cls = $mm[1] ?? 'Controller';
        $fn = $mm[2] ?? 'method';

        return $this->sol(
            'Method publik tidak terhubung ke route',
            "$cls::$fn() bisa jadi fitur yang belum didaftarkan routenya, atau method bantu yang tidak sengaja publik.",
            'Method publik ada tetapi tidak dipanggil oleh route manapun.',
            'Method ini tidak bisa diakses dari browser; kemungkinan fitur belum selesai atau method seharusnya private.',
            'Daftarkan route atau ubah visibilitas',
            ["Jika ini halaman/aksi: tambahkan route yang memanggilnya.", "Jika hanya helper internal: ubah menjadi private/protected.", 'Jika sudah tidak dipakai: hapus method-nya.'],
            "Route::get('/{uri}', [$cls::class, '$fn'])->name('{nama}');",
            [
                $this->ex('Periksa pemanggilan non-route', 'Method bisa dipanggil dari route resource, invokable, atau package lain; abaikan warning ini jika memang begitu.'),
                $this->ex('Periksa route yang dikomentari', 'Cari baris route yang dinonaktifkan dengan // di routes/*.php.'),
            ]
        );
    }

    private function rAssetMissing(array $c, array $m): array
    {
        $path = preg_match('#public/(\S+) tidak#', $c['message'], $mm) ? $mm[1] : ($this->q($c['message'])[0] ?? '');
        $path = ltrim($path, '/');
        $base = basename($path);
        $files = $this->publicFiles();
        $exact = array_values(array_filter($files, fn ($f) => strcasecmp(basename($f), $base) === 0));
        $sim = $exact ?: $this->closest($base, array_map('basename', $files));
        $line = $this->lineText($c);

        if ($exact) {
            return $this->sol(
                'Asset ada, tetapi di folder lain',
                "$path tidak ada, namun file bernama $base ditemukan di: " . implode(', ', array_slice($exact, 0, 3)) . '.',
                'Path yang ditulis tidak cocok dengan lokasi file yang sebenarnya.',
                'Browser akan menerima 404 untuk asset ini, sehingga halaman kehilangan CSS/JS/gambar.',
                'Arahkan ke lokasi file yang benar',
                ["Ubah referensi menjadi asset('{$exact[0]}').", 'Atau pindahkan file ke ' . $path . ' jika memang lokasi itu yang diinginkan.'],
                "{{ asset('{$exact[0]}') }}",
                [$this->ex('Periksa huruf besar-kecil', 'Di Linux CSS/App.css berbeda dari css/app.css.'), $this->ex('Cari referensi lain', 'Cari "' . $path . '" di resources/views dan CSS agar semua referensi diperbaiki sekaligus.')],
                $this->diff($line, $path, $exact[0])
            );
        }

        if (str_starts_with($path, 'storage/')) {
            $steps = ['Link public/storage belum dibuat atau file belum diunggah.', 'Buat symlink dengan perintah di bawah.', 'Pastikan file ada di storage/app/public/' . substr($path, 8) . '.'];
            $code = 'php artisan storage:link';
        } elseif (str_starts_with($path, 'build/') || str_starts_with($path, 'js/') || str_starts_with($path, 'css/')) {
            $steps = ['Jika file dihasilkan bundler, build ulang: npm install && npm run build.', 'Jika file ditulis manual, letakkan di public/' . $path . '.', 'Periksa juga vite.config.js / webpack.mix.js bila path output diubah.'];
            $code = 'npm install && npm run build';
        } else {
            $steps = ["Letakkan file di public/$path atau ubah referensi ke file yang ada.", $sim ? 'File mirip: ' . implode(', ', array_slice($sim, 0, 3)) . '.' : 'Tidak ada file mirip di folder public.', 'Jika memakai CDN, gunakan URL penuh https://… dan jangan asset().'];
            $code = "{{ asset('$path') }}";
        }

        return $this->sol(
            'File asset tidak ditemukan di public/',
            "public/$path tidak ada, jadi browser akan menerima 404 untuk file ini.",
            'File asset tidak ada di folder public/. Bisa karena belum di-build, belum diunggah, atau path salah.',
            'Browser akan menerima 404; halaman kehilangan CSS/JS/gambar.',
            'Sediakan file atau perbaiki referensinya',
            $steps,
            $code,
            [
                $this->ex('Periksa huruf besar-kecil', 'Nama file harus sama persis (Linux peka huruf).'),
                $this->ex('Gunakan helper asset()', 'asset() membuat URL dari public/ dan aman saat aplikasi berada di sub-folder.'),
            ]
        );
    }

    private function rAssetUnreadable(array $c, array $m): array
    {
        $path = preg_match('#public/(\S+)#', $c['message'], $mm) ? $mm[1] : '';
        $isStorage = str_starts_with($path, 'storage');

        return $this->sol(
            'Asset tidak bisa dibaca',
            $c['message'] . ($isStorage ? ' Symlink storage kemungkinan rusak (target pindah/terhapus).' : ''),
            'File tidak bisa dibaca karena izin, symlink rusak, atau file tidak ada.',
            'Browser akan menerima 403/500 untuk asset ini.',
            $isStorage ? 'Buat ulang symlink storage' : 'Perbaiki file/izin akses',
            $isStorage
                ? ['Hapus link lama: rm public/storage (Windows: rmdir public\\storage).', 'Buat ulang dengan perintah di bawah.']
                : ['Pastikan file ada dan bukan symlink yang rusak.', 'Periksa izin file (chmod 644) dan folder (chmod 755).'],
            $isStorage ? 'php artisan storage:link' : 'ls -l public/' . $path,
            [
                $this->ex('Windows butuh hak admin', 'Membuat symlink di Windows biasanya perlu terminal Administrator atau Developer Mode aktif.'),
                $this->ex('Setelah deploy', 'Pastikan storage:link dijalankan lagi di server karena symlink tidak ikut di-commit.'),
            ]
        );
    }

    private function rAssetEmpty(array $c, array $m): array
    {
        return $this->sol(
            'File asset berukuran 0 byte',
            $c['message'] . '. File yang kosong tidak memberi efek apa pun dan sering menandakan proses build/unggah yang gagal.',
            'File ada tetapi kosong, biasanya karena proses build/unggah gagal.',
            'Asset tidak memberi efek apa pun; halaman kehilangan style/script.',
            'Isi ulang file dari sumbernya',
            ['Jika hasil build: jalankan npm run build dan pastikan tidak ada error.', 'Jika file unggahan: unggah ulang atau ambil dari repositori.', 'Jika file memang placeholder, hapus referensinya.'],
            'npm run build',
            [
                $this->ex('Periksa Git LFS/gitignore', 'File besar bisa terpotong bila memakai LFS yang belum di-pull, atau tidak ikut ter-commit karena .gitignore.'),
                $this->ex('Periksa disk dan izin', 'Penulisan file gagal bila disk penuh atau folder tidak writable.'),
            ]
        );
    }

    private function rVite(array $c, array $m): array
    {
        $t = $c['type'];
        $entry = preg_match("/^'?([^'\s]+)'?\s/", $c['message'], $mm) ? $mm[1] : '';

        if ($t === 'Vite entry') {
            return $this->sol(
                'Entry Vite menunjuk file yang tidak ada',
                $c['message'] . '. @vite() akan gagal saat dirender.',
                'Path entry di @vite() tidak cocok dengan file di resources/.',
                'Halaman akan error saat render karena @vite() tidak bisa menemukan entry.',
                'Perbaiki path entry atau buat filenya',
                ['Samakan path di @vite([...]) dengan file di resources/.', 'Periksa juga "input" di vite.config.js.', 'Perhatikan huruf besar-kecil.'],
                "@vite(['resources/css/app.css', 'resources/js/app.js'])",
                [$this->ex('Cek vite.config.js', 'Semua entry yang dipanggil @vite harus tercantum pada laravel({ input: [...] }).'), $this->ex('Bangun ulang', 'Setelah mengubah input, jalankan npm run build.')]
            );
        }

        if ($t === 'Vite manifest') {
            return $this->sol(
                'Entry belum ada di manifest build',
                $c['message'] . '. Build lama tidak memuat entry ini.',
                'Entry tidak ada di manifest.json karena belum di-build atau input vite.config.js belum diperbarui.',
                '@vite() akan error saat render karena entry tidak ada di manifest.',
                'Tambahkan entry ke vite.config.js lalu build ulang',
                ["Pastikan '$entry' ada di array input pada vite.config.js.", 'Jalankan npm run build (production) atau npm run dev (development).'],
                'npm run build',
                [$this->ex('Hapus build lama', 'Hapus public/build lalu build ulang bila manifest terasa usang.'), $this->ex('Deploy', 'public/build biasanya di-ignore git; jalankan build di server/CI.')]
            );
        }

        return $this->sol(
            'Build Vite belum ada',
            'public/build/manifest.json tidak ditemukan, sehingga @vite() tidak bisa menyisipkan CSS/JS.',
            'Build Vite belum dijalankan, sehingga manifest.json belum ada.',
            '@vite() akan error saat render; halaman kehilangan semua CSS/JS.',
            'Jalankan build atau dev server',
            ['Development: npm run dev (membuat file public/hot).', 'Production/staging: npm install lalu npm run build.', 'Pastikan Node.js terpasang (node -v).'],
            "npm install\nnpm run build",
            [$this->ex('Audit saat dev server aktif', 'Jika npm run dev berjalan, audit mengabaikan manifest. Pastikan jalan di terminal terpisah.'), $this->ex('CI/CD', 'Tambahkan langkah npm ci && npm run build di pipeline deploy.')]
        );
    }

    private function rMix(array $c, array $m): array
    {
        return $this->sol(
            'Manifest Laravel Mix bermasalah',
            $c['message'],
            'Manifest Mix tidak valid atau belum di-build.',
            'mix() akan error saat render; halaman kehilangan CSS/JS.',
            'Compile ulang asset dengan Mix',
            ['Jalankan npm install lalu npm run prod (atau npm run dev).', 'Pastikan file yang dipanggil mix() terdaftar di webpack.mix.js.'],
            "npm install\nnpm run prod",
            [$this->ex('Periksa webpack.mix.js', 'Setiap mix.js()/mix.css() menentukan nama output; samakan dengan yang dipanggil di Blade.'), $this->ex('Pertimbangkan Vite', 'Laravel baru memakai Vite yang lebih cepat dan lebih sedikit konfigurasi.')]
        );
    }

    private function rExternal(array $c, array $m): array
    {
        $url = preg_match('#(https?://\S+|//\S+)#', $c['message'], $mm) ? $mm[1] : '';

        return $this->sol(
            'Asset CDN tidak bisa diakses',
            $c['message'] . '. Halaman akan kehilangan style/skrip dari CDN ini.',
            'URL CDN tidak bisa diakses dari server atau browser.',
            'Halaman kehilangan style/skrip dari CDN ini.',
            'Perbaiki URL atau host-kan lokal',
            ["Buka $url di browser untuk memastikan URL/versi masih ada.", 'Jika offline atau diblokir, unduh file lalu simpan di public/vendor dan panggil lewat asset().', 'Atau pasang lewat npm dan bundel dengan Vite.'],
            "<script src=\"{{ asset('vendor/nama/lib.min.js') }}\"></script>",
            [
                $this->ex('Cek koneksi server', 'Server/staging mungkin tidak punya akses internet atau memblokir domain CDN.'),
                $this->ex('Kunci versi', 'Gunakan URL berversi (mis. @3.7.1) agar tidak berubah/hilang saat CDN memperbarui.'),
            ]
        );
    }

    private function rJsSyntax(array $c, array $m): array
    {
        $msg = $c['message'];
        $inline = str_contains($c['type'], 'inline');

        if (str_contains($msg, 'Unexpected end of input')) {
            $why = 'Ada { ( [ yang belum ditutup.';
            $steps = ['Hitung pasangan kurung/kurawal pada blok script.'];
        } elseif (preg_match('/missing \) after argument list/i', $msg)) {
            $why = 'Pemanggilan fungsi kehilangan ) atau , antar argumen.';
            $steps = ['Periksa tanda koma antar argumen dan kurung penutup pada baris terlapor (dan baris sebelumnya).'];
        } elseif (preg_match('/Invalid or unexpected token/i', $msg)) {
            $why = 'Ada kutip yang tidak berpasangan atau karakter aneh.';
            $steps = ['Periksa tanda kutip; jika memasukkan data dari PHP, gunakan @json($var) agar kutip di-escape otomatis.'];
        } elseif (preg_match('/Unexpected token/i', $msg)) {
            $why = 'Biasanya koma/titik koma/kurung hilang atau berlebih.';
            $steps = ['Periksa baris terlapor dan baris sebelumnya.'];
        } else {
            $why = 'Struktur kode JavaScript tidak valid.';
            $steps = ['Baca pesan error dan periksa baris terlapor.'];
        }

        if ($inline) {
            $steps[] = 'Ini script inline di Blade; pastikan sintaks Blade ({{ }}, @json) menghasilkan JS yang valid, dan nilai string diberi kutip.';
        }
        $steps[] = 'Validasi: node --check berkas yang diperbaiki (atau jalankan Sinkron tab ini).';

        return $this->sol(
            'JavaScript tidak valid',
            $why . ' Pesan: ' . $msg,
            'Kode JavaScript memiliki kesalahan sintaks sehingga tidak bisa dieksekusi.',
            'Script tidak akan berjalan; fitur yang bergantung padanya tidak berfungsi.',
            'Perbaiki sintaks di baris ' . $c['line'],
            $steps,
            $inline ? "const data = @json(\$data);\nconst nama = @json(\$nama);" : null,
            [
                $this->ex('Pindahkan JS inline ke file', 'Script besar lebih mudah dicek (node --check) dan di-cache jika dipindah ke public/js atau resources/js.'),
                $this->ex('Aktifkan ESLint/Prettier', 'Linter menandai kesalahan kurung dan kutip sebelum dijalankan di browser.'),
            ]
        );
    }

    private function rJsElement(array $c, array $m): array
    {
        $id = preg_match('/#([\w\-]+)/', $c['message'], $mm) ? $mm[1] : '';
        $ids = $this->viewIds();
        $inViews = array_keys($ids[$id] ?? []);
        $sim = $this->closest($id, array_keys($ids));

        if ($inViews) {
            return $this->sol(
                'Elemen ada di halaman lain, bukan di halaman ini',
                "JS mencari #$id. Elemen itu ada di view: " . implode(', ', array_slice($inViews, 0, 3)) . ', tetapi tidak ikut termuat di halaman yang diaudit.',
                'JS mencari elemen yang hanya ada di halaman lain.',
                'JS tidak menemukan elemen, sehingga logika yang bergantung padanya tidak berjalan.',
                'Muat JS hanya di halaman yang punya elemen, atau tambahkan elemen',
                ['Jika JS dipakai bersama, bungkus dengan pengecekan elemen (lihat kode).', 'Atau pindahkan tag <script> ke view yang memang punya elemen tersebut (@push).'],
                "const el = document.getElementById('$id');\nif (el) {\n    // logika yang memakai el\n}",
                [$this->ex('Gunakan @push/@stack', 'Letakkan script per halaman dengan @push(\'scripts\') agar tidak termuat di halaman lain.'), $this->ex('Periksa include', 'Pastikan @include partial yang memuat elemen itu tidak terlewat.')]
            );
        }

        return $this->sol(
            'JS mencari elemen yang tidak ada',
            "Tidak ada elemen dengan id=\"$id\" di Blade manapun." . ($sim ? ' ID yang mirip: ' . implode(', ', $sim) . '.' : ''),
            'ID elemen tidak ada di Blade manapun, atau salah ketik.',
            'JS tidak menemukan elemen; logika yang bergantung padanya tidak berjalan.',
            $sim ? "Samakan id dengan '{$sim[0]}' atau tambahkan elemen" : 'Tambahkan elemen atau hapus kode JS-nya',
            array_filter([$sim ? "Mungkin typo; ID mirip: " . implode(', ', $sim) : null, "Tambahkan id=\"$id\" pada elemen yang dimaksud.", 'Jika kode sudah tidak dipakai, hapus bagian JS yang mencari elemen ini.']),
            "const el = document.getElementById('$id');\nif (!el) return;",
            [
                $this->ex('Pastikan ID unik', 'Satu id hanya boleh dipakai satu elemen; selain itu getElementById mengambil yang pertama saja.'),
                $this->ex('Periksa elemen dinamis', 'Jika elemen dibuat lewat JS/AJAX, gunakan event delegation: $(document).on("click", "#' . $id . '", ...)'),
            ]
        );
    }

    private function rJsScope(array $c, array $m): array
    {
        $fn = preg_match('/^(\w+)\(\)/', $c['message'], $mm) ? $mm[1] : 'fungsi';

        return $this->sol(
            'Fungsi tidak bisa dipanggil dari atribut HTML',
            "$fn() didefinisikan di dalam scope/module, bukan global, sehingga onclick=\"$fn()\" menghasilkan 'is not defined'.",
            'Fungsi didefinisikan dalam module/IIFE, tidak tersedia di global scope.',
            'onclick/onchange tidak bisa memanggil fungsi ini; akan error "is not defined".',
            'Pasang event lewat JavaScript (disarankan)',
            ["Ganti onclick pada HTML dengan data-attribute dan addEventListener.", "Alternatif cepat: ekspor ke global dengan window.$fn = $fn;"],
            "document.querySelectorAll('[data-aksi=\"$fn\"]').forEach(function (el) {\n    el.addEventListener('click', $fn);\n});\n// atau cepat:\nwindow.$fn = $fn;",
            [
                $this->ex('Module butuh pengikatan manual', 'File type="module" dan hasil bundler tidak membuat fungsi global; selalu pasang event dari dalam module.'),
                $this->ex('Hindari atribut onclick', 'Event listener lebih mudah dites dan aman untuk Content-Security-Policy.'),
            ]
        );
    }

    private function rJsNotLoaded(array $c, array $m): array
    {
        preg_match('/^(\w+)\(\) didefinisikan di (\S+?):\d+ .*halaman \'([^\']+)\'/', $c['message'], $mm);
        $fn = $mm[1] ?? 'fungsi';
        $js = $mm[2] ?? 'js/file.js';
        $view = $mm[3] ?? 'view';
        $web = preg_replace('#^public/#', '', $js);

        return $this->sol(
            'File JS yang berisi fungsi tidak dimuat di halaman',
            "$fn() ada di $js, tetapi halaman '$view' tidak memuat file itu.",
            'File JS yang mendefinisikan fungsi tidak di-include di halaman ini.',
            'Fungsi tidak terdefinisi saat dipanggil; akan error "is not defined".',
            'Muat file JS di halaman tersebut',
            ["Tambahkan tag script di view '$view' (atau layout bila dipakai banyak halaman).", 'Pastikan layout punya @stack(\'scripts\') bila memakai @push.', 'Muat setelah library yang dibutuhkannya (mis. jQuery).'],
            "@push('scripts')\n<script src=\"{{ asset('$web') }}\"></script>\n@endpush",
            [
                $this->ex('Muat di layout bila sering dipakai', 'Jika banyak halaman memakai fungsi ini, tempatkan script di layout utama sebelum </body>.'),
                $this->ex('Periksa path & cache', 'Pastikan path benar dan lakukan hard refresh (Ctrl+F5) agar bukan versi lama di cache.'),
            ]
        );
    }

    private function rJsFn(array $c, array $m): array
    {
        $fn = preg_match('/^(\w+)\(\)/', $c['message'], $mm) ? $mm[1] : '';
        $sim = $this->closest($fn, $this->jsFunctions());

        return $this->sol(
            'Fungsi JS dipanggil tetapi tidak ditemukan',
            "$fn() dipanggil dari HTML, tetapi tidak didefinisikan di file JS project." . ($sim ? ' Fungsi yang mirip: ' . implode(', ', $sim) . '.' : ''),
            'Fungsi dipanggil tetapi tidak ada definisinya di file JS yang dimuat.',
            'Akan error "is not defined" saat dipanggil.',
            $sim ? "Ganti ke '{$sim[0]}' atau buat fungsi $fn" : "Definisikan fungsi $fn",
            array_filter([$sim ? 'Periksa salah ketik: ' . implode(', ', $sim) : null, "Buat function $fn() { ... } di file JS yang dimuat halaman ini.", 'Jika berasal dari library, pastikan library dimuat sebelum pemakaian.']),
            "function $fn() {\n    // TODO\n}\nwindow.$fn = $fn;",
            [
                $this->ex('Periksa urutan script', 'Fungsi harus sudah didefinisikan saat dipanggil (script dimuat sebelum event dijalankan).'),
                $this->ex('Periksa bundler', 'Jika memakai Vite/Mix, fungsi di dalam bundle tidak global kecuali diekspos lewat window.'),
            ]
        );
    }

    private function rJsLib(array $c, array $m): array
    {
        $lib = preg_match('/Memakai (.+?) tetapi/', $c['message'], $mm) ? $mm[1] : '';
        $cdn = [
            'jQuery' => 'https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js',
            'axios' => 'https://cdn.jsdelivr.net/npm/axios@1.7.9/dist/axios.min.js',
            'SweetAlert' => 'https://cdn.jsdelivr.net/npm/sweetalert2@11',
            'Bootstrap JS' => 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js',
            'Chart.js' => 'https://cdn.jsdelivr.net/npm/chart.js',
            'moment' => 'https://cdn.jsdelivr.net/npm/moment@2.30.1/min/moment.min.js',
            'DataTables' => 'https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js',
            'select2' => 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
        ][$lib] ?? null;
        $needJq = in_array($lib, ['DataTables', 'select2'], true);

        return $this->sol(
            "Library $lib dipakai tetapi tidak dimuat",
            $c['message'] . '. Kode akan melempar "is not defined" di browser.',
            'Library belum dimuat di halaman, atau dimuat setelah script yang memakainya.',
            'Semua kode yang memakai library ini akan error "is not defined".',
            "Muat $lib sebelum script yang memakainya",
            array_filter([
                "Tambahkan tag script $lib di layout/halaman, sebelum script yang memakainya.",
                $needJq ? 'Plugin ini butuh jQuery; muat jQuery lebih dulu.' : null,
                'Sesuaikan versi dengan kebutuhan project; lebih baik hosting lokal untuk production.',
            ]),
            $cdn ? "<script src=\"$cdn\"></script>" : "npm install " . strtolower($lib),
            [
                $this->ex('Pasang lewat npm', 'Untuk production, pasang via npm dan impor di resources/js/app.js agar tidak tergantung CDN.'),
                $this->ex('Hindari dobel muat', 'Pastikan library tidak dimuat dua kali (mis. jQuery di layout dan di halaman).'),
            ]
        );
    }

    private function rCsrfAjax(array $c, array $m): array
    {
        return $this->sol(
            'Request AJAX berisiko error 419 (CSRF)',
            $c['message'],
            'Request POST/PUT/DELETE tidak menyertakan token CSRF.',
            'Request akan ditolak dengan 419 Page Expired.',
            'Kirim token CSRF pada setiap request non-GET',
            ['Tambahkan <meta name="csrf-token" content="{{ csrf_token() }}"> di <head> layout.', 'Setel header X-CSRF-TOKEN secara global (jQuery atau fetch/axios).'],
            "<meta name=\"csrf-token\" content=\"{{ csrf_token() }}\">\n// jQuery\n\$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': \$('meta[name=\"csrf-token\"]').attr('content') } });\n// fetch\nfetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]').content } });",
            [
                $this->ex('Axios otomatis', 'Axios bawaan Laravel membaca cookie XSRF-TOKEN; pastikan bootstrap.js terpasang.'),
                $this->ex('Jangan matikan proteksi', 'Hindari menambah URL ke $except di VerifyCsrfToken kecuali endpoint webhook.'),
            ]
        );
    }

    private function rJsAjax(array $c, array $m): array
    {
        $msg = $c['message'];

        if (preg_match("/hanya menerima (.+), JS memanggil (\w+)/", $msg, $mm)) {
            return $this->sol(
                'Method AJAX tidak sesuai dengan route',
                $msg,
                'Method HTTP yang dipakai JS tidak diizinkan oleh route.',
                'Request akan ditolak dengan 405 Method Not Allowed.',
                "Gunakan method {$mm[1]} di JS atau ubah route",
                ["Ubah method pemanggilan di JS menjadi salah satu dari: {$mm[1]}.", "Atau ubah route menjadi Route::" . strtolower($mm[2]) . '(...)'],
                null,
                [$this->ex('Spoofing method', 'Untuk PUT/PATCH/DELETE lewat form, kirim POST dengan field _method.'), $this->ex('Cek route:list', 'php artisan route:list menampilkan method yang diizinkan untuk setiap URI.')]
            );
        }

        if (preg_match('/^(\w+) (\S+) tidak cocok/', $msg, $mm)) {
            $sim = $this->closest($mm[2], $this->routeUris(), 4);
            return $this->sol(
                'URL AJAX tidak punya route',
                $msg . ($sim ? ' URL mirip: ' . implode(', ', $sim) : ''),
                'URL yang dipanggil JS tidak terdaftar di routes/*.php.',
                'Request akan 404 Not Found.',
                $sim ? "Ganti URL ke '{$sim[0]}' atau daftarkan route" : 'Daftarkan route untuk URL ini',
                array_filter([$sim ? 'URL mirip: ' . implode(', ', $sim) : null, 'Tambahkan route di routes/web.php atau api.php.', 'Gunakan route() dari Blade agar URL tidak ditulis manual.']),
                "url: \"{{ route('{nama.route}') }}\"",
                [$this->ex('Periksa prefix', 'Route di api.php otomatis berawalan /api.'), $this->ex('Periksa base path', 'Jika aplikasi di sub-folder, URL manual berawalan / akan salah; pakai route()/url().')]
            );
        }

        preg_match('/(\S+) ada, tetapi method (\w+)/', $msg, $mm);
        $path = $mm[1] ?? '';
        $methods = [];
        foreach (Route::getRoutes()->getRoutes() as $r) {
            if ('/' . ltrim($r->uri(), '/') === $path) {
                $methods = array_merge($methods, $r->methods());
            }
        }

        return $this->sol(
            'Method AJAX tidak diizinkan untuk URL ini',
            $msg . ($methods ? ' Route menerima: ' . implode('|', array_unique($methods)) . '.' : ''),
            'Method HTTP yang dipakai JS tidak diizinkan oleh route.',
            'Request akan ditolak dengan 405 Method Not Allowed.',
            'Samakan method JS dengan route',
            ['Ubah method di JS sesuai method route, atau ubah definisi route.'],
            null,
            [$this->ex('Periksa form method spoofing', 'PUT/PATCH/DELETE perlu _method pada request POST.'), $this->ex('Cek route:list', 'php artisan route:list --path=' . ltrim($path, '/'))]
        );
    }

    private function rRender(array $c, array $m): array
    {
        if ($c['type'] === 'Render 404') {
            return $this->sol(
                'Route ada tetapi halaman 404',
                $c['message'],
                'Route terdaftar tetapi controller/middleware mengembalikan 404.',
                'Halaman tidak bisa dibuka; user melihat 404.',
                'Cari sumber 404 di controller/middleware',
                ['Periksa controller: abort(404), findOrFail() tanpa data, atau firstOrFail().', 'Periksa middleware yang memanggil abort(404).', 'Periksa apakah halaman butuh data seed (tabel kosong).'],
                'php artisan db:seed',
                [$this->ex('Cek log', 'Lihat storage/logs/laravel.log saat halaman dibuka.'), $this->ex('Cek route fallback', 'Route::fallback atau route resource dapat menimpa URI ini.')]
            );
        }
        return $this->rServerError($c, $m);
    }

    private function rNode(array $c, array $m): array
    {
        return $this->sol(
            'Node.js tidak terdeteksi oleh PHP',
            'Pengecekan syntax JS dilewati karena perintah node tidak bisa dijalankan dari proses PHP.',
            'Perintah node tidak ada di PATH proses PHP.',
            'Audit JS syntax dilewati; masalah syntax JS tidak terdeteksi.',
            'Pasang Node.js atau tambahkan ke PATH',
            ['Pasang Node.js LTS dari nodejs.org dan verifikasi: node --version.', 'Restart terminal/PHP-FPM/Laragon agar PATH terbaru terbaca oleh proses PHP.', 'Jika memang tidak dibutuhkan: php artisan audit:assets --skip-js-syntax'],
            'node --version',
            [$this->ex('PATH berbeda untuk web server', 'Proses web (Apache/FPM) bisa punya PATH berbeda dari terminal; pastikan Node terlihat oleh user yang menjalankan PHP.'), $this->ex('Playwright butuh Node juga', 'Audit JS (Playwright) memakai Node; sekalian pastikan npx playwright install chromium sudah dijalankan.')]
        );
    }

    private function rtKb(string $text, array $c): ?array
    {
        $loc = $c['file'] ? $c['file'] . ($c['line'] ? ':' . $c['line'] : '') : 'file terkait';
        $kb = [
            ['/Cannot read propert(?:y|ies) of (?:null|undefined) \(reading \'(\w+)\'\)/', 'Elemen/objek bernilai null saat dipakai',
                "Kode mengakses .{1} pada sesuatu yang null/undefined. Umumnya getElementById/querySelector tidak menemukan elemen, atau script berjalan sebelum DOM siap. Lokasi: $loc.",
                'Script akan crash saat mencapai baris ini.',
                'Pastikan elemen ada sebelum dipakai',
                ['Jalankan kode setelah DOM siap (DOMContentLoaded) atau letakkan script sebelum </body>.', 'Pastikan id/selector benar dan elemen ada di halaman ini.', 'Tambahkan pengaman: if (!el) return; atau el?.{1}.'],
                "document.addEventListener('DOMContentLoaded', function () {\n    const el = document.querySelector('#id');\n    if (!el) return;\n    el.addEventListener('click', handler);\n});"],
            ['/(\S+) is not a function/', 'Pemanggilan sesuatu yang bukan fungsi',
                "{1} bukan fungsi. Penyebab umum: plugin jQuery belum dimuat/dimuat sebelum jQuery, jQuery termuat dua kali, atau nama method salah. Lokasi: $loc.",
                'Script akan crash saat memanggil {1}().',
                'Periksa urutan dan duplikasi pemuatan library',
                ['Pastikan jQuery dimuat satu kali, sebelum plugin (DataTable, select2, modal, dsb).', 'Periksa ejaan dan huruf besar-kecil method.', 'Cek di Console: typeof {1} untuk melihat isinya.'],
                null],
            ['/Unexpected token \'<\'/', 'Server mengirim HTML, bukan JavaScript/JSON',
                'Browser menerima halaman HTML (biasanya 404/500/redirect login) padahal mengharapkan JS atau JSON.',
                'Script tidak bisa dijalankan; fitur yang bergantung padanya tidak berfungsi.',
                'Periksa URL script atau endpoint AJAX',
                ['Buka URL script/endpoint langsung di browser dan lihat isinya.', 'Perbaiki path asset (404) atau error server (500).', 'Jika endpoint butuh login, pastikan session aktif dan header Accept: application/json.'],
                null],
            ['/Identifier \'(\w+)\' has already been declared/', 'Variabel dideklarasikan dua kali',
                "Variabel {1} dideklarasikan ulang dengan let/const di scope global; biasanya karena file JS yang sama dimuat dua kali. Lokasi: $loc.",
                'Script akan crash saat parsing; seluruh file JS tidak berjalan.',
                'Muat script sekali atau bungkus dalam IIFE',
                ['Cari tag <script> ganda yang menunjuk file yang sama (layout + halaman).', 'Atau bungkus kode dalam (function () { ... })(); agar tidak mengotori scope global.'],
                "(function () {\n    'use strict';\n    // kode Anda\n})();"],
            ['/Unexpected end of JSON input|is not valid JSON|JSON\.parse/i', 'Respons bukan JSON valid',
                'Endpoint mengembalikan respons kosong atau HTML sehingga JSON.parse/response.json() gagal.',
                'Parsing JSON gagal; data tidak bisa diproses.',
                'Pastikan endpoint mengembalikan JSON',
                ['Cek tab Network: status dan isi respons.', 'Di controller gunakan return response()->json([...]).', 'Tangani error: if (!response.ok) throw new Error(response.status).'],
                null],
            ['/Failed to execute \'(\w+)\' on \'Document\': \'([^\']*)\' is not a valid selector/', 'Selector CSS tidak valid',
                'Selector {2} tidak valid untuk {1}.',
                'Pemilihan elemen gagal; logika yang bergantung padanya tidak berjalan.',
                'Perbaiki selector',
                ['Id/kelas yang diawali angka atau mengandung titik/titik dua perlu di-escape: CSS.escape(id).', 'Periksa tanda kutip dan kurung pada selector.'],
                null],
            ['/Maximum call stack size exceeded/', 'Rekursi tanpa henti',
                "Fungsi memanggil dirinya sendiri tanpa kondisi berhenti, atau event memicu dirinya sendiri. Lokasi: $loc.",
                'Script crash dengan stack overflow; halaman bisa freeze.',
                'Tambahkan kondisi berhenti',
                ['Periksa handler event yang memicu event yang sama (mis. trigger("change") di dalam handler change).', 'Tambahkan guard/flag agar tidak berulang.'],
                null],
            ['/Cannot set propert(?:y|ies) of (?:null|undefined)/', 'Mengisi properti pada elemen yang tidak ada',
                "Elemen yang akan diisi nilainya tidak ditemukan. Lokasi: $loc.",
                'Script crash saat mencoba mengisi properti elemen null.',
                'Pastikan elemen ada sebelum diisi',
                ['Periksa id/selector dan waktu eksekusi script (DOM siap).', 'Tambahkan pengecekan if (el) { ... }.'],
                null],
            ['/Uncaught \(in promise\)|Failed to fetch|NetworkError/i', 'Promise/fetch gagal tidak ditangani',
                'Request fetch gagal (jaringan, CORS, atau server error) dan tidak ada catch().',
                'Error tidak tertangani; user tidak mendapat feedback.',
                'Tangani error pada promise',
                ['Tambahkan .catch(...) atau try/catch pada async function.', 'Periksa tab Network untuk status dan pesan asli.'],
                "try {\n    const res = await fetch(url);\n    if (!res.ok) throw new Error('HTTP ' + res.status);\n    const data = await res.json();\n} catch (e) {\n    console.error(e);\n}"],
            ['/blocked by CORS policy/i', 'Diblokir kebijakan CORS',
                'Browser memblokir request lintas origin karena server tujuan tidak mengizinkan.',
                'Request diblokir browser; data tidak sampai.',
                'Izinkan origin di server atau gunakan origin yang sama',
                ['Pakai URL satu domain dengan halaman (relatif /api/...).', 'Jika API Laravel: atur config/cors.php (allowed_origins) lalu php artisan config:clear.', 'Pastikan AUDIT_BASE_URL sama dengan APP_URL (localhost vs 127.0.0.1 dianggap berbeda).'],
                null],
            ['/Mixed Content/i', 'Konten campuran HTTP di halaman HTTPS',
                'Halaman HTTPS memuat resource lewat http:// sehingga diblokir.',
                'Resource diblokir browser; halaman kehilangan asset.',
                'Gunakan HTTPS atau URL relatif',
                ['Ganti http:// menjadi https:// atau // pada resource.', 'Di Laravel pakai asset()/url() dan setel APP_URL ke https; di production panggil URL::forceScheme("https").'],
                null],
            ['/Refused to (?:execute|apply).*MIME type|Content Security Policy/i', 'Diblokir MIME type/CSP',
                'Browser menolak file karena tipe konten salah (sering file 404 berisi HTML) atau melanggar Content-Security-Policy.',
                'File diblokir browser; asset tidak termuat.',
                'Perbaiki path file atau kebijakan CSP',
                ['Buka URL file langsung; bila berisi halaman error HTML, perbaiki path-nya.', 'Jika CSP aktif, tambahkan sumber yang sah di header/ meta CSP.'],
                null],
            ['/non-unique id|duplicate id/i', 'ID elemen duplikat',
                'Ada lebih dari satu elemen dengan id yang sama; getElementById dan label for akan berperilaku salah.',
                'getElementById hanya mengambil elemen pertama; elemen lain tidak terakses.',
                'Buat id unik',
                ['Cari id ganda pada hasil render (khusus elemen dalam @foreach tambahkan indeks/ID record).'],
                "<input id=\"nama-{{ \$row->id }}\">"],
            ['/favicon/i', 'Favicon tidak ditemukan',
                'Browser meminta /favicon.ico dan menerima 404.',
                'Hanya warning di console; tidak memengaruhi fungsi.',
                'Sediakan favicon',
                ['Letakkan favicon.ico di public/ atau tambahkan <link rel="icon">.'],
                "<link rel=\"icon\" href=\"{{ asset('favicon.ico') }}\">"],
        ];

        foreach ($kb as [$re, $title, $detail, $impact, $fix, $steps, $code]) {
            if (preg_match($re, $text, $mm)) {
                $rep = function (string $s) use ($mm) {
                    return preg_replace_callback('/\{(\d)\}/', fn ($x) => $mm[(int) $x[1]] ?? '', $s);
                };
                return $this->sol(
                    $title,
                    $rep($detail),
                    $rep($impact),
                    'Error ini muncul di console browser dan bisa mengganggu fungsi halaman.',
                    $fix,
                    array_map($rep, $steps),
                    $code,
                    [
                        $this->ex('Reproduksi di browser', 'Buka halaman terkait, tekan F12 → Console, muat ulang, lalu klik link lokasi error untuk melihat baris tepatnya.'),
                        $this->ex('Verifikasi', 'Setelah diperbaiki, klik "Sinkron tab ini" agar Playwright membuka ulang halaman dan memastikan error hilang.'),
                    ]
                );
            }
        }
        return null;
    }

    private function rRtUndefined(array $c, array $m): array
    {
        $name = $m[1] ?? '';
        $libs = [
            '$' => 'jQuery', 'jQuery' => 'jQuery', 'axios' => 'axios', 'Swal' => 'SweetAlert', 'bootstrap' => 'Bootstrap JS',
            'Chart' => 'Chart.js', 'moment' => 'moment', 'Vue' => 'Vue', 'Alpine' => 'Alpine.js',
        ];

        if (isset($libs[$name])) {
            $lib = $libs[$name];
            return $this->sol(
                "$lib belum termuat saat dipakai",
                "$name tidak terdefinisi: library $lib tidak dimuat, gagal dimuat (404/CDN), atau dimuat setelah script yang memakainya. Lokasi: " . ($c['file'] ?: 'lihat Console') . '.',
                'Library belum dimuat di halaman, atau dimuat setelah script yang memakainya.',
                'Semua kode yang memakai library ini akan error "is not defined".',
                "Pastikan $lib dimuat lebih dulu",
                ["Muat $lib sebelum script yang memakainya (urutan tag script penting).", 'Buka tab Network dan pastikan file library tidak 404/diblokir.', 'Jika memakai bundler, impor library di entry JS, bukan mengandalkan global.'],
                "<script src=\"{{ asset('vendor/jquery/jquery.min.js') }}\"></script>\n<script src=\"{{ asset('js/halaman.js') }}\"></script>",
                [
                    $this->ex('Cek CDN/offline', 'Jika memakai CDN dan jaringan terbatas, host library secara lokal di public/vendor.'),
                    $this->ex('Cek atribut defer/async', 'Script dengan async/defer dieksekusi tidak berurutan; script yang bergantung harus defer juga atau dimuat setelahnya.'),
                ]
            );
        }

        $sim = $this->closest($name, $this->jsFunctions());
        return $this->sol(
            "'$name' tidak terdefinisi",
            "$name dipanggil tetapi tidak ada di scope saat ini." . ($sim ? ' Nama mirip di project: ' . implode(', ', $sim) . '.' : ''),
            'Variabel/fungsi dipanggil tetapi tidak ada di scope saat ini.',
            'Script akan error "is not defined" saat mencapai baris ini.',
            'Definisikan atau muat file yang berisi ' . $name,
            array_filter([
                $sim ? 'Periksa typo; nama mirip: ' . implode(', ', $sim) . '.' : null,
                'Pastikan file JS yang mendefinisikannya dimuat di halaman ini dan sebelum pemakaian.',
                'Jika didefinisikan dalam module/IIFE, ekspos dengan window.' . $name . ' = ' . $name . ';',
            ]),
            "window.$name = $name;",
            [
                $this->ex('Periksa urutan eksekusi', 'Pemanggilan sebelum definisi (kecuali function declaration) menghasilkan error ini.'),
                $this->ex('Periksa error sebelumnya', 'Error lain yang menghentikan file JS lebih awal dapat membuat definisi tidak pernah dijalankan; atasi error pertama dulu.'),
            ]
        );
    }

    private function rRtException(array $c, array $m): array
    {
        return $this->rtKb($c['message'], $c) ?? $this->sol(
            'Exception JavaScript di halaman',
            $c['message'] . ($c['file'] ? " (lokasi: {$c['file']}:{$c['line']})" : ''),
            'Ada kesalahan saat mengeksekusi JavaScript di halaman.',
            'Script berhenti di baris error; fitur setelahnya tidak berjalan.',
            'Telusuri baris penyebab lalu tangani nilainya',
            ['Buka lokasi pada hasil audit atau klik link di Console.', 'Periksa nilai variabel di baris itu (console.log atau breakpoint).', 'Tambahkan pengecekan null/undefined dan try/catch pada bagian yang bergantung pada data eksternal.'],
            null,
            [
                $this->ex('Pakai breakpoint', 'DevTools → Sources → klik nomor baris untuk menjeda eksekusi dan melihat nilai variabel.'),
                $this->ex('Aktifkan source map', 'Jika memakai bundler, aktifkan sourcemap agar baris error menunjuk ke kode asli.'),
            ]
        );
    }

    private function rRtConsole(array $c, array $m): array
    {
        if (preg_match('/status of (\d{3})/', $c['message'], $mm)) {
            $cc = $c;
            $cc['type'] = 'HTTP ' . $mm[1];
            $u = preg_replace('/:\d+$/', '', $c['url']);
            $cc['message'] = 'GET ' . preg_replace('#^https?://[^/]+#', '', $u);
            return $this->rHttpStatus($cc, [1 => $mm[1]]);
        }
        return $this->rtKb($c['message'], $c) ?? $this->sol(
            'Pesan error di console browser',
            $c['message'],
            'Ada pesan error di console browser.',
            'Bisa mengindikasikan masalah yang memengaruhi fungsi halaman.',
            'Baca pesan lengkap di DevTools dan telusuri sumbernya',
            ['Buka halaman, tekan F12 → Console, lalu klik link sumber pada pesan.', 'Cari kata kunci pesan di dokumentasi library yang disebut.', 'Jika berasal dari library pihak ketiga dan tidak memengaruhi fungsi, catat sebagai peringatan.'],
            null,
            [$this->ex('Filter pesan', 'Di Console gunakan filter Errors untuk fokus pada yang kritis.'), $this->ex('Cek Network', 'Banyak error console berasal dari request yang gagal; lihat tab Network.')]
        );
    }

    private function rRtNetFail(array $c, array $m): array
    {
        $msg = $c['message'];
        $map = [
            '/ERR_CONNECTION_REFUSED/' => ['Server tujuan menolak koneksi', ['Pastikan server berjalan (php artisan serve / Laragon aktif).', 'Periksa host dan port pada AUDIT_BASE_URL dan APP_URL.']],
            '/ERR_NAME_NOT_RESOLVED/' => ['Nama domain tidak bisa di-resolve', ['Periksa ejaan domain/CDN atau koneksi internet.', 'Jika di jaringan terbatas, host file tersebut secara lokal.']],
            '/ERR_ABORTED/' => ['Request dibatalkan', ['Biasanya karena navigasi pindah halaman sebelum request selesai atau diblokir; abaikan jika halaman berfungsi.', 'Jika berulang pada satu file, periksa apakah file itu redirect/ditolak server.']],
            '/ERR_BLOCKED_BY_CLIENT/' => ['Diblokir ekstensi/klien', ['Nonaktifkan ad-blocker pada browser, atau ubah nama file/URL yang mengandung kata seperti ads/track.']],
            '/ERR_CERT|ERR_SSL/' => ['Masalah sertifikat SSL', ['Gunakan http untuk lokal atau pasang sertifikat yang valid.', 'Pastikan APP_URL sesuai skema yang dipakai.']],
            '/ERR_TIMED_OUT|ERR_CONNECTION_TIMED_OUT/' => ['Koneksi time-out', ['Periksa firewall dan jaringan, atau server yang lambat merespons.']],
            '/ERR_EMPTY_RESPONSE|ERR_CONNECTION_RESET/' => ['Server menutup koneksi tanpa respons', ['Periksa log PHP/web server: crash proses atau batas memori/waktu.', 'Lihat storage/logs/laravel.log.']],
        ];

        $why = 'Request tidak dapat diselesaikan.';
        $steps = ['Periksa tab Network pada URL tersebut.', 'Pastikan URL dapat dibuka langsung di browser.'];
        foreach ($map as $re => [$w, $s]) {
            if (preg_match($re, $msg)) {
                $why = $w;
                $steps = $s;
                break;
            }
        }

        return $this->sol(
            'Request gagal: ' . $why,
            ($c['url'] ? $c['url'] . ' → ' : '') . $msg,
            'Request HTTP gagal karena alasan jaringan/server.',
            'Data tidak sampai; fitur yang bergantung pada request ini tidak berfungsi.',
            'Perbaiki penyebab kegagalan request',
            $steps,
            null,
            [
                $this->ex('Cek URL dasar', 'Samakan AUDIT_BASE_URL di .env dengan alamat aplikasi yang benar-benar berjalan, lalu php artisan config:clear.'),
                $this->ex('Cek resource pihak ketiga', 'Jika URL berasal dari domain luar, anggap itu sebagai ketergantungan eksternal dan pertimbangkan hosting lokal.'),
            ]
        );
    }

    private function rHttpStatus(array $c, array $m): array
    {
        preg_match('/(\d{3})/', $c['type'], $mm);
        $code = (int) ($mm[1] ?? 0);
        $path = preg_match('#\s(/\S*)#', $c['message'], $pm) ? preg_replace('/[?#].*$/', '', $pm[1]) : '';

        if ($code >= 500) {
            return $this->rServerError($c, $m);
        }
        if ($code === 404) {
            if ($path !== '' && preg_match('/\.\w{2,5}$/', $path)) {
                $cc = $c;
                $cc['message'] = 'public' . $path . ' tidak ditemukan';
                return $this->rAssetMissing($cc, []);
            }
            $sim = $this->closest($path, $this->routeUris(), 3);
            return $this->sol(
                'URL tidak punya route (404)',
                "$path mengembalikan 404." . ($sim ? ' Route mirip: ' . implode(', ', $sim) . '.' : ''),
                'URL tidak terdaftar di routes/*.php, atau salah ketik.',
                'Halaman/endpoint tidak bisa diakses; user melihat 404.',
                $sim ? "Gunakan URL yang benar: {$sim[0]}" : 'Daftarkan route atau perbaiki URL',
                array_filter([$sim ? 'Periksa salah ketik pada URL.' : null, 'Cek: php artisan route:list --path=' . ltrim($path, '/'), 'Jika data tidak ada, controller bisa memanggil findOrFail()/abort(404).']),
                'php artisan route:list --path=' . ltrim($path, '/'),
                [$this->ex('Periksa APP_URL & subfolder', 'Aplikasi di sub-folder membuat URL berawalan / salah; gunakan route()/url().'), $this->ex('Bersihkan cache route', 'php artisan route:clear')]
            );
        }

        $table = [
            401 => ['Belum terautentikasi (401)', ['Pastikan audit login berhasil (AUDIT_EMAIL, AUDIT_PASSWORD, selector).', 'Untuk API pastikan token/guard benar.']],
            403 => ['Akses ditolak (403)', ['Periksa policy/gate/middleware role pada route.', 'Pastikan user audit memiliki izin untuk halaman ini.']],
            405 => ['Method tidak diizinkan (405)', ['Pastikan method request sama dengan yang didaftarkan di route (php artisan route:list).', 'Gunakan @method(\'PUT\') dengan form POST untuk PUT/PATCH/DELETE.']],
            419 => ['Token CSRF kedaluwarsa (419)', ['Sertakan @csrf pada form atau header X-CSRF-TOKEN pada AJAX.', 'Periksa SESSION_DOMAIN/SESSION_SECURE_COOKIE bila terjadi hanya di server tertentu.']],
            422 => ['Validasi gagal (422)', ['Periksa aturan validasi dan field yang dikirim form.', 'Tampilkan @error pada form agar pesan validasi terlihat.']],
            429 => ['Terlalu banyak request (429)', ['Periksa middleware throttle pada route.', 'Kurangi frekuensi request atau naikkan batas.']],
        ];
        [$title, $steps] = $table[$code] ?? ["HTTP $code", ['Periksa respons pada tab Network dan log server.']];

        return $this->sol(
            $title,
            ($path !== '' ? $path . ': ' : '') . $c['message'],
            'Server mengembalikan status HTTP ' . $code . '.',
            'Request tidak berhasil; user mendapat pesan error.',
            'Atasi penyebab status ' . $code,
            $steps,
            null,
            [
                $this->ex('Cek log aplikasi', 'Lihat storage/logs/laravel.log untuk detail saat request dibuat.'),
                $this->ex('Cek middleware route', 'php artisan route:list -v menampilkan middleware yang menahan request.'),
            ]
        );
    }

    private function rRtPage(array $c, array $m): array
    {
        $msg = $c['message'];
        if (preg_match('/Timeout|timeout/', $msg)) {
            return $this->sol(
                'Halaman terlalu lama dimuat (timeout)',
                $msg,
                'Halaman tidak selesai dimuat dalam batas waktu audit.',
                'Audit tidak bisa memeriksa halaman ini; bisa jadi halaman memang lambat.',
                'Cari request yang menggantung atau query lambat',
                ['Buka halaman manual dan lihat tab Network: request mana yang tidak selesai.', 'Optimalkan query berat (pagination, eager loading with()).', 'Jika memang long-polling/websocket, kecualikan dari audit.'],
                null,
                [$this->ex('Naikkan timeout audit', 'Ubah NAVIGATION_TIMEOUT_MS di scripts/audit-js.mjs bila halaman memang berat.'), $this->ex('Periksa server dev', 'php artisan serve bersifat single-thread; request yang bertumpuk bisa saling menunggu.')]
            );
        }
        if (preg_match('/ERR_CONNECTION_REFUSED|ECONNREFUSED/', $msg)) {
            return $this->sol(
                'Server aplikasi tidak berjalan',
                $msg,
                'Server aplikasi tidak merespons di alamat yang diaudit.',
                'Audit tidak bisa mengakses halaman sama sekali.',
                'Jalankan server dan samakan AUDIT_BASE_URL',
                ['Jalankan php artisan serve (atau aktifkan Laragon/XAMPP).', 'Samakan AUDIT_BASE_URL di .env dengan alamat server tersebut.', 'Jalankan php artisan config:clear.'],
                'php artisan serve --host=127.0.0.1 --port=8000',
                [$this->ex('Periksa port & firewall', 'Pastikan port tidak dipakai aplikasi lain atau diblokir firewall.'), $this->ex('Periksa host', 'localhost dan 127.0.0.1 bisa berbeda untuk cookie/session; gunakan satu saja.')]
            );
        }
        return $this->sol(
            'Halaman gagal dimuat saat diaudit',
            $msg,
            'Halaman tidak bisa dimuat oleh audit.',
            'Audit tidak bisa memeriksa halaman ini.',
            'Buka halaman manual lalu telusuri error server',
            ['Buka URL halaman di browser dan lihat error yang tampil.', 'Periksa storage/logs/laravel.log.', 'Jika status 5xx, perbaiki error server terlebih dahulu.'],
            'tail -n 80 storage/logs/laravel.log',
            [$this->ex('Cek login audit', 'Halaman mungkin butuh login; pastikan kredensial AUDIT_* valid.'), $this->ex('Cek route di route:list', 'Pastikan URI memang terdaftar untuk method GET.')]
        );
    }

    private function rRtLogin(array $c, array $m): array
    {
        return $this->sol(
            'Login otomatis untuk audit gagal',
            $c['message'],
            'Audit tidak bisa login otomatis ke aplikasi.',
            'Audit tidak bisa mengakses halaman yang butuh login.',
            'Periksa kredensial dan selector login',
            [
                'Periksa AUDIT_LOGIN_URL, AUDIT_EMAIL, AUDIT_PASSWORD di .env.',
                'Samakan AUDIT_EMAIL_SELECTOR/AUDIT_PASSWORD_SELECTOR dengan atribut name pada form login (default audit-js.mjs: input[name=username]).',
                'Jika login memakai captcha/2FA, buat user audit khusus tanpa itu pada environment lokal/staging.',
                'Jalankan php artisan config:clear agar .env terbaca.',
            ],
            "AUDIT_LOGIN_URL=/login\nAUDIT_EMAIL=user@contoh.test\nAUDIT_PASSWORD=rahasia\nAUDIT_EMAIL_SELECTOR=input[name=username]\nAUDIT_PASSWORD_SELECTOR=input[name=password]",
            [
                $this->ex('Cek URL dasar', 'AUDIT_BASE_URL harus sama dengan alamat aplikasi yang berjalan.'),
                $this->ex('Jangan commit kredensial', 'Simpan AUDIT_* hanya di .env lokal/staging dan pastikan .env tidak masuk git.'),
            ]
        );
    }

    private function rRouteNoName(array $c, array $m): array
    {
        $guess = $c['feature'] ? $c['feature'] . '.' . ($c['function'] ?: 'aksi') : 'fitur.aksi';
        return $this->sol(
            'Route belum diberi nama',
            'Tanpa nama, route tidak bisa dipanggil lewat route(), redirect()->route(), atau diaudit konsisten di Blade.',
            'Route tidak memiliki nama yang bisa dipanggil.',
            'Tidak bisa memakai route() untuk route ini; harus tulis URL manual.',
            'Tambahkan ->name() pada route',
            ["Beri nama konsisten, misalnya '$guess'.", 'Gunakan nama itu di Blade: route(\'' . $guess . '\').'],
            "Route::get('/uri', [Controller::class, 'metode'])->name('$guess');",
            [
                $this->ex('Pakai Route::resource', 'Route::resource() otomatis memberi nama index/create/store/show/edit/update/destroy.'),
                $this->ex('Cache route', 'Route bernama mempermudah php artisan route:cache dan pencarian di route:list.'),
            ]
        );
    }

    private function rExport(array $c, array $m): array
    {
        $fq = $c['message'];
        return $this->sol(
            'Class export belum ada',
            "Controller memakai class export $fq tetapi class itu tidak ditemukan.",
            'Class export belum dibuat, atau namespace-nya salah.',
            'Fitur export akan error "Class not found" saat dipanggil.',
            'Buat class export atau perbaiki namespace-nya',
            ['Buat class dengan perintah di bawah (butuh paket maatwebsite/excel).', 'Pastikan import use App\\Exports\\NamaExport; ada di controller.'],
            $this->makeCmd($fq),
            [$this->ex('Pasang paket', 'composer require maatwebsite/excel jika belum terpasang.'), $this->ex('Segarkan autoload', 'composer dump-autoload setelah menambah class.')]
        );
    }

    private function rFormRoute(array $c, array $m): array
    {
        $name = $c['route'] ?: $c['feature'] . '.' . ($c['function'] === 'edit_simpan' ? 'update' : 'store');
        return $this->sol(
            'Form tidak terlihat mengarah ke route ini',
            'Audit tidak menemukan pemanggilan route ini di Blade (termasuk @include). Bisa jadi form mengirim lewat JavaScript, atau action diketik manual.',
            'Form tidak memakai route() untuk action-nya.',
            'Jika route berubah, form tidak ikut berubah; bisa 404.',
            'Arahkan action form dengan route()',
            ['Gunakan route() pada atribut action agar URL otomatis benar.', 'Jika memang dikirim lewat AJAX, abaikan peringatan ini.'],
            "<form action=\"{{ route('$name') }}\" method=\"POST\">\n@csrf\n<!-- field -->\n</form>",
            [$this->ex('Untuk update, tambahkan @method', "Form edit: tambahkan @method('PUT') di dalam form."), $this->ex('Periksa nama route', 'Pastikan nama di route() sama dengan di php artisan route:list.')]
        );
    }

    private function rFormCsrf(array $c, array $m): array
    {
        return $this->sol(
            'Form tidak memiliki token CSRF',
            'Submit POST akan ditolak dengan 419 Page Expired.',
            'Form POST tidak menyertakan token CSRF.',
            'Submit form akan ditolak dengan 419 Page Expired.',
            'Tambahkan @csrf di dalam setiap form POST',
            ['Letakkan @csrf tepat di bawah tag <form>.', 'Untuk AJAX gunakan header X-CSRF-TOKEN dari meta tag.'],
            "<form method=\"POST\" action=\"...\">\n@csrf\n...\n</form>",
            [
                $this->ex('Jangan mengecualikan CSRF', 'Menambah URL ke $except di VerifyCsrfToken hanya untuk webhook eksternal.'),
                $this->ex('Periksa session', 'Jika @csrf sudah ada namun 419 tetap muncul, periksa SESSION_DRIVER, cookie domain, dan permission storage/framework/sessions.'),
            ]
        );
    }

    function rTableMissingOld(array $c, array $m): array
    {
        $table = $this->q($c['message'])[0] ?? '';
        $tables = $this->tables();
        $sim = $this->closest($table, $tables);
        $one = $table !== '' && str_ends_with($table, 's') ? substr($table, 0, -1) : $table . 's';
        $alt = in_array($one, $tables, true) ? [$one] : [];
        $sim = array_values(array_unique(array_merge($alt, $sim)));

        return $this->sol(
            'Tabel database tidak ada',
            "Tabel '$table' diminta tetapi tidak ada di database aktif." . ($sim ? ' Tabel yang mirip: ' . implode(', ', $sim) . '.' : ''),
            'Tabel belum dibuat di database, atau nama tabel salah.',
            'Query ke tabel ini akan error "Table not found" (500).',
            $sim ? "Arahkan model ke tabel '{$sim[0]}' atau jalankan migration" : 'Jalankan atau buat migration',
            array_filter([
                'Cek status migration: php artisan migrate:status',
                'Jika ada migration yang pending: php artisan migrate',
                $sim ? "Jika tabel sebenarnya '{$sim[0]}', set properti \$table pada model." : "Jika belum ada migration: php artisan make:migration create_{$table}_table",
                'Pastikan .env menunjuk database yang benar (DB_DATABASE).',
            ]),
            $sim ? "protected \$table = '{$sim[0]}';" : "php artisan make:migration create_{$table}_table\nphp artisan migrate",
            [
                $this->ex('Periksa koneksi model', 'Model dengan $connection khusus memakai database lain; pastikan tabelnya ada di koneksi tersebut.'),
                $this->ex('Setelah migrasi', 'Jalankan php artisan config:clear bila baru mengganti .env, lalu Cek ulang fitur ini.'),
            ]
        );
    }

    function rFillableColsOld(array $c, array $m): array
    {
        $table = $this->q($c['message'])[0] ?? '';
        $cols = preg_match('/kolom:\s*(.+)$/', $c['message'], $mm) ? array_map('trim', explode(',', $mm[1])) : [];
        $have = $this->columns($table);
        $hints = [];
        foreach ($cols as $col) {
            $s = $this->closest($col, $have, 1);
            $hints[] = $s ? "$col → mungkin '{$s[0]}'" : "$col → tidak ada yang mirip";
        }
        $mig = '';
        foreach ($cols as $col) {
            $mig .= "\$table->string('$col')->nullable();\n";
        }

        return $this->sol(
            'Kolom di $fillable tidak ada di tabel',
            "Tabel '$table' tidak punya kolom: " . implode(', ', $cols) . '. Create/update yang memakai kolom itu akan error SQL (Unknown column).',
            'Kolom di $fillable tidak ada di struktur tabel.',
            'INSERT/UPDATE akan error "Unknown column" (500).',
            'Hapus/perbaiki dari $fillable, atau tambahkan kolom lewat migration',
            array_filter([
                $hints ? 'Petunjuk: ' . implode('; ', $hints) . '.' : null,
                'Jika salah ketik: perbaiki nama pada $fillable.',
                "Jika kolom memang dibutuhkan: php artisan make:migration add_kolom_to_{$table}_table --table=$table lalu php artisan migrate.",
            ]),
            rtrim($mig),
            [
                $this->ex('Kolom yang ada saat ini', $have ? implode(', ', $have) : 'Tidak dapat membaca kolom tabel.'),
                $this->ex('Samakan dengan input form', 'Nama field form (name="...") harus sama dengan nama kolom di $fillable.'),
            ]
        );
    }

    private function rMassAssign(array $c, array $m): array
    {
        $name = preg_match('/^(\w+) tidak punya/', $c['message'], $mm) ? $mm[1] : '';
        $cols = [];
        $fq = $this->classIndex()[strtolower($name)][0] ?? null;
        if ($fq && class_exists($fq)) {
            try {
                $mod = new $fq();
                $cols = array_values(array_diff($this->columns($mod->getTable()), [$mod->getKeyName(), 'created_at', 'updated_at', 'deleted_at']));
            } catch (Throwable $e) {
            }
        }

        $code = $cols
            ? "protected \$fillable = [\n    '" . implode("',\n    '", $cols) . "',\n];"
            : "protected \$fillable = [\n    'kolom_1',\n    'kolom_2',\n];";

        return $this->sol(
            'Mass assignment diblokir',
            "Model $name tidak punya \$fillable (atau \$guarded = ['*']), padahal controller memakai create()/fill(), sehingga kolom tidak akan tersimpan / muncul MassAssignmentException.",
            'Model tidak mengizinkan mass assignment karena $fillable kosong.',
            'create()/fill() tidak akan menyimpan data; bisa jadi data kosong di database.',
            'Tambahkan $fillable pada model',
            [$cols ? 'Daftar di bawah dibuat dari kolom tabel yang sebenarnya (tanpa id & timestamp); hapus yang tidak boleh diisi dari form.' : 'Isi dengan kolom yang boleh diisi dari form.', 'Jangan mengisi kolom sensitif seperti role atau is_admin.'],
            $code,
            [
                $this->ex('Alternatif: $guarded', 'protected $guarded = []; lebih cepat tetapi mengizinkan semua kolom; pakai hanya jika input selalu divalidasi.'),
                $this->ex('Validasi sebelum simpan', 'Gunakan $request->validate([...]) dan kirim hasil validasi saja ke create(): Model::create($data);'),
            ]
        );
    }

    private function rRequiredCols(array $c, array $m): array
    {
        $cols = preg_match('/default:\s*([^.]+)\./', $c['message'], $mm) ? array_map('trim', explode(',', $mm[1])) : [];
        return $this->sol(
            'Kolom wajib terisi tidak ada di $fillable',
            'Kolom ' . implode(', ', $cols) . ' NOT NULL tanpa default. Jika tidak diisi, INSERT akan gagal (Field doesn\'t have a default value).',
            'Kolom wajib tidak ada di $fillable, jadi tidak bisa diisi lewat mass assignment.',
            'INSERT akan gagal "Field doesn\'t have a default value" (500).',
            'Isi kolom saat menyimpan, atau beri default',
            ['Jika diisi dari form: tambahkan ke $fillable dan validasi.', 'Jika diisi sistem (mis. user_id): set manual di controller, contoh di kode.', 'Jika boleh kosong: ubah migration menjadi ->nullable() atau ->default(...).'],
            "\$data = \$request->validate([...]);\n\$data['" . ($cols[0] ?? 'kolom') . "'] = auth()->id();\nModel::create(\$data);",
            [
                $this->ex('Peringatan boleh diabaikan', 'Jika kolom sudah diisi manual (mis. lewat $model->kolom = ...; $model->save()), warning ini aman.'),
                $this->ex('Gunakan model events', 'Untuk nilai otomatis (uuid/slug), isi di boot() lewat creating event.'),
            ]
        );
    }

    private function rNoModel(array $c, array $m): array
    {
        return $this->sol(
            'Akses database tidak terdeteksi di controller',
            'Method tidak memanggil Model atau DB::table langsung, jadi audit tidak bisa memastikan data benar-benar tersimpan. Biasanya karena logika ada di Service/Repository.',
            'Audit tidak menemukan pemanggilan Model/DB::table di method ini.',
            'Audit tidak bisa memverifikasi data tersimpan; bisa jadi fitur belum selesai.',
            'Verifikasi manual atau tambahkan Feature test',
            ['Jika memang lewat Service/Repository, periksa class tersebut memanggil create/update/delete.', 'Tulis Feature test yang memanggil route ini dan assertDatabaseHas().'],
            "\$this->post(route('{nama.route}'), \$data)->assertRedirect();\n\$this->assertDatabaseHas('tabel', \$data);",
            [$this->ex('Bisa diabaikan', 'Jika alur sudah diuji lewat test lain, peringatan ini hanya informasi.'), $this->ex('Type-hint Model', 'Menulis Model::create() / route model binding langsung di controller membuat alur dapat dilacak audit.')]
        );
    }

    private function rNoWrite(array $c, array $m): array
    {
        $fn = $c['function'];
        $want = $fn === 'tambah_simpan' ? 'store' : ($fn === 'edit_simpan' ? 'update' : 'destroy');
        return $this->sol(
            'Operasi simpan/ubah/hapus tidak terlihat',
            'Method ini tidak memanggil operasi tulis (create/update/delete) secara langsung. Mungkin tertinggal TODO atau dikerjakan di Service.',
            'Method tidak memanggil create/update/delete secara langsung.',
            'Data mungkin tidak tersimpan; fitur belum selesai.',
            'Pastikan data benar-benar ditulis ke database',
            ['Periksa apakah method hanya redirect tanpa menyimpan.', 'Jika lewat Service, pastikan Service memanggil operasi tulis.'],
            $this->skeleton($want),
            [$this->ex('Gunakan transaksi', 'Untuk simpan multi-tabel, bungkus dengan DB::transaction(function () { ... });'), $this->ex('Tulis test', 'assertDatabaseHas / assertDatabaseMissing memastikan operasi benar-benar berjalan.')]
        );
    }

    private function rModelInst(array $c, array $m): array
    {
        return $this->sol(
            'Model tidak bisa diinstansiasi',
            $c['message'] . ' (abstract class, trait, atau constructor butuh argumen).',
            'Model tidak bisa diinstansiasi langsung.',
            'Audit tidak bisa memeriksa tabel model ini.',
            'Gunakan model konkret',
            ['Pastikan class yang dipakai bukan abstract/trait/interface.', 'Jika constructor butuh argumen, audit tidak bisa memeriksa tabelnya; abaikan atau sediakan default.'],
            null,
            [$this->ex('Periksa nama class', 'Pastikan yang dipanggil Model::, bukan class dasar.'), $this->ex('Pisahkan logika', 'Hindari logika berat di constructor model.')]
        );
    }

    private function rServerError(array $c, array $m): array
    {
        $t = $c['message'];
        if (preg_match('/Base table or view not found.*?\'([^\']+)\'|Table \'[^\']*\.([^\']+)\' doesn\'t exist/i', $t, $mm)) {
            $name = $mm[1] ?: ($mm[2] ?? '');
            $name = substr(strrchr('.' . $name, '.'), 1);   
            $cc = $c;
            $cc['message'] = "Tabel '$name' tidak ada";
            return $this->rTableMissing($cc, []);
        }
        if (preg_match('/Class "([^"]+)" not found/', $t, $mm)) {
            $cc = $c;
            $cc['message'] = "'{$mm[1]}' belum di-import atau tidak ada";
            return $this->rClassMissing($cc, []);
        }
        if (preg_match('/View \[([^\]]+)\] not found/', $t, $mm)) {
            $cc = $c;
            $cc['message'] = "View '{$mm[1]}' tidak ditemukan";
            return $this->rViewMissing($cc, []);
        }
        if (preg_match('/Route \[([^\]]+)\] not defined/', $t, $mm)) {
            $cc = $c;
            $cc['message'] = "Route bernama '{$mm[1]}' tidak terdaftar";
            return $this->rRouteName($cc, []);
        }
        if (preg_match('/Target class \[([^\]]+)\] does not exist/', $t, $mm)) {
            $cc = $c;
            $cc['message'] = "memanggil controller '{$mm[1]}' yang tidak ada";
            return $this->rRouteController($cc, []);
        }

        $loc = $c['file'] ? $c['file'] . ($c['line'] ? ':' . $c['line'] : '') : 'lihat stack trace';
        $kb = [
            ['/Unknown database \'([^\']*)\'|\[1049\]/', 'Database belum dibuat', 'Server database terhubung tetapi database {1} tidak ada.', 'Database tidak ada; semua query gagal.', 'Buat database dan samakan .env', ['Buat database (mis. lewat phpMyAdmin/HeidiSQL).', 'Samakan DB_DATABASE di .env, lalu php artisan config:clear.', 'Jalankan php artisan migrate.'], "DB_DATABASE=nama_database\nphp artisan config:clear\nphp artisan migrate"],
            ['/\[2002\]|Connection refused|No connection could be made|actively refused/i', 'Server database tidak bisa dihubungi', 'Aplikasi tidak bisa menghubungi MySQL/PostgreSQL.', 'Semua query database gagal.', 'Nyalakan server database dan cek host/port', ['Pastikan service database berjalan (Laragon/XAMPP/Docker).', 'Periksa DB_HOST dan DB_PORT; di lokal biasanya 127.0.0.1 dan 3306.', 'php artisan config:clear'], "DB_HOST=127.0.0.1\nDB_PORT=3306"],
            ['/\[1045\]|Access denied for user/i', 'Kredensial database salah', 'Username/password database ditolak.', 'Semua query database gagal.', 'Perbaiki DB_USERNAME dan DB_PASSWORD', ['Periksa .env, lalu php artisan config:clear.', 'Pastikan user punya hak akses ke database tersebut.'], null],
            ['/could not find driver/i', 'Driver PDO belum aktif', 'Ekstensi PDO untuk database ini belum diaktifkan di PHP.', 'Semua query database gagal.', 'Aktifkan ekstensi di php.ini', ['Aktifkan extension=pdo_mysql (atau pdo_pgsql/pdo_sqlite) di php.ini.', 'Restart web server/PHP, cek dengan php -m.'], 'extension=pdo_mysql'],
            ['/Unknown column \'([^\']+)\'/', 'Kolom tidak ada di tabel', 'Query memakai kolom {1} yang tidak ada di tabel.', 'Query gagal "Unknown column" (500).', 'Tambahkan kolom atau perbaiki nama kolom', ['Periksa ejaan kolom di query/model ($fillable, where, orderBy).', 'Jika memang kolom baru: buat migration lalu php artisan migrate.', 'Bandingkan dengan struktur tabel: php artisan db:table nama_tabel'], null],
            ['/Column \'([^\']+)\' cannot be null|Field \'([^\']+)\' doesn\'t have a default value/', 'Kolom wajib tidak terisi', 'Kolom {1}{2} tidak diberi nilai saat INSERT/UPDATE.', 'INSERT/UPDATE gagal "Field doesn\'t have a default value" (500).', 'Pastikan nilai dikirim atau beri default', ['Pastikan input form punya name sesuai kolom dan lolos validasi.', 'Tambahkan kolom ke $fillable.', 'Atau ubah migration: ->nullable() / ->default(...).'], null],
            ['/Duplicate entry \'([^\']*)\' for key \'([^\']+)\'/', 'Data duplikat pada kolom unik', 'Nilai {1} sudah ada pada indeks unik {2}.', 'INSERT gagal "Duplicate entry" (500).', 'Cegah duplikat pada validasi', ['Tambahkan validasi unique: \'kolom\' => \'unique:tabel,kolom\'.', 'Gunakan updateOrCreate bila perilakunya upsert.'], null],
            ['/Data too long for column \'([^\']+)\'/', 'Data lebih panjang dari kolom', 'Kolom {1} tidak muat nilai yang dikirim.', 'INSERT/UPDATE gagal "Data too long" (500).', 'Perpanjang kolom atau batasi input', ['Tambahkan validasi max:N sesuai panjang kolom.', 'Atau ubah tipe kolom (string → text) lewat migration.'], null],
            ['/Incorrect (?:integer|decimal|double|date|datetime) value/i', 'Tipe data tidak cocok', 'Nilai yang dikirim tidak sesuai tipe kolom (mis. string kosong ke kolom angka/tanggal).', 'INSERT/UPDATE gagal "Incorrect value" (500).', 'Normalisasi nilai sebelum disimpan', ['Ubah string kosong menjadi null: $request->merge([\'kolom\' => $request->kolom ?: null]).', 'Pastikan kolom nullable bila boleh kosong.', 'Validasi tipe: numeric, date.'], null],
            ['/Undefined variable \$(\w+)/', 'Variabel tidak dikirim ke view', 'Variabel ${1} dipakai di Blade/PHP tetapi tidak didefinisikan.', 'Halaman error "Undefined variable" (500).', 'Kirim variabel dari controller', ['Tambahkan ke view: return view(\'x\', compact(\'{1}\')).', 'Periksa salah ketik nama variabel.', 'Atau beri nilai bawaan di Blade: ${1} ?? null.'], "return view('{view}', compact('{1}'));"],
            ['/Undefined (?:array key|index|offset) "?\'?([^"\']+)/i', 'Key array tidak ada', 'Key {1} tidak ada pada array/koleksi.', 'Akan warning/error saat mengakses key.', 'Gunakan null-coalescing atau validasi key', ['Gunakan $arr[\'{1}\'] ?? null atau Arr::get($arr, \'{1}\').', 'Periksa sumber data: apakah form/API mengirim key itu?'], null],
            ['/Undefined property: ([\w\\\\]+)::\$(\w+)/', 'Properti tidak ada pada objek', 'Properti {2} tidak ada pada {1}.', 'Akan warning/error saat mengakses properti.', 'Periksa nama kolom/relasi', ['Pastikan kolom ada di tabel atau relasi didefinisikan di model.', 'Bila relasi: with(\'{2}\') dan method relasinya benar.'], null],
            ['/Call to a member function (\w+)\(\) on (null|bool|array|string|int)/', 'Memanggil method pada nilai kosong', 'Memanggil {1}() pada {2}; biasanya hasil find()/first() tidak ditemukan atau relasi kosong.', 'Script crash "Call to member function on null" (500).', 'Gunakan findOrFail atau null-safe', ['Ganti find()/first() dengan findOrFail()/firstOrFail() bila data wajib ada.', 'Atau gunakan null-safe: $obj?->{1}().', 'Periksa apakah user sudah login (auth()->user() bisa null).'], null],
            ['/Attempt to read property "(\w+)" on (\w+)/', 'Membaca properti dari nilai kosong', 'Mengakses ->{1} pada {2}; objek/relasi tidak ada.', 'Akan error "Attempt to read property on null" (500).', 'Gunakan null-safe atau pastikan data ada', ['Gunakan $obj?->{1} pada Blade/PHP 8.', 'Gunakan optional($obj)->{1}.', 'Pastikan relasi di-load dan datanya ada.'], null],
            ['/Too few arguments to function ([\w\\\\:]+)\(\)/', 'Argumen method kurang', 'Pemanggilan {1}() kurang argumen, sering karena parameter route tidak sesuai dengan parameter method.', 'Error "Too few arguments" (500).', 'Samakan parameter route dan method', ['Pastikan {param} pada URI route sama banyak dan urutannya dengan parameter method.', 'Berikan nilai default pada parameter opsional.'], null],
            ['/Add \[([^\]]+)\] to fillable property/', 'Kolom belum di $fillable', 'Kolom {1} diisi lewat mass assignment tetapi tidak ada di $fillable.', 'Data tidak tersimpan; bisa jadi data kosong di database.', 'Tambahkan ke $fillable', ['Tambahkan kolom ke $fillable pada model.'], "protected \$fillable = ['{1}'];"],
            ['/Allowed memory size|Maximum execution time/i', 'Memori/waktu eksekusi habis', 'Proses terlalu berat (data besar atau loop tak berujung).', 'Request gagal "Memory exhausted" atau "Timeout" (500).', 'Batasi data dan optimalkan query', ['Gunakan paginate()/chunk() daripada all().', 'Tambahkan eager loading with() untuk mencegah N+1.', 'Naikkan memory_limit hanya sebagai langkah terakhir.'], null],
            ['/Failed to open stream|No such file or directory|Permission denied/i', 'File/folder tidak bisa diakses', 'PHP tidak menemukan file atau tidak punya izin.', 'File tidak bisa dibaca/ditulis; fitur bergantung file gagal.', 'Periksa path dan izin', ['Periksa path dan huruf besar-kecil.', 'Pastikan storage/ dan bootstrap/cache/ dapat ditulis oleh user web server.', 'Untuk file upload: php artisan storage:link.'], null],
            ['/Array to string conversion|htmlspecialchars\(\).*array|Illegal offset type/i', 'Array dicetak sebagai teks', 'Nilai array dipakai di tempat yang butuh string (mis. {{ $array }} di Blade).', 'Halaman error "Array to string conversion" (500).', 'Ubah array menjadi string', ['Gunakan implode(\', \', $arr) atau @json($arr).', 'Atau loop dengan @foreach.'], "{{ implode(', ', \$arr) }}"],
            ['/Method ([\w\\\\]+)::(\w+) does not exist/', 'Method tidak ada pada objek', 'Method {2} dipanggil pada {1} tetapi tidak ada.', 'Error "Method does not exist" (500).', 'Periksa tipe objek dan nama method', ['Periksa ejaan method dan tipe objek (Collection vs Model vs Builder).', 'Jika scope/relasi, pastikan didefinisikan di model.'], null],
            ['/No query results for model \[([^\]]+)\]/', 'Data tidak ditemukan', 'findOrFail()/firstOrFail() pada {1} tidak menemukan data (menjadi 404).', 'Halaman 404; user tidak melihat data.', 'Pastikan data ada atau tangani 404', ['Jalankan seeder atau buat data contoh.', 'Periksa id pada URL.'], 'php artisan db:seed'],
            ['/Missing required parameter|Missing required parameters for \[Route: ([^\]]+)\]/', 'Parameter route kurang', 'Pemanggilan route() tidak menyertakan semua parameter URI.', 'Error "Missing required parameter" (500).', 'Sertakan parameter di route()', ['Sertakan parameter: route(\'nama\', [\'id\' => $row->id]).', 'Pastikan $row tidak null saat memanggil route().'], null],
            ['/CSRF token mismatch|Page Expired/i', 'Token CSRF tidak valid', 'Token CSRF hilang atau kedaluwarsa.', 'Form POST ditolak 419 Page Expired.', 'Sertakan @csrf', ['Tambahkan @csrf pada form atau header X-CSRF-TOKEN pada AJAX.'], null],
            ['/Unauthenticated|This action is unauthorized/i', 'Butuh login / izin', 'Request ditolak autentikasi atau otorisasi (bukan bug kode).', 'Request ditolak 401/403.', 'Login dengan user yang berizin', ['Pastikan AUDIT_USER_ID menunjuk user yang punya izin untuk halaman ini.'], null],
        ];

        foreach ($kb as [$re, $title, $detail, $impact, $fix, $steps, $code]) {
            if (preg_match($re, $t, $mm)) {
                $rep = fn (string $s) => preg_replace_callback('/\{(\d)\}/', fn ($x) => $mm[(int) $x[1]] ?? '', $s);
                return $this->sol(
                    $title,
                    $rep($detail) . " Lokasi: $loc.",
                    $rep($impact),
                    'Halaman tidak bisa dibuka; user melihat error 500.',
                    $fix,
                    array_map($rep, $steps),
                    $code ? $rep($code) : null,
                    [
                        $this->ex('Lihat log lengkap', 'Baca storage/logs/laravel.log untuk stack trace penuh dan baris tepat penyebab error.'),
                        $this->ex('Cek ulang setelah diperbaiki', 'Klik "Cek ulang" agar request ini dijalankan lagi (dalam transaksi yang di-rollback).'),
                    ]
                );
            }
        }

        return $this->sol(
            'Error server saat halaman dijalankan',
            ($t !== '' ? $t : 'Server mengembalikan error.') . " Lokasi: $loc.",
            'Server mengembalikan error 500 saat memproses halaman.',
            'Halaman tidak bisa dibuka; user melihat error 500.',
            'Telusuri exception di log lalu perbaiki penyebabnya',
            ['Buka file dan baris pada lokasi di atas.', 'Lihat stack trace penuh: tail -n 80 storage/logs/laravel.log', 'Aktifkan APP_DEBUG=true di lokal untuk melihat halaman error terperinci.'],
            'tail -n 80 storage/logs/laravel.log',
            [
                $this->ex('Reproduksi manual', 'Buka URL halaman di browser dengan user yang sama dan lihat halaman error Laravel.'),
                $this->ex('Bersihkan cache', 'php artisan optimize:clear bila error muncul setelah mengubah config/route/view.'),
            ]
        );
    }

    private function rGeneric(array $c, array $m): array
    {
        $loc = $c['file'] ? $c['file'] . ($c['line'] ? ':' . $c['line'] : '') : '(tanpa lokasi file)';
        return $this->sol(
            $c['type'] !== '' ? $c['type'] : 'Temuan audit',
            $c['message'] . "\nLokasi: $loc",
            'Audit mendeteksi masalah pada lokasi ini.',
            'Bisa memengaruhi fungsi atau kualitas kode.',
            'Telusuri lokasi dan perbaiki sesuai pesan',
            ['Buka lokasi di VS Code (tombol "Buka di VS Code").', 'Baca pesan dengan teliti; nama class/file/route yang disebut di dalam tanda kutip adalah kunci masalahnya.', 'Perbaiki lalu klik "Cek ulang" untuk memastikan temuan hilang.'],
            null,
            [
                $this->ex('Cari pola serupa', 'Cari teks yang sama di seluruh project; temuan serupa biasanya punya akar masalah yang sama.'),
                $this->ex('Beri umpan balik', 'Klik "Kurang tepat" agar jenis masalah ini diberi bobot rendah, dan tambahkan aturan khusus bila sering muncul.'),
            ]
        );
    }
}