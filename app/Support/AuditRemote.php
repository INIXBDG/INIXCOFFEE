<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

/**
 * Letakkan di: app/Support/AuditRemote.php  (di komputer lokal DAN di server)
 */
class AuditRemote
{
    /** Alamat server bila mode remote aktif, null bila audit berjalan lokal. */
    public static function base(): ?string
    {
        $mode = strtolower((string) config('audit.request_mode', 'auto'));
        $base = rtrim((string) config('audit.base_url', ''), '/');
        if ($mode === 'kernel' || $base === '') {
            return null;
        }
        if ($mode === 'http') {
            return $base;
        }
        $a = (string) parse_url($base, PHP_URL_HOST);
        $b = (string) parse_url((string) config('app.url'), PHP_URL_HOST);

        return strcasecmp($a, $b) !== 0 ? $base : null;
    }

    public static function enabled(): bool
    {
        return self::base() !== null;
    }

    /** Minta server menjalankan audit:code, lalu kembalikan isi code.json-nya. */
    public static function fetchCode(array $files = []): array
    {
        $base = self::base();
        $token = (string) config('audit.remote_token');

        if ($base === null) {
            throw new RuntimeException('Mode remote tidak aktif.');
        }
        if ($token === '') {
            throw new RuntimeException('AUDIT_REMOTE_TOKEN belum diisi di .env (nilainya harus sama dengan di server).');
        }

        try {
            $res = Http::withHeaders(['X-Audit-Token' => $token, 'Accept' => 'application/json'])
                ->withOptions(['verify' => filter_var(config('audit.verify_ssl', true), FILTER_VALIDATE_BOOLEAN)])
                ->connectTimeout(15)
                ->timeout(600)
                ->get($base . '/audit/remote/code', $files ? ['files' => array_values($files)] : []);
        } catch (Throwable $e) {
            throw new RuntimeException('Server tidak bisa dihubungi: ' . mb_substr($e->getMessage(), 0, 200));
        }

        if ($res->status() === 404) {
            throw new RuntimeException('Server menjawab 404: endpoint belum dipasang di server, token tidak sama, atau env server bukan local/staging (dan AUDIT_REMOTE_ENABLED belum true).');
        }
        if (!$res->successful()) {
            throw new RuntimeException("Server menjawab {$res->status()}: " . mb_substr(trim(strip_tags($res->body())), 0, 300));
        }

        $json = $res->json();
        if (!is_array($json) || ($json['kind'] ?? '') !== 'code') {
            throw new RuntimeException('Respons server bukan hasil audit code yang valid.');
        }
        $json['source'] = $base;

        return $json;
    }
}