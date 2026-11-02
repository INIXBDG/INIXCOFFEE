<?php

namespace App\Services;

use App\Models\ReportMlDictionary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ReportSmartScanner
{
    public const AUTO_THRESHOLD = 0.85;
    public const MIN_THRESHOLD = 0.5;  

    public function __construct(protected ReportGeneratorService $generator)
    {
    }

    public function scan(string $path, string $table, array $columns, array $reservedKeys = []): array
    {
        $blanks = $this->generator->scanBlankSpaces($path);
        $history = $this->loadHistory();
        $used = array_merge(array_keys($columns), $reservedKeys);

        $out = [];
        foreach ($blanks as $i => $b) {
            $out[] = $b + [
                'id' => 'b' . $i,
                'prediction' => $this->predict($b['context'], $columns, $history),
                'suggested_key' => $this->makeKey($b['context'], $used),
            ];
        }

        return $out;
    }

    public function predict(string $context, array $columns, $history = null): ?array
    {
        $norm = $this->normalize($context);
        if ($norm === '') {
            return null;
        }

        $history ??= $this->loadHistory();
        $best = null;

        foreach ($history as $row) {
            $score = $this->similarity($norm, (string) $row->context_keyword);
            if ($score < 0.75) {
                continue;
            }

            $cand = $this->rowToPrediction($row, $columns);
            if (!$cand) {
                continue;
            }

            $conf = $score >= 1.0
                ? min(1.0, 0.9 + min((int) $row->hit_count, 10) * 0.01)
                : $score * 0.9;

            if (!$best || $conf > $best['confidence']) {
                $best = $cand + ['confidence' => round($conf, 2), 'source' => 'history'];
            }
        }

        if ($best && $best['confidence'] >= self::MIN_THRESHOLD) {
            return $best;
        }

        return $this->heuristic($norm, $columns);
    }

    protected function rowToPrediction($row, array $columns): ?array
    {
        $type = $row->field_type ?: 'db';
        $val = $row->field_value;

        if ($type === 'db') {
            return isset($columns[$val]) ? ['type' => 'db', 'key' => $val, 'config' => null] : null;
        }

        $config = json_decode((string) $val, true);

        if (!is_array($config)) {
            // dukung baris lama yang punya kolom "config" terpisah
            $c = $row->getAttribute('config');
            $config = is_array($c) ? $c : (is_string($c) ? json_decode($c, true) : null);
        }

        return is_array($config) ? ['type' => $type, 'key' => null, 'config' => $config] : null;
    }

    protected function heuristic(string $norm, array $columns): ?array
    {
        if ($norm === 'nomor' || preg_match('/\bnomor (surat|keputusan|sk|dokumen|memo|perjanjian|kontrak)\b/u', $norm)) {
            return [
                'type' => 'formula',
                'key' => null,
                'config' => [
                    'template' => 'SK/{tahun}/{bulan_romawi}/{urutan:4}',
                    'last_number' => 0,
                    'label' => 'Nomor Surat',
                ],
                'confidence' => 0.6,
                'source' => 'heuristic',
            ];
        }

        if (in_array($norm, ['tanggal', 'tanggal surat', 'ditetapkan tanggal', 'dibuat tanggal', 'tanggal dibuat'], true)) {
            return [
                'type' => 'auto_date',
                'key' => null,
                'config' => [
                    'day_format' => 'number',
                    'month_format' => 'month_name',
                    'year_format' => 'number',
                    'separator' => ' ',
                    'label' => 'Tanggal Otomatis',
                ],
                'confidence' => 0.6,
                'source' => 'heuristic',
            ];
        }

        $syn = [
            'nip' => ['nip', 'nik', 'nomor induk karyawan', 'nomor induk pegawai'],
            'nik_karyawan' => ['nik', 'nik karyawan', 'nomor induk karyawan'],
            'nama_lengkap' => ['nama', 'nama lengkap', 'nama karyawan', 'nama pegawai', 'nama pelamar'],
            'alamat_lengkap' => ['alamat', 'alamat lengkap', 'alamat rumah'],
            'domisili' => ['domisili', 'alamat domisili'],
            'jabatan' => ['jabatan', 'posisi'],
            'divisi' => ['divisi', 'departemen', 'bagian'],
            'tempat_lahir' => ['tempat lahir'],
            'tanggal_lahir' => ['tanggal lahir'],
            'gender' => ['jenis kelamin'],
            'jenis_kelamin' => ['jenis kelamin'],
            'religion' => ['agama'],
            'email' => ['email', 'alamat email'],
            'no_telepon' => ['nomor telepon', 'nomor hp', 'nomor handphone', 'telepon'],
            'awal_kontrak' => ['tanggal awal kontrak', 'awal kontrak', 'mulai kontrak'],
            'akhir_kontrak' => ['tanggal akhir kontrak', 'akhir kontrak', 'selesai kontrak'],
            'awal_probation' => ['awal probation', 'mulai probation'],
            'akhir_probation' => ['akhir probation', 'selesai probation'],
            'tanggal_mulai_kerja' => ['tanggal mulai kerja', 'tanggal masuk kerja', 'tanggal masuk'],
        ];

        $best = null;
        foreach ($columns as $key => $label) {
            $cands = array_merge(
                [$this->normalize(str_replace('_', ' ', $key)), $this->normalize((string) $label)],
                array_map([$this, 'normalize'], $syn[$key] ?? [])
            );

            foreach (array_unique($cands) as $c) {
                if ($c === '') {
                    continue;
                }
                $score = 0;
                if ($norm === $c) {
                    $score = 0.88;
                } elseif (mb_strlen($c) >= 4 && (str_contains($norm, $c) || str_contains($c, $norm))) {
                    $score = 0.6;
                }
                if ($score > ($best['confidence'] ?? 0)) {
                    $best = ['type' => 'db', 'key' => $key, 'config' => null, 'confidence' => $score, 'source' => 'heuristic'];
                }
            }
        }

        return $best;
    }

    public function learn(array $items): void
    {
        if (!Schema::hasTable('report_ml_dictionaries')) {
            return;
        }

        foreach ($items as $it) {
            try {
                $norm = $this->normalize($it['context'] ?? '');
                if ($norm === '' || empty($it['type'])) {
                    continue;
                }

                $type = $it['type'];

                if ($type === 'db') {
                    $value = (string) $it['key'];
                } else {
                    $cfg = is_array($it['config'] ?? null) ? $it['config'] : [];
                    if ($type === 'formula') {
                        $cfg = [
                            'template' => $cfg['template'] ?? '',
                            'last_number' => 0,
                            'label' => $cfg['label'] ?? 'Rumus',
                        ];
                    }
                    $value = json_encode($cfg, JSON_UNESCAPED_UNICODE);
                }

                $row = ReportMlDictionary::firstOrNew(['context_keyword' => $norm]);
                $same = $row->exists && $row->field_type === $type && $row->field_value === $value;

                $row->field_type = $type;
                $row->field_value = $value;
                $row->hit_count = $same ? ((int) $row->hit_count + 1) : 1;
                $row->last_used_at = now();
                if (!$row->exists) {
                    $row->created_by = Auth::id();
                }
                $row->save();
            } catch (\Throwable $e) {
                Log::warning('ML learn gagal: ' . $e->getMessage());
            }
        }
    }

    public function normalize(string $s): string
    {
        $s = mb_strtolower($s, 'UTF-8');
        $s = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $s) ?? $s;
        $s = preg_replace('/\b(no|nmr|nomer)\b/u', 'nomor', $s) ?? $s;
        $s = preg_replace('/\b(tgl)\b/u', 'tanggal', $s) ?? $s;
        $s = preg_replace('/\s+/u', ' ', $s) ?? $s;

        return trim($s);
    }

    protected function similarity(string $a, string $b): float
    {
        if ($a === $b) {
            return 1.0;
        }
        if ($a === '' || $b === '') {
            return 0.0;
        }

        similar_text($a, $b, $pct);

        $ta = explode(' ', $a);
        $tb = explode(' ', $b);
        $union = count(array_unique(array_merge($ta, $tb)));
        $jacc = $union ? count(array_intersect($ta, $tb)) / $union : 0;

        return max($pct / 100, $jacc);
    }

    protected function loadHistory()
    {
        if (!Schema::hasTable('report_ml_dictionaries')) {
            return collect();
        }

        return ReportMlDictionary::orderByDesc('hit_count')->limit(500)->get();
    }

    protected function makeKey(string $context, array &$used): string
    {
        $base = Str::slug($context, '_');
        $base = $base !== '' ? mb_substr($base, 0, 40) : 'isian';
        if (preg_match('/^\d/', $base)) {
            $base = 'f_' . $base;
        }

        $key = $base;
        $i = 2;
        while (in_array($key, $used, true)) {
            $key = $base . '_' . $i++;
        }
        $used[] = $key;

        return $key;
    }
}