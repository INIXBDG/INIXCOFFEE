<?php

namespace App\Support\Concerns;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

trait ProjectAwareSolutions
{
    private ?array $srcIdx = null;

    /* ------------------------------------------------------------------ */
    /*  Indeks & pencarian pemakaian di seluruh project                    */
    /* ------------------------------------------------------------------ */

    private function relBase(string $abs): string
    {
        $base = rtrim(str_replace('\\', '/', base_path()), '/') . '/';
        $abs = str_replace('\\', '/', $abs);

        return str_starts_with($abs, $base) ? substr($abs, strlen($base)) : ltrim($abs, '/');
    }

    private function kindOf(string $rel): string
    {
        return match (true) {
            str_starts_with($rel, 'database/migrations/') => 'migration',
            str_starts_with($rel, 'resources/views/') => 'blade',
            str_ends_with($rel, '.js'), str_ends_with($rel, '.mjs'), str_ends_with($rel, '.vue') => 'js',
            str_starts_with($rel, 'app/Http/Controllers/') => 'controller',
            str_starts_with($rel, 'app/Models/') => 'model',
            str_starts_with($rel, 'routes/') => 'route',
            default => 'other',
        };
    }

    /** [rel => baris[]] untuk file sumber project (migration dulu agar tidak terpotong batas). */
    private function sourceIndex(): array
    {
        if ($this->srcIdx !== null) {
            return $this->srcIdx;
        }
        $this->srcIdx = [];
        $n = 0;
        foreach (['database/migrations', 'app', 'resources/views', 'resources/js', 'routes', 'public/js'] as $d) {
            if (!is_dir(base_path($d))) {
                continue;
            }
            try {
                foreach (File::allFiles(base_path($d)) as $f) {
                    $rel = $this->relBase($f->getPathname());
                    if (++$n > 4000 || $f->getSize() > 400000
                        || !in_array($f->getExtension(), ['php', 'js', 'mjs', 'vue'], true)
                        || preg_match('#(\.min\.|/vendor/|/plugins?/|/libs?/|/build/|/app/Support/Audit|/app/Console/Commands/Audit|AuditController|/resources/views/audit/)#i', '/' . $rel)) {
                        continue;
                    }
                    $this->srcIdx[$rel] = file($f->getPathname(), FILE_IGNORE_NEW_LINES) ?: [];
                }
            } catch (Throwable $e) {
            }
        }

        return $this->srcIdx;
    }

    /** Cari nama (kolom/route/id/dll) yang ditulis sebagai string, ->properti, atau name="..." */
    private function usages(string $word, int $limit = 12): array
    {
        if (strlen($word) < 2) {
            return [];
        }
        $q = preg_quote($word, '/');
        $re = '/([\'"])' . $q . '\1|->' . $q . '\b|name\s*=\s*[\'"]' . $q . '(?:\[\])?[\'"]/';
        $out = [];
        foreach ($this->sourceIndex() as $rel => $lines) {
            foreach ($lines as $i => $text) {
                if (preg_match($re, $text)) {
                    $out[] = ['file' => $rel, 'line' => $i + 1, 'kind' => $this->kindOf($rel), 'text' => trim(mb_substr($text, 0, 140))];
                    if (count($out) >= $limit * 4) {
                        break 2;
                    }
                }
            }
        }

        return $out;
    }

    /** Cari baris yang memuat 'kata' dalam kutip; bila $after diberi, mulai dari baris yang cocok dengan regex itu. */
    private function findLine(?string $rel, string $word, ?string $after = null): ?array
    {
        if (!$rel || !is_file(base_path($rel))) {
            return null;
        }
        $lines = file(base_path($rel), FILE_IGNORE_NEW_LINES) ?: [];
        $start = 0;
        if ($after) {
            foreach ($lines as $i => $t) {
                if (preg_match($after, $t)) {
                    $start = $i;
                    break;
                }
            }
        }
        for ($i = $start; $i < count($lines); $i++) {
            if (preg_match('/([\'"])' . preg_quote($word, '/') . '\1/', $lines[$i])) {
                return [$i + 1, $lines[$i]];
            }
        }

        return null;
    }

    private function fqFromFile(?string $rel): ?string
    {
        if (!$rel || !str_starts_with($rel, 'app/') || !str_ends_with($rel, '.php')) {
            return null;
        }

        return 'App\\' . str_replace('/', '\\', substr($rel, 4, -4));
    }

    /* ------------------------------------------------------------------ */
    /*  Deteksi salah ketik                                                */
    /* ------------------------------------------------------------------ */

    /** Salah ketik yang ketat (selisih 1-2 huruf, abaikan underscore & huruf besar). */
    private function typoOf(string $word, array $hay): ?string
    {
        $norm = fn (string $s) => str_replace(['_', '-', ' '], '', strtolower($s));
        $w = $norm($word);
        $limit = strlen($w) <= 3 ? 0 : (strlen($w) <= 6 ? 1 : 2);
        $best = null;
        $bestD = 99;
        foreach (array_unique($hay) as $h) {
            if ($h === $word) {
                continue;
            }
            $d = levenshtein($w, $norm((string) $h));
            if ($d <= $limit && $d < $bestD) {
                $best = $h;
                $bestD = $d;
            }
        }

        return $best;
    }

    /** Pengganti closest(): similar_text + levenshtein, lebih peka pada salah ketik. */
    private function closest(string $needle, array $hay, int $n = 3): array
    {
        $needle = strtolower($needle);
        $scored = [];
        foreach (array_unique($hay) as $h) {
            $l = strtolower((string) $h);
            if ($l === $needle) {
                continue;
            }
            similar_text($needle, $l, $pct);
            $lev = (strlen($needle) < 200 && strlen($l) < 200) ? levenshtein($needle, $l) : 99;
            $near = strlen($needle) >= 4 && $lev <= max(1, (int) floor(strlen($needle) / 5));
            if ($pct >= 55 || $near) {
                $scored[] = [$h, $near ? max($pct, 90 - $lev) : $pct];
            }
        }
        usort($scored, fn ($a, $b) => $b[1] <=> $a[1]);

        return array_slice(array_column($scored, 0), 0, $n);
    }

    /* ------------------------------------------------------------------ */
    /*  Pembuat kode migration yang relevan                                */
    /* ------------------------------------------------------------------ */

    private function guessColumn(string $c): string
    {
        return match (true) {
            str_ends_with($c, '_id') => "\$table->unsignedBigInteger('$c')->nullable();",
            (bool) preg_match('/^(is|has|can)_/', $c) => "\$table->boolean('$c')->default(false);",
            str_ends_with($c, '_at') => "\$table->timestamp('$c')->nullable();",
            (bool) preg_match('/(tanggal|tgl|date)/i', $c) => "\$table->date('$c')->nullable();",
            (bool) preg_match('/(harga|total|biaya|nominal|saldo|price|amount)/i', $c) => "\$table->decimal('$c', 15, 2)->default(0);",
            (bool) preg_match('/(qty|stok|stock|jumlah|urut|umur|usia|count)/i', $c) => "\$table->integer('$c')->default(0);",
            (bool) preg_match('/(keterangan|deskripsi|alamat|catatan|isi|description|note|content)/i', $c) => "\$table->text('$c')->nullable();",
            default => "\$table->string('$c')->nullable();",
        };
    }

    private function addColumnsMigration(string $table, array $cols): string
    {
        $up = '';
        foreach ($cols as $col) {
            $up .= '    ' . $this->guessColumn($col) . "\n";
        }
        $drop = "'" . implode("', '", $cols) . "'";

        return "// up()\nSchema::table('$table', function (Blueprint \$table) {\n$up});\n\n"
            . "// down()\nSchema::table('$table', function (Blueprint \$table) {\n    \$table->dropColumn([$drop]);\n});";
    }

    /* ------------------------------------------------------------------ */
    /*  Solusi: kolom $fillable tidak ada di tabel                         */
    /* ------------------------------------------------------------------ */

    private function rFillableCols(array $c, array $m): array
    {
        $table = $this->q($c['message'])[0] ?? '';
        $cols = preg_match('/kolom:\s*(.+)$/', $c['message'], $mm)
            ? array_values(array_filter(array_map('trim', explode(',', $mm[1]))))
            : [];
        $have = $this->columns($table);
        $file = $c['file'];

        $detail = [];
        $steps = [];
        $add = [];
        $kinds = [];
        $diff = null;

        foreach ($cols as $col) {
            $hit = $this->findLine($file, $col, '/\$fillable\b/');
            $at = $hit ? "baris {$hit[0]} di $file" : 'di $fillable model';
            $typo = $this->typoOf($col, $have);

            // 1) salah ketik -> perbaiki menjadi kata yang benar
            if ($typo) {
                $dup = $this->findLine($file, $typo, '/\$fillable\b/');
                if ($dup && (!$hit || $dup[0] !== $hit[0])) {
                    $detail[] = "'$col' tidak ada di tabel '$table'. Kolom mirip '$typo' sudah terdaftar di \$fillable (baris {$dup[0]}), jadi '$col' kemungkinan entri ganda hasil salah ketik.";
                    $steps[] = "Hapus entri '$col' ($at) karena '$typo' sudah ada.";
                    $diff ??= $hit ? ['before' => trim($hit[1]), 'after' => '(hapus entri ini)'] : null;
                    $kinds['hapus'] = true;
                } else {
                    $detail[] = "'$col' tidak ada di tabel '$table', tetapi kolom '$typo' ada. Hampir pasti salah ketik.";
                    $steps[] = "Ganti '$col' menjadi '$typo' ($at). Cek juga name=\"$col\" di Blade dan aturan validasi di controller agar ikut diganti.";
                    $diff ??= $hit ? $this->diff($hit[1], $col, $typo) : null;
                    $kinds['typo'] = true;
                }
                continue;
            }

            // 2) bukan salah ketik -> lihat apakah kolom dipakai di tempat lain
            $use = array_values(array_filter($this->usages($col), function ($u) use ($table) {
                if ($u['kind'] === 'model') {
                    return false;
                }
                if ($u['kind'] === 'migration') {
                    return str_contains(implode("\n", $this->sourceIndex()[$u['file']] ?? []), $table);
                }

                return true;
            }));
            $mig = array_values(array_filter($use, fn ($u) => $u['kind'] === 'migration'));
            $app = array_values(array_filter($use, fn ($u) => $u['kind'] !== 'migration'));
            $where = implode(', ', array_map(fn ($u) => $u['file'] . ':' . $u['line'], array_slice($app, 0, 4)));

            if ($mig) {
                $detail[] = "'$col' ditulis di migration {$mig[0]['file']}:{$mig[0]['line']}, tetapi kolomnya belum ada di tabel '$table' pada database aktif. Migration kemungkinan belum dijalankan (atau dijalankan di database lain).";
                $steps[] = 'Jalankan php artisan migrate:status lalu php artisan migrate. Pastikan DB_DATABASE di .env sama dengan database yang diaudit.';
                $kinds['migrate'] = true;
            } elseif ($app) {
                $detail[] = "'$col' dipakai di $where, tetapi tidak ada migration yang membuatnya, sehingga kolom tidak ada di tabel '$table'. Kode Anda membutuhkan kolom ini, jadi jangan dihapus dari \$fillable.";
                $steps[] = "Buat migration untuk kolom '$col' (kode di bawah), lalu php artisan migrate.";
                $add[] = $col;
                $kinds['tambah'] = true;
            } else {
                $detail[] = "'$col' hanya muncul di model: tidak ada migration, Blade, JS, atau controller yang memakainya. Aman dihapus dari \$fillable.";
                $steps[] = "Hapus '$col' dari \$fillable ($at).";
                $diff ??= $hit ? ['before' => trim($hit[1]), 'after' => '(hapus entri ini)'] : null;
                $kinds['hapus'] = true;
            }
        }

        if ($add) {
            array_unshift($steps, "Buat file migration: php artisan make:migration add_" . implode('_', array_slice($add, 0, 2)) . "_to_{$table}_table --table=$table");
        }

        $title = count($kinds) > 1 ? 'Perbaiki $fillable sesuai kondisi tiap kolom'
            : (isset($kinds['typo']) ? 'Perbaiki salah ketik di $fillable'
            : (isset($kinds['tambah']) ? 'Tambahkan kolom lewat migration'
            : (isset($kinds['migrate']) ? 'Jalankan migration yang tertunda' : 'Hapus kolom yatim dari $fillable')));

        return $this->sol(
            'Kolom di $fillable tidak ada di tabel',
            "Tabel '$table' tidak punya kolom: " . implode(', ', $cols) . ".\nHasil pengecekan terhadap project Anda:\n- " . implode("\n- ", $detail),
            'Daftar $fillable berisi nama kolom yang tidak ada di struktur tabel. Tiap kolom saya cocokkan dengan kolom tabel yang ada (untuk mendeteksi salah ketik), lalu saya cari pemakaiannya di migration, Blade, JS, dan controller untuk memutuskan: diperbaiki, ditambahkan, atau dihapus.',
            'INSERT/UPDATE yang membawa kolom ini gagal dengan error SQL "Unknown column" (500).',
            $title,
            $steps,
            $add ? $this->addColumnsMigration($table, $add) : null,
            [
                $this->ex('Kolom yang ada di tabel sekarang', $have ? implode(', ', $have) : 'Tidak dapat membaca kolom tabel.'),
                $this->ex('Setelah diperbaiki', 'Klik "Cek ulang" pada temuan ini; audit akan membaca ulang $fillable dan struktur tabel.'),
            ],
            $diff
        );
    }

    /* ------------------------------------------------------------------ */
    /*  Solusi: tabel database tidak ada                                   */
    /* ------------------------------------------------------------------ */

    private function rTableMissing(array $c, array $m): array
    {
        $table = $this->q($c['message'])[0] ?? '';
        $tables = $this->tables();
        $typo = $this->typoOf($table, $tables);
        $line = $this->findLine($c['file'], $table);
        $creates = array_values(array_filter(
            $this->usages($table),
            fn ($u) => $u['kind'] === 'migration'
                && preg_match('/Schema::create\(\s*[\'"]' . preg_quote($table, '/') . '[\'"]/', implode("\n", $this->sourceIndex()[$u['file']] ?? []))
        ));

        if ($creates) {
            return $this->sol(
                'Tabel ada di migration tetapi belum dibuat di database',
                "Tabel '$table' dibuat oleh {$creates[0]['file']}:{$creates[0]['line']}, tetapi tidak ada di database aktif.",
                'Migration sudah ditulis tetapi belum dijalankan, atau dijalankan pada database lain dari yang dipakai aplikasi sekarang.',
                'Query ke tabel ini akan error "Table not found" (500).',
                'Jalankan migration yang tertunda',
                ['php artisan migrate:status untuk melihat migration yang Pending.', 'php artisan migrate untuk menjalankannya.', 'Bila migration sudah "Ran" tetapi tabel tetap tidak ada, periksa DB_DATABASE/DB_CONNECTION di .env lalu php artisan config:clear.'],
                "php artisan migrate:status\nphp artisan migrate",
                [$this->ex('Tabel yang ada di database', $tables ? implode(', ', array_slice($tables, 0, 30)) : 'Tidak dapat membaca daftar tabel.')]
            );
        }

        if ($typo) {
            $code = $line ? null : "protected \$table = '$typo';";
            return $this->sol(
                'Nama tabel salah ketik',
                "Tabel '$table' tidak ada, tetapi tabel '$typo' ada. Kemungkinan besar maksudnya '$typo'."
                    . ($line ? " Ditulis di baris {$line[0]} {$c['file']}." : ' Model tidak punya properti $table, jadi nama tabel dibentuk Laravel dari nama class (jamak, huruf kecil); bisa jadi nama class modelnya yang salah ketik.'),
                'Nama tabel yang dipakai tidak cocok dengan tabel yang benar-benar ada di database.',
                'Query ke tabel ini akan error "Table not found" (500).',
                "Arahkan ke tabel '$typo'",
                $line
                    ? ["Ganti '$table' menjadi '$typo' di baris {$line[0]}."]
                    : ["Tambahkan protected \$table = '$typo'; di model, atau ganti nama class model agar sesuai tabel."],
                $code,
                [$this->ex('Cari penulisan lain', "Cari '$table' di seluruh project agar semua salah ketik yang sama ikut diperbaiki.")],
                $line ? $this->diff($line[1], $table, $typo) : null
            );
        }

        // Tidak ada migration & tidak ada tabel mirip -> buatkan migration dari $fillable model
        $cols = [];
        $fq = $this->fqFromFile($c['file']);
        if ($fq && class_exists($fq)) {
            try {
                $cols = (new $fq())->getFillable();
            } catch (Throwable $e) {
            }
        }
        $body = '';
        foreach ($cols as $col) {
            $body .= '    ' . $this->guessColumn($col) . "\n";
        }
        $code = "Schema::create('$table', function (Blueprint \$table) {\n    \$table->id();\n$body    \$table->timestamps();\n});";
        $uses = array_slice(array_filter($this->usages($table), fn ($u) => $u['kind'] !== 'migration'), 0, 3);

        return $this->sol(
            'Tabel belum punya migration',
            "Tabel '$table' dipakai tetapi tidak ada di database, tidak ada migration Schema::create('$table'), dan tidak ada tabel dengan nama mirip."
                . ($cols ? "\nKolom pada kode di bawah diambil dari \$fillable model: " . implode(', ', $cols) . '.' : '')
                . ($uses ? "\nDipakai di: " . implode(', ', array_map(fn ($u) => $u['file'] . ':' . $u['line'], $uses)) . '.' : ''),
            'Tabel tidak pernah dibuat lewat migration, jadi tidak ada di database.',
            'Query ke tabel ini akan error "Table not found" (500).',
            'Buat migration untuk tabel ini',
            ["php artisan make:migration create_{$table}_table", 'Isi method up() dengan kode di bawah; sesuaikan tipe kolom (tipe ditebak dari nama kolom).', 'Jalankan php artisan migrate.'],
            $code,
            [$this->ex('Cek tipe kolom', 'Tipe kolom pada kode ditebak dari namanya (mis. *_id, tanggal, harga). Periksa lagi sebelum migrate.')]
        );
    }

    /* ------------------------------------------------------------------ */
    /*  Solusi: class tidak ditemukan (cabang akhir rClassMissing)         */
    /* ------------------------------------------------------------------ */

    private function classMissingSmart(array $c, string $base): array
    {
        $line = $this->lineText($c) ?? '';
        $typo = $this->typoOf($base, $this->classNames());

        if ($typo) {
            $fq = $this->classIndex()[strtolower($typo)][0] ?? $typo;
            return $this->sol(
                "Class '$base' salah ketik",
                "'$base' tidak ada di app/, tetapi class '$typo' ada di $fq. Hampir pasti maksudnya '$typo'.",
                'Nama class yang ditulis tidak cocok dengan class manapun di project.',
                "Pemanggilan '$base' akan menghasilkan \"Class not found\" fatal error.",
                "Ganti '$base' menjadi '$typo'",
                ["Ubah penulisan di baris {$c['line']} menjadi '$typo'.", "Pastikan ada import: use $fq;", 'Cari penulisan salah yang sama di file lain.'],
                "use $fq;",
                [],
                $this->diff($line, $base, $typo)
            );
        }

        $q = preg_quote($base, '/');
        $isModel = (bool) preg_match('/\b' . $q . '::(where|find|findOrFail|first|all|create|query|with|latest|orderBy|paginate|count|pluck|select|firstOrCreate|updateOrCreate)\b/', $line);
        [$kind, $fq] = match (true) {
            $isModel => ['model', 'App\\Models\\' . $base],
            str_ends_with($base, 'Controller') => ['controller', 'App\\Http\\Controllers\\' . $base],
            str_ends_with($base, 'Request') => ['form request', 'App\\Http\\Requests\\' . $base],
            str_ends_with($base, 'Export') => ['export', 'App\\Exports\\' . $base],
            default => ['class', 'App\\Services\\' . $base],
        };

        $steps = [
            "Tidak ada class bernama mirip di app/, jadi ini bukan salah ketik. Dari baris {$c['line']} ('" . trim($line) . "') class ini dipakai sebagai $kind.",
            "Buat dengan: " . $this->makeCmd($fq),
        ];
        if ($isModel) {
            $steps[] = 'Model butuh tabel: php artisan make:migration create_' . Str::snake(Str::plural($base)) . '_table, isi kolom, lalu php artisan migrate.';
            $steps[] = 'Isi $fillable di model sesuai kolom yang dipakai form.';
        }
        $steps[] = "Tambahkan use $fq; di file ini bila namespace-nya berbeda.";

        return $this->sol(
            "Class '$base' belum ada",
            "'$base' tidak di-import, tidak ada di app/, dan tidak ada nama yang mirip.",
            'Class belum pernah dibuat. Setelah dicek, bukan karena salah ketik maupun lupa import.',
            "Setiap pemanggilan '$base' gagal dengan \"Class not found\" fatal error.",
            "Buat $kind '$base'",
            $steps,
            $this->makeCmd($fq),
            [$this->ex('Jika dari package', "Bila '$base' berasal dari package, pasang dengan composer require lalu import namespace yang benar.")]
        );
    }

    /* ------------------------------------------------------------------ */
    /*  Extras: hanya yang relevan dengan project (tanpa boilerplate)      */
    /* ------------------------------------------------------------------ */

    private function fillExtras(array $c, array $extras): array
    {
        $tok = ltrim($this->q($c['message'])[0] ?? '', '\\');
        if (strlen($tok) > 2) {
            $hits = array_values(array_filter(
                $this->usages($tok, 8),
                fn ($u) => !($u['file'] === $c['file'] && $u['line'] === $c['line'])
            ));
            if ($hits) {
                $extras[] = $this->ex(
                    "Tempat lain yang menulis '$tok'",
                    implode("\n", array_map(fn ($u) => "{$u['file']}:{$u['line']}  {$u['text']}", array_slice($hits, 0, 6)))
                        . "\nJika '$tok' memang salah ketik, perbaiki di semua tempat ini sekaligus."
                );
            }
        }

        $seen = [];
        $out = [];
        foreach ($extras as $e) {
            if (isset($seen[$e['title']])) {
                continue;
            }
            $seen[$e['title']] = true;
            $out[] = $e;
        }

        return array_slice($out, 0, 6);
    }
}