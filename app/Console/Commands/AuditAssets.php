<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * Letakkan di: app/Console/Commands/AuditAssets.php
 *
 *   php artisan audit:assets                    # analisis statis semua fitur
 *   php artisan audit:assets --feature=orders   # satu fitur (folder di resources/views)
 *   php artisan audit:assets --render --user=1  # + render halaman GET (jalankan di lokal/staging!)
 *   php artisan audit:assets --external         # + cek CDN lewat HTTP HEAD
 */
class AuditAssets extends Command
{
    protected $signature = 'audit:assets
        {--feature= : Hanya audit satu fitur (nama folder di resources/views)}
        {--render : Render halaman GET tanpa parameter (cek error 500 & asset hasil render)}
        {--user= : ID user untuk login saat --render}
        {--external : Cek URL asset eksternal (CDN) lewat HTTP HEAD}
        {--skip-js-syntax : Lewati node --check}';

    protected $description = 'Audit per fitur: asset (css/js/gambar/vite/mix) ada & huruf sesuai, JS valid, fungsi/elemen/AJAX/library konsisten';

    private const VENDOR_JS = '/(\.min\.|vendor|node_modules|plugins?\/|libs?\/|jquery|bootstrap|adminlte|\/build\/)/i';

    private const SKIP_CALLS = [
        'if', 'for', 'while', 'switch', 'return', 'function', 'typeof', 'void', 'new', 'alert', 'confirm',
        'prompt', 'event', 'this', 'console', 'window', 'document', 'setTimeout', 'setInterval', 'parseInt',
        'parseFloat', 'Number', 'String', 'Boolean', 'JSON', 'Math', 'Date', 'Array', 'Object', 'fetch',
        'encodeURIComponent', 'decodeURIComponent', 'history', 'location', 'Swal', 'bootstrap',
    ];

    // nama => [regex pemakaian di JS (tanpa string), regex nama sumber script yang memuatnya]
    private const LIBS = [
        'jQuery'       => ['/(?<![\w$.])(?:\$|jQuery)\s*[(.]/', '/jquery/i'],
        'axios'        => ['/(?<![\w$.])axios\s*[.(]/', '/axios/i'],
        'SweetAlert'   => ['/(?<![\w$.])Swal\s*[.(]/', '/sweetalert/i'],
        'Bootstrap JS' => ['/(?<![\w$.])bootstrap\.(?:Modal|Tooltip|Toast|Dropdown|Collapse|Popover|Offcanvas)/', '/bootstrap/i'],
        'Chart.js'     => ['/\bnew\s+Chart\s*\(/', '/chart/i'],
        'moment'       => ['/(?<![\w$.])moment\s*\(/', '/moment/i'],
        'DataTables'   => ['/\.DataTable\s*\(/', '/datatables?/i'],
        'select2'      => ['/\.select2\s*\(/', '/select2/i'],
    ];

    private array $issues = [];
    private array $seen = [];
    private array $views = [];       // 'orders.index' => path absolut
    private array $viewSrc = [];     // 'orders.index' => isi blade (tanpa komentar)
    private array $fragments = [];   // view yang di-extends / include (bukan halaman utuh)
    private array $jsCache = [];
    private array $jsChecked = [];
    private array $jsPages = [];     // path js => [ids tiap halaman yang memuatnya]
    private array $allFns = [];      // nama fungsi => [[rel, line, global]]
    private array $dirCache = [];
    private array $assetDone = [];
    private array $cssDone = [];
    private array $libReported = [];
    private array $extCache = [];
    private ?array $viteManifest = null;
    private ?bool $nodeOk = null;
    private string $feature = '-';

    /* ================================================================== */
    /*  Alur utama                                                         */
    /* ================================================================== */

    public function handle(): int
    {
        $this->indexViews();
        $this->indexJs();

        $only = $this->option('feature');

        foreach ($this->views as $name => $path) {
            $feat = $this->featureOf($name);
            if ($only && $feat !== $only) {
                continue;
            }
            $this->feature = $feat;
            $this->auditViewAssets($name, $path);
        }

        foreach ($this->views as $name => $path) {
            $feat = $this->featureOf($name);
            if (isset($this->fragments[$name]) || ($only && $feat !== $only)) {
                continue;
            }
            $this->feature = $feat;
            $this->auditPage($name);
        }

        $this->feature = '-';
        $this->auditSharedIds();

        if ($this->option('render')) {
            $this->renderRoutes();
        }

        $this->report();

        return collect($this->issues)->contains('level', 'ERROR') ? self::FAILURE : self::SUCCESS;
    }

    /* ================================================================== */
    /*  Util umum                                                          */
    /* ================================================================== */

    private function add(string $level, string $type, string $path, int $line, string $message): void
    {
        $rel = $this->relPath($path);
        $key = "$level|$type|$rel|$line|$message";
        if (isset($this->seen[$key])) {
            return;
        }
        $this->seen[$key] = true;
        $this->issues[] = ['level' => $level, 'feature' => $this->feature, 'type' => $type, 'rel' => $rel, 'line' => $line, 'message' => $message];
    }

    private function relPath(string $p): string
    {
        return str_replace([base_path() . DIRECTORY_SEPARATOR, '\\'], ['', '/'], $p);
    }

    private function lineAt(string $text, int $offset): int
    {
        return substr_count($text, "\n", 0, $offset) + 1;
    }

    private function featureOf(string $view): string
    {
        return str_contains($view, '.') ? explode('.', $view)[0] : '_root';
    }

    private function ls(string $dir): array
    {
        return $this->dirCache[$dir] ??= is_dir($dir) ? array_values(array_diff(scandir($dir), ['.', '..'])) : [];
    }

    /** Cek path secara case-sensitive. Return [ok|case|missing, path sebenarnya] */
    private function resolveExact(string $base, string $rel): array
    {
        $parts = [];
        foreach (explode('/', str_replace('\\', '/', $rel)) as $p) {
            if ($p === '' || $p === '.') {
                continue;
            }
            if ($p === '..') {
                array_pop($parts);
                continue;
            }
            $parts[] = $p;
        }
        $cur = rtrim($base, '/\\');
        $actual = [];
        $case = false;
        foreach ($parts as $p) {
            $list = $this->ls($cur);
            $hit = null;
            if (in_array($p, $list, true)) {
                $hit = $p;
            } else {
                foreach ($list as $f) {
                    if (strcasecmp($f, $p) === 0) {
                        $hit = $f;
                        $case = true;
                        break;
                    }
                }
            }
            if ($hit === null) {
                return ['missing', $rel];
            }
            $actual[] = $hit;
            $cur .= '/' . $hit;
        }
        return [$case ? 'case' : 'ok', implode('/', $actual)];
    }

    /* ================================================================== */
    /*  Indeks view & JS                                                   */
    /* ================================================================== */

    private function indexViews(): void
    {
        $dir = resource_path('views');
        if (!is_dir($dir)) {
            return;
        }
        foreach (File::allFiles($dir) as $f) {
            if (!str_ends_with($f->getFilename(), '.blade.php')) {
                continue;
            }
            $rel = substr($f->getRelativePathname(), 0, -10);
            $name = str_replace(['/', DIRECTORY_SEPARATOR], '.', $rel);
            $this->views[$name] = $f->getPathname();
            $this->viewSrc[$name] = $this->cleanBlade(file_get_contents($f->getPathname()));
        }
        ksort($this->views);
        foreach ($this->viewSrc as $src) {
            foreach ($this->chainRefs($src) as $n) {
                $this->fragments[$n] = true;
            }
        }
    }

    private function cleanBlade(string $raw): string
    {
        $nl = fn ($m) => str_repeat("\n", substr_count($m[0], "\n"));
        $raw = preg_replace_callback('/\{\{--.*?--\}\}/s', $nl, $raw);
        return preg_replace_callback('/<!--.*?-->/s', $nl, $raw);
    }

    private function chainRefs(string $src): array
    {
        $out = [];
        preg_match_all('/@(?:extends|include|includeIf|includeWhen|includeUnless|includeFirst|each)\s*\(\s*(?:[^\'"\)]*,\s*)?[\'"]([\w.\-]+)[\'"]/', $src, $m);
        foreach ($m[1] as $n) {
            if (isset($this->views[$n])) {
                $out[] = $n;
            }
        }
        preg_match_all('/<x-([\w.\-]+)/', $src, $m);
        foreach ($m[1] as $n) {
            foreach (["components.$n", "components.$n.index"] as $cand) {
                if (isset($this->views[$cand])) {
                    $out[] = $cand;
                    break;
                }
            }
        }
        return array_values(array_unique($out));
    }

    private function walk(string $view, array &$names): void
    {
        if (in_array($view, $names, true) || !isset($this->views[$view])) {
            return;
        }
        $names[] = $view;
        foreach ($this->chainRefs($this->viewSrc[$view]) as $n) {
            $this->walk($n, $names);
        }
    }

    private function indexJs(): void
    {
        $files = [];
        foreach ([public_path(), resource_path('js')] as $dir) {
            if (!is_dir($dir)) {
                continue;
            }
            foreach (File::allFiles($dir) as $f) {
                if (in_array($f->getExtension(), ['js', 'mjs'], true)) {
                    $files[] = $f->getPathname();
                }
            }
        }
        foreach ($files as $abs) {
            $info = $this->jsInfo($abs);
            foreach ($info['functions'] as $fn => $d) {
                $this->allFns[$fn][] = [$this->relPath($abs), $d['line'], $d['global']];
            }
        }
    }

    private function isVendor(string $rel): bool
    {
        return (bool) preg_match(self::VENDOR_JS, str_replace('\\', '/', $rel));
    }

    private function jsInfo(string $abs): array
    {
        if (isset($this->jsCache[$abs])) {
            return $this->jsCache[$abs];
        }
        $rel = $this->relPath($abs);
        $skip = $this->isVendor($rel) || !is_file($abs) || filesize($abs) > 400000;
        return $this->jsCache[$abs] = $skip
            ? $this->emptyInfo()
            : $this->analyzeJsCode(file_get_contents($abs), $rel, 1);
    }

    private function emptyInfo(): array
    {
        return ['module' => false, 'functions' => [], 'ids' => [], 'ajax' => [], 'routes' => [], 'libs' => [], 'csrf' => false];
    }

    /* ================================================================== */
    /*  Asset di blade                                                     */
    /* ================================================================== */

    private function extractAssets(string $src): array
    {
        $refs = [];
        $push = function (string $val, int $off, string $tag) use ($src, &$refs) {
            $line = $this->lineAt($src, $off);
            if (preg_match('/(?:secure_)?asset\(\s*[\'"]([^\'"]+)[\'"]/', $val, $m)) {
                $refs["{$m[1]}@$line"] = ['kind' => 'local', 'rel' => $m[1], 'line' => $line, 'tag' => $tag];
            } elseif (preg_match('/mix\(\s*[\'"]([^\'"]+)[\'"]/', $val, $m)) {
                $refs["{$m[1]}@$line"] = ['kind' => 'mix', 'rel' => $m[1], 'line' => $line, 'tag' => $tag];
            } elseif (preg_match('/\{\{|\{!!|@|\$/', $val) || $val === '') {
                return;
            } elseif (preg_match('#^(https?:)?//#i', $val)) {
                $refs["$val@$line"] = ['kind' => 'external', 'rel' => $val, 'line' => $line, 'tag' => $tag];
            } elseif (!preg_match('/^(data:|#|mailto:|tel:|javascript:)/i', $val)) {
                $refs["$val@$line"] = ['kind' => 'local', 'rel' => $val, 'line' => $line, 'tag' => $tag];
            }
        };

        preg_match_all('/<(script|link|img|source|video|audio)\b([^>]*)>/is', $src, $tags, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($tags as $t) {
            $tag = strtolower($t[1][0]);
            $attrs = $t[2][0];
            if ($tag === 'link' && !preg_match('/rel\s*=\s*["\'][^"\']*(stylesheet|icon|preload|manifest)/i', $attrs)) {
                continue;
            }
            if (preg_match('/\b(?:src|href)\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/i', $attrs, $v)) {
                $push($v[1] !== '' ? $v[1] : ($v[2] ?? ''), $t[0][1], $tag);
            }
        }

        preg_match_all('/(?:secure_)?asset\(\s*[\'"]([^\'"]+)[\'"]\s*\)/', $src, $m, PREG_OFFSET_CAPTURE);
        foreach ($m[1] as [$rel, $off]) {
            $line = $this->lineAt($src, $off);
            $refs["$rel@$line"] ??= ['kind' => 'local', 'rel' => $rel, 'line' => $line, 'tag' => ''];
        }

        preg_match_all('/@vite\(\s*(\[[^\]]*\]|[\'"][^\'"]+[\'"])/s', $src, $m, PREG_OFFSET_CAPTURE);
        foreach ($m[1] as [$list, $off]) {
            preg_match_all('/[\'"]([^\'"]+)[\'"]/', $list, $e);
            foreach ($e[1] as $entry) {
                $line = $this->lineAt($src, $off);
                $refs["$entry@$line"] = ['kind' => 'vite', 'rel' => $entry, 'line' => $line, 'tag' => ''];
            }
        }

        return array_values($refs);
    }

    private function auditViewAssets(string $name, string $path): void
    {
        $src = $this->viewSrc[$name];

        foreach ($this->extractAssets($src) as $r) {
            match ($r['kind']) {
                'local'    => $this->checkLocal($r['rel'], $path, $r['line']),
                'vite'     => $this->checkVite($r['rel'], $path, $r['line']),
                'mix'      => $this->checkMix($r['rel'], $path, $r['line']),
                'external' => $this->option('external') ? $this->checkExternal($r['rel'], $path, $r['line']) : null,
            };
        }

        preg_match_all('/(?<![\w>$])route\(\s*[\'"]([^\'"$]+)[\'"]/', $src, $m, PREG_OFFSET_CAPTURE);
        foreach ($m[1] as [$rn, $off]) {
            if (!Route::has($rn)) {
                $this->add('ERROR', 'Route name', $path, $this->lineAt($src, $off), "route('$rn') tidak terdaftar");
            }
        }
    }

    private function checkLocal(string $rel, string $label, int $line): ?string
    {
        $rel = preg_replace('/[?#].*$/', '', $rel);
        if ($rel === '') {
            return null;
        }
        [$st, $actual] = $this->resolveExact(public_path(), $rel);
        if ($st === 'missing') {
            $this->add('ERROR', 'Asset tidak ada', $label, $line, "public/" . ltrim($rel, '/') . ' tidak ditemukan');
            return null;
        }
        if ($st === 'case') {
            $this->add('ERROR', 'Huruf berbeda', $label, $line, "Ditulis '$rel', file sebenarnya 'public/$actual' (gagal di Linux)");
        }
        $abs = public_path($actual);
        if (is_dir($abs)) {
            return null;
        }
        if (!is_file($abs)) {
            $this->add('ERROR', 'Asset tidak dapat dibaca', $label, $line, "public/$actual bukan file yang dapat diakses (target link mungkin tidak ada)");
            return null;
        }
        $size = filesize($abs);
        if ($size === false) {
            $this->add('ERROR', 'Asset tidak dapat dibaca', $label, $line, "Ukuran public/$actual tidak dapat diperiksa");
            return null;
        }
        if ($size === 0) {
            $this->add('WARNING', 'Asset kosong', $label, $line, "public/$actual berukuran 0 byte");
        }
        if (str_ends_with(strtolower($abs), '.css')) {
            $this->checkCss($abs);
        }
        return $abs;
    }

    private function checkCss(string $abs): void
    {
        if (isset($this->cssDone[$abs]) || !is_file($abs)) {
            return;
        }
        $size = filesize($abs);
        if ($size === false || $size > 1500000) {
            return;
        }
        $this->cssDone[$abs] = true;
        $css = file_get_contents($abs);
        $css = preg_replace_callback('#/\*.*?\*/#s', fn ($m) => str_repeat("\n", substr_count($m[0], "\n")), $css);
        $relCss = ltrim(str_replace('\\', '/', substr($abs, strlen(public_path()))), '/');
        $dir = dirname($relCss);
        $dir = $dir === '.' ? '' : $dir;

        preg_match_all('/url\(\s*[\'"]?([^\'")]+?)[\'"]?\s*\)|@import\s+[\'"]([^\'"]+)[\'"]/i', $css, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($m as $set) {
            $url = trim(($set[1][0] ?? '') !== '' ? $set[1][0] : ($set[2][0] ?? ''));
            if ($url === '' || preg_match('#^(data:|https?:|//|\#)#i', $url)) {
                continue;
            }
            $url = preg_replace('/[?#].*$/', '', $url);
            $target = str_starts_with($url, '/') ? $url : "$dir/$url";
            [$st, $actual] = $this->resolveExact(public_path(), $target);
            $line = $this->lineAt($css, $set[0][1]);
            if ($st === 'missing') {
                $this->add('ERROR', 'Asset CSS', $abs, $line, "'$url' tidak ditemukan");
            } elseif ($st === 'case') {
                $this->add('ERROR', 'Huruf berbeda', $abs, $line, "Ditulis '$url', file sebenarnya '$actual'");
            }
        }
    }

    private function viteManifest(): ?array
    {
        if ($this->viteManifest !== null) {
            return $this->viteManifest ?: null;
        }
        foreach ([public_path('build/manifest.json'), public_path('build/.vite/manifest.json')] as $f) {
            if (is_file($f)) {
                return $this->viteManifest = (json_decode(file_get_contents($f), true) ?: []);
            }
        }
        $this->viteManifest = [];
        return null;
    }

    private function checkVite(string $entry, string $label, int $line): void
    {
        [$st, $actual] = $this->resolveExact(base_path(), $entry);
        if ($st === 'missing') {
            $this->add('ERROR', 'Vite entry', $label, $line, "$entry tidak ditemukan");
        } elseif ($st === 'case') {
            $this->add('ERROR', 'Huruf berbeda', $label, $line, "Ditulis '$entry', file sebenarnya '$actual'");
        }
        if (is_file(public_path('hot'))) {
            return; // dev server aktif
        }
        $manifest = $this->viteManifest();
        if ($manifest === null) {
            $this->add('WARNING', 'Vite build', $label, $line, 'public/build/manifest.json tidak ada (jalankan npm run build)');
        } elseif (!isset($manifest[$entry])) {
            $this->add('ERROR', 'Vite manifest', $label, $line, "'$entry' belum ada di manifest, build ulang");
        }
    }

    private function checkMix(string $rel, string $label, int $line): void
    {
        $f = public_path('mix-manifest.json');
        if (!is_file($f)) {
            $this->add('WARNING', 'Mix', $label, $line, 'public/mix-manifest.json tidak ada (jalankan npm run prod)');
            return;
        }
        $m = json_decode(file_get_contents($f), true) ?: [];
        if (!isset($m['/' . ltrim($rel, '/')])) {
            $this->add('ERROR', 'Mix manifest', $label, $line, "'$rel' tidak ada di mix-manifest.json");
        }
    }

    private function checkExternal(string $url, string $label, int $line): void
    {
        $url = str_starts_with($url, '//') ? "https:$url" : $url;
        if (!isset($this->extCache[$url])) {
            try {
                $this->extCache[$url] = Http::timeout(6)->head($url)->status();
            } catch (Throwable $e) {
                $this->extCache[$url] = 0;
            }
        }
        $s = $this->extCache[$url];
        if ($s === 0 || $s >= 400) {
            $this->add('ERROR', 'Asset eksternal', $label, $line, "$url tidak bisa diakses (status " . ($s ?: 'timeout') . ')');
        }
    }

    /* ================================================================== */
    /*  Audit halaman: JS, fungsi, elemen, library, AJAX                   */
    /* ================================================================== */

    private function pageInfo(string $view): array
    {
        $names = [];
        $this->walk($view, $names);
        $p = ['views' => $names, 'scripts' => [], 'externals' => [], 'bundled' => false, 'ids' => [],
              'inline' => [], 'handlers' => [], 'srcs' => [], 'csrf' => false];

        foreach ($names as $n) {
            $src = $this->viewSrc[$n];
            $file = $this->views[$n];

            foreach ($this->extractAssets($src) as $r) {
                if ($r['kind'] === 'vite' || $r['kind'] === 'mix') {
                    $p['bundled'] = true;
                    $p['srcs'][] = $r['rel'];
                    continue;
                }
                if ($r['kind'] === 'external') {
                    if ($r['tag'] === 'script' || preg_match('/\.m?js(\?|$)/i', $r['rel'])) {
                        $p['externals'][] = $r['rel'];
                        $p['srcs'][] = $r['rel'];
                    }
                    continue;
                }
                if ($r['tag'] === 'script' || preg_match('/\.m?js(\?|$)/i', $r['rel'])) {
                    [$st, $actual] = $this->resolveExact(public_path(), preg_replace('/[?#].*$/', '', $r['rel']));
                    if ($st !== 'missing') {
                        $p['scripts'][] = public_path($actual);
                        $p['srcs'][] = $actual;
                    }
                }
            }

            preg_match_all('/\bid\s*=\s*(?:"([^"{]*)"|\'([^\'{]*)\')/i', $src, $m);
            foreach (array_merge($m[1], $m[2]) as $id) {
                if ($id !== '') {
                    $p['ids'][$id] = true;
                }
            }

            if (preg_match('/csrf-token|@csrf|name\s*=\s*["\']_token/i', $src)) {
                $p['csrf'] = true;
            }

            preg_match_all('/<script\b(?![^>]*\bsrc\s*=)([^>]*)>(.*?)<\/script>/is', $src, $sm, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
            foreach ($sm as $s) {
                if (preg_match('/type\s*=\s*["\'](?!text\/javascript|module)[^"\']*["\']/i', $s[1][0])) {
                    continue; // json / template
                }
                $p['inline'][] = [$this->bladeToJs($s[2][0]), $file, $this->lineAt($src, $s[2][1])];
            }

            preg_match_all('/\bon(?:click|dblclick|change|input|submit|keyup|keydown|keypress|blur|focus|load|mouseover|mouseout)\s*=\s*(?:"([^"]*)"|\'([^\']*)\')/i', $src, $hm, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
            foreach ($hm as $h) {
                $code = $h[1][0] !== '' ? $h[1][0] : ($h[2][0] ?? '');
                $code = preg_replace('/\{\{.*?\}\}|\{!!.*?!!\}/s', '0', $code);
                preg_match_all('/(?<![\w$.])(?<!new )([A-Za-z_$][\w$]*)\s*\(/', $code, $cm);
                foreach (array_unique($cm[1]) as $fn) {
                    if (!in_array($fn, self::SKIP_CALLS, true) && $fn !== '$' && $fn !== 'jQuery') {
                        $p['handlers'][] = [$file, $this->lineAt($src, $h[0][1]), $fn];
                    }
                }
            }
        }

        $p['scripts'] = array_values(array_unique($p['scripts']));
        return $p;
    }

    private function auditPage(string $view): void
    {
        $p = $this->pageInfo($view);
        $infos = []; // [label, info]
        $loaded = [];

        // --- file JS lokal ---
        foreach ($p['scripts'] as $abs) {
            $rel = $this->relPath($abs);
            if (!isset($this->jsChecked[$abs])) {
                $this->jsChecked[$abs] = true;
                if (!$this->isVendor($rel) && is_file($abs) && filesize($abs) <= 400000) {
                    $code = file_get_contents($abs);
                    $module = $this->jsInfo($abs)['module'];
                    $this->nodeCheck($code, $module ? 'mjs' : 'js', $abs, 1, 'ERROR', 'JS syntax');
                    $this->checkAjax($this->jsInfo($abs), $abs);
                }
            }
            $this->jsPages[$abs][] = $p['ids'];
            $infos[] = [$abs, $this->jsInfo($abs)];
        }

        // --- script inline ---
        foreach ($p['inline'] as [$code, $file, $start]) {
            $this->nodeCheck($code, 'js', $file, $start, 'WARNING', 'JS syntax (inline)');
            $info = $this->analyzeJsCode($code, $file, $start);
            $this->checkAjax($info, $file);
            foreach ($info['ids'] as [$id, $line]) {
                if (!isset($p['ids'][$id])) {
                    $this->add('WARNING', 'JS elemen', $file, $line, "JS mencari #$id tetapi tidak ada elemen id=\"$id\" di halaman '$view'");
                }
            }
            $infos[] = [$file, $info];
        }

        // --- fungsi yang tersedia di halaman ---
        foreach ($infos as [$label, $info]) {
            foreach ($info['functions'] as $fn => $d) {
                $where = $this->relPath($label) . ':' . $d['line'];
                if (!isset($loaded[$fn]) || $d['global']) {
                    $loaded[$fn] = ['global' => $d['global'], 'where' => $where];
                }
            }
        }

        // --- handler onclick="..." di HTML ---
        foreach ($p['handlers'] as [$file, $line, $fn]) {
            if (isset($loaded[$fn])) {
                if (!$loaded[$fn]['global']) {
                    $this->add('ERROR', 'JS scope', $file, $line,
                        "{$fn}() ada di {$loaded[$fn]['where']} tetapi di dalam scope/module (bukan global), tidak bisa dipanggil dari atribut HTML");
                }
                continue;
            }
            if (isset($this->allFns[$fn])) {
                [$w, $l] = $this->allFns[$fn][0];
                $this->add('ERROR', 'JS tidak dimuat', $file, $line, "{$fn}() didefinisikan di $w:$l tetapi file itu tidak dimuat di halaman '$view'");
                continue;
            }
            $level = ($p['externals'] || $p['bundled']) ? 'WARNING' : 'ERROR';
            $this->add($level, 'JS fungsi', $file, $line, "{$fn}() tidak didefinisikan di JS manapun" . ($level === 'WARNING' ? ' (mungkin dari library/bundle)' : ''));
        }

        // --- library & CSRF ---
        $haystack = implode(' ', $p['srcs']);
        foreach ($infos as [$label, $info]) {
            if (!$p['bundled']) {
                foreach ($info['libs'] as $lib => $line) {
                    $k = $label . $lib;
                    if (!isset($this->libReported[$k]) && !preg_match(self::LIBS[$lib][1], $haystack)) {
                        $this->libReported[$k] = true;
                        $this->add('ERROR', 'JS library', $label, $line, "Memakai $lib tetapi library-nya tidak dimuat (contoh halaman: $view)");
                    }
                }
            }
            if (!$p['csrf'] && !$info['csrf']) {
                foreach ($info['ajax'] as [$method, $url, $line, $lib]) {
                    if ($method !== 'GET' && $lib !== 'axios' && str_starts_with($url, '/') && !preg_match('/[{$\s]|__route__/', $url)) {
                        [$st, $route] = $this->matchRoute($method, preg_replace('/[?#].*$/', '', $url));
                        if ($st === 'ok' && $route && in_array('web', (array) $route->gatherMiddleware(), true)) {
                            $this->add('WARNING', 'CSRF', $label, $line, "$method $url tanpa token CSRF di halaman '$view' (kemungkinan error 419)");
                        }
                    }
                }
            }
        }
    }

    private function auditSharedIds(): void
    {
        foreach ($this->jsPages as $abs => $pages) {
            $union = [];
            foreach ($pages as $ids) {
                $union += $ids;
            }
            foreach ($this->jsInfo($abs)['ids'] as [$id, $line]) {
                if (!isset($union[$id])) {
                    $this->add('WARNING', 'JS elemen', $abs, $line, "Mencari #$id tetapi tidak ada elemen id=\"$id\" di halaman manapun yang memuat file ini");
                }
            }
        }
    }

    /* ================================================================== */
    /*  Analisis JS                                                        */
    /* ================================================================== */

    private function nodeAvailable(): bool
    {
        if ($this->nodeOk !== null) {
            return $this->nodeOk;
        }
        if ($this->option('skip-js-syntax')) {
            return $this->nodeOk = false;
        }
        exec('node --version 2>&1', $o, $rc);
        if ($rc !== 0) {
            $this->add('WARNING', 'Node', 'package.json', 0, 'node tidak ditemukan, pengecekan syntax JS dilewati');
        }
        return $this->nodeOk = ($rc === 0);
    }

    private function nodeCheck(string $code, string $ext, string $label, int $lineBase, string $level, string $type): void
    {
        if (trim($code) === '' || !$this->nodeAvailable()) {
            return;
        }
        $base = tempnam(sys_get_temp_dir(), 'aud');
        $tmp = "$base.$ext";
        file_put_contents($tmp, $code);
        exec('node --check ' . escapeshellarg($tmp) . ' 2>&1', $out, $rc);
        @unlink($tmp);
        @unlink($base);
        if ($rc === 0) {
            return;
        }
        $txt = implode("\n", $out);
        $line = preg_match('/^.*?:(\d+)\s*$/m', $txt, $m) ? (int) $m[1] : 1;
        $msg = preg_match('/^(\w*Error: .+)$/m', $txt, $e) ? $e[1] : 'Syntax error';
        if ($level === 'WARNING') {
            $msg .= ' (bisa karena sintaks Blade di dalam script)';
        }
        $this->add($level, $type, $label, $lineBase + $line - 1, $msg);
    }

    private function bladeToJs(string $s): string
    {
        $nl = fn ($m) => str_repeat("\n", substr_count($m[0], "\n"));
        $s = preg_replace_callback('/\{\{\s*route\(\s*[\'"]([^\'"]+)[\'"][^}]*?\)\s*\}\}/s', fn ($m) => '/__route__' . $m[1] . '__' . $nl($m), $s);
        $s = preg_replace_callback('/\{\{\s*url\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*\}\}/s', fn ($m) => '/' . ltrim($m[1], '/') . $nl($m), $s);
        $s = preg_replace_callback('/\{\{.*?\}\}|\{!!.*?!!\}/s', fn ($m) => '0' . $nl($m), $s);
        $s = preg_replace('/@json\((?:[^()]|\((?:[^()]|\([^()]*\))*\))*\)/', '0', $s);
        $s = preg_replace('/^\s*@(?:if|elseif|else|endif|foreach|endforeach|forelse|endforelse|for|endfor|while|endwhile|isset|endisset|auth|endauth|guest|endguest|can|endcan|cannot|push|endpush|prepend|endprepend|section|endsection|php|endphp|unless|endunless|empty|endempty|switch|case|break|endswitch|stack|yield|include\w*|once|endonce|csrf|lang|error|enderror|production|endproduction|env|endenv|verbatim|endverbatim)\b.*$/m', '', $s);
        return $s;
    }

    /** Hapus komentar JS. Return [dengan string, string dikosongkan] dengan panjang sama. */
    private function stripJs(string $code): array
    {
        $n = strlen($code);
        $a = '';
        $b = '';
        $i = 0;
        while ($i < $n) {
            $ch = $code[$i];
            $nx = $code[$i + 1] ?? '';
            if ($ch === '/' && $nx === '/') {
                while ($i < $n && $code[$i] !== "\n") {
                    $a .= ' ';
                    $b .= ' ';
                    $i++;
                }
                continue;
            }
            if ($ch === '/' && $nx === '*') {
                $end = strpos($code, '*/', $i + 2);
                $end = $end === false ? $n : $end + 2;
                $blank = preg_replace('/[^\n]/', ' ', substr($code, $i, $end - $i));
                $a .= $blank;
                $b .= $blank;
                $i = $end;
                continue;
            }
            if ($ch === "'" || $ch === '"' || $ch === '`') {
                $q = $ch;
                $a .= $q;
                $b .= $q;
                $i++;
                while ($i < $n && $code[$i] !== $q) {
                    if ($code[$i] === '\\' && $i + 1 < $n) {
                        $a .= $code[$i] . $code[$i + 1];
                        $b .= ' ' . ($code[$i + 1] === "\n" ? "\n" : ' ');
                        $i += 2;
                        continue;
                    }
                    if ($code[$i] === "\n" && $q !== '`') {
                        break;
                    }
                    $a .= $code[$i];
                    $b .= $code[$i] === "\n" ? "\n" : ' ';
                    $i++;
                }
                if ($i < $n && $code[$i] === $q) {
                    $a .= $q;
                    $b .= $q;
                    $i++;
                }
                continue;
            }
            $a .= $ch;
            $b .= $ch;
            $i++;
        }
        return [$a, $b];
    }

    private function analyzeJsCode(string $code, string $label, int $lineBase): array
    {
        [$a, $b] = $this->stripJs($code);
        $info = $this->emptyInfo();
        $L = fn (string $text, int $off) => $this->lineAt($text, $off) + $lineBase - 1;

        $info['module'] = (bool) preg_match('/^\s*(?:import\s[^;\n]*\sfrom\s|import\s*[\'"]|export\s)/m', $b);
        $info['csrf'] = (bool) preg_match('/csrf|_token|xsrf/i', $a);

        // definisi fungsi + apakah global (depth 0 & bukan module)
        $defs = [
            '/\bfunction\s*\*?\s+([A-Za-z_$][\w$]*)\s*\(/',
            '/\b(?:var|let|const)\s+([A-Za-z_$][\w$]*)\s*=\s*(?:async\s+)?(?:function\b|\([^()]*\)\s*=>|[A-Za-z_$][\w$]*\s*=>)/',
        ];
        foreach ($defs as $re) {
            preg_match_all($re, $b, $m, PREG_OFFSET_CAPTURE);
            foreach ($m[1] as [$name, $off]) {
                $depth = substr_count($b, '{', 0, $off) - substr_count($b, '}', 0, $off);
                $global = !$info['module'] && $depth === 0;
                $prev = $info['functions'][$name]['global'] ?? false;
                $info['functions'][$name] = ['line' => $L($b, $off), 'global' => $global || $prev];
            }
        }
        preg_match_all('/\b(?:window|globalThis)\.([A-Za-z_$][\w$]*)\s*=(?!=)/', $b, $m, PREG_OFFSET_CAPTURE);
        foreach ($m[1] as [$name, $off]) {
            $info['functions'][$name] = ['line' => $L($b, $off), 'global' => true];
        }

        // elemen DOM yang dicari
        $idRegex = [
            '/getElementById\(\s*[\'"]([\w-]+)[\'"]\s*\)/',
            '/(?<![\w$])(?:\$|jQuery)\(\s*[\'"]#([\w-]+)[\'"]\s*\)/',
            '/querySelector(?:All)?\(\s*[\'"]#([\w-]+)[\'"]\s*\)/',
        ];
        foreach ($idRegex as $re) {
            preg_match_all($re, $a, $m, PREG_OFFSET_CAPTURE);
            foreach ($m[1] as [$id, $off]) {
                $info['ids'][] = [$id, $L($a, $off)];
            }
        }

        // pemanggilan AJAX
        preg_match_all('/\baxios\.(get|post|put|patch|delete)\(\s*([\'"])([^\'"]+)\2(?!\s*\+)/i', $a, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($m as $s) {
            $info['ajax'][] = [strtoupper($s[1][0]), $s[3][0], $L($a, $s[0][1]), 'axios'];
        }
        preg_match_all('/(?<![\w$])\$\.(get|post|getJSON)\(\s*([\'"])([^\'"]+)\2(?!\s*\+)/', $a, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($m as $s) {
            $info['ajax'][] = [$s[1][0] === 'post' ? 'POST' : 'GET', $s[3][0], $L($a, $s[0][1]), 'jquery'];
        }
        preg_match_all('/(?<![\w$])\$\.ajax\(\s*\{(.*?)\}\s*\)/s', $a, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($m as $s) {
            if (preg_match('/\burl\s*:\s*([\'"])([^\'"]+)\1(?!\s*\+)/', $s[1][0], $u)) {
                $method = preg_match('/\b(?:type|method)\s*:\s*([\'"])(\w+)\1/i', $s[1][0], $mm) ? strtoupper($mm[2]) : 'GET';
                $info['ajax'][] = [$method, $u[2], $L($a, $s[0][1]), 'jquery'];
            }
        }
        preg_match_all('/\bfetch\(\s*([\'"])([^\'"]+)\1(?!\s*\+)/', $a, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        foreach ($m as $s) {
            $method = preg_match('/method\s*:\s*[\'"](\w+)[\'"]/i', substr($a, $s[0][1], 250), $mm) ? strtoupper($mm[1]) : 'GET';
            $info['ajax'][] = [$method, $s[2][0], $L($a, $s[0][1]), 'fetch'];
        }

        // route() hasil konversi blade
        preg_match_all('/__route__([\w.\-]+)__/', $a, $m, PREG_OFFSET_CAPTURE);
        foreach ($m[1] as [$name, $off]) {
            $info['routes'][] = [$name, $L($a, $off)];
        }

        // library yang dipakai
        foreach (self::LIBS as $lib => [$re]) {
            if (preg_match($re, $b, $m, PREG_OFFSET_CAPTURE)) {
                $info['libs'][$lib] = $L($b, $m[0][1]);
            }
        }

        return $info;
    }

    private function matchRoute(string $method, string $path): array
    {
        try {
            $route = Route::getRoutes()->match(Request::create($path, $method));
            return ['ok', $route];
        } catch (MethodNotAllowedHttpException $e) {
            return ['method', null];
        } catch (NotFoundHttpException $e) {
            return ['notfound', null];
        } catch (Throwable $e) {
            return ['ok', null];
        }
    }

    private function checkAjax(array $info, string $label): void
    {
        foreach ($info['routes'] as [$name, $line]) {
            if (!Route::has($name)) {
                $this->add('ERROR', 'JS route', $label, $line, "route('$name') di JS tidak terdaftar");
            }
        }
        foreach ($info['ajax'] as [$method, $url, $line]) {
            if (preg_match('#^/__route__([\w.\-]+)__#', $url, $m)) {
                $r = Route::getRoutes()->getByName($m[1]);
                if ($r && !in_array($method, $r->methods(), true)) {
                    $this->add('ERROR', 'JS ajax', $label, $line, "Route '{$m[1]}' hanya menerima " . implode('|', $r->methods()) . ", JS memanggil $method");
                }
                continue;
            }
            if ($url === '' || $url[0] !== '/' || str_starts_with($url, '//') || preg_match('/[{$\s]/', $url)) {
                continue;
            }
            $path = preg_replace('/[?#].*$/', '', $url);
            [$st] = $this->matchRoute($method, $path);
            if ($st === 'notfound' && !is_file(public_path($path))) {
                $this->add('ERROR', 'JS ajax', $label, $line, "$method $path tidak cocok dengan route manapun");
            } elseif ($st === 'method') {
                $this->add('ERROR', 'JS ajax', $label, $line, "$path ada, tetapi method $method tidak diizinkan");
            }
        }
    }

    /* ================================================================== */
    /*  Render halaman (opsional)                                          */
    /* ================================================================== */

    private function renderRoutes(): void
    {
        $kernel = app(HttpKernel::class);
        if ($uid = $this->option('user')) {
            Auth::loginUsingId((int) $uid);
        }
        $host = parse_url((string) config('app.url'), PHP_URL_HOST);

        foreach (Route::getRoutes() as $route) {
            $uri = '/' . ltrim($route->uri(), '/');
            if (!in_array('GET', $route->methods(), true) || str_contains($uri, '{')) {
                continue;
            }
            if (preg_match('#^/(_|telescope|horizon|livewire|storage|sanctum|up$)|logout|signout|delete|destroy|download|export|print#i', $uri)) {
                continue;
            }
            $label = "[GET $uri]";
            $this->feature = explode('/', trim($uri, '/'))[0] ?: '_root';

            try {
                $response = $kernel->handle(Request::create($uri, 'GET'));
            } catch (Throwable $e) {
                $this->add('ERROR', 'Render', $label, 0, get_class($e) . ': ' . $e->getMessage());
                continue;
            }

            $code = $response->getStatusCode();
            if ($code >= 500) {
                $ex = $response->exception ?? null;
                $msg = $ex ? get_class($ex) . ': ' . mb_substr($ex->getMessage(), 0, 300) . ' @ ' . $this->relPath($ex->getFile()) . ':' . $ex->getLine() : 'Status ' . $code;
                $this->add('ERROR', 'Render 500', $label, 0, $msg);
                continue;
            }
            if ($code === 404) {
                $this->add('WARNING', 'Render 404', $label, 0, 'Route terdaftar tetapi halaman mengembalikan 404');
                continue;
            }
            if ($code !== 200 || !str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
                continue;
            }

            foreach ($this->extractAssets((string) $response->getContent()) as $r) {
                $rel = $r['rel'];
                if ($r['kind'] === 'external') {
                    $u = parse_url(str_starts_with($rel, '//') ? "http:$rel" : $rel);
                    if (($u['host'] ?? '') !== $host || empty($u['path'])) {
                        continue;
                    }
                    $rel = $u['path'];
                }
                if (in_array($r['kind'], ['local', 'external'], true)) {
                    $this->checkLocal($rel, $label, 0);
                }
            }
        }
        $this->feature = '-';
    }

    /* ================================================================== */
    /*  Laporan                                                            */
    /* ================================================================== */

    private function report(): void
    {
        usort($this->issues, fn ($a, $b) => [$a['feature'], $a['rel'], $a['line']] <=> [$b['feature'], $b['rel'], $b['line']]);

        $errors = count(array_filter($this->issues, fn ($i) => $i['level'] === 'ERROR'));
        $warnings = count($this->issues) - $errors;

        $dir = storage_path('logs/audit');
        File::ensureDirectoryExists($dir);
        File::put("$dir/assets.json", json_encode([
            'kind' => 'assets',
            'generated_at' => now()->toIso8601String(),
            'summary' => ['errors' => $errors, 'warnings' => $warnings],
            'issues' => array_map(fn ($i) => [
                'level' => $i['level'], 'feature' => $i['feature'], 'type' => $i['type'],
                'file' => $i['rel'], 'line' => $i['line'], 'message' => $i['message'],
            ], $this->issues),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

        $log = '[' . now()->toDateTimeString() . "] audit:assets - $errors error, $warnings warning\n";
        foreach ($this->issues as $i) {
            $log .= sprintf("  [%s] (%s) %s | %s:%d | %s\n", $i['level'], $i['feature'], $i['type'], $i['rel'], $i['line'], $i['message']);
        }
        File::put(storage_path('logs/audit-assets-' . now()->format('Y-m-d') . '.log'), $log);

        if (!$this->issues) {
            $this->info('Semua asset & JavaScript lolos pengecekan.');
            return;
        }

        $this->table(
            ['Level', 'Fitur', 'Jenis', 'File:Baris', 'Pesan'],
            array_map(fn ($i) => [$i['level'], $i['feature'], $i['type'], $i['rel'] . ':' . $i['line'], $i['message']], $this->issues)
        );

        $per = [];
        foreach ($this->issues as $i) {
            $per[$i['feature']][$i['level']] = ($per[$i['feature']][$i['level']] ?? 0) + 1;
        }
        $this->newLine();
        $this->table(['Fitur', 'Error', 'Warning'], array_map(
            fn ($f, $c) => [$f, $c['ERROR'] ?? 0, $c['WARNING'] ?? 0], array_keys($per), $per
        ));
        $this->error("Total: $errors error, $warnings warning");

        if ($errors > 0) {
            Log::error("audit:assets menemukan $errors error. Lihat storage/logs/audit-assets-" . now()->format('Y-m-d') . '.log');
        }
    }
}