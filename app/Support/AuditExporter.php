<?php

namespace App\Support;

use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AuditExporter
{
    public const TABS = [
        'code' => 'Audit Code',
        'js' => 'Audit JS',
        'assets' => 'Audit Assets',
        'features' => 'Audit Fitur',
    ];

    private const GROUP = ['code' => 'Kategori', 'js' => 'Halaman', 'assets' => 'Fitur', 'features' => 'Fitur'];
    private const CATEGORY = ['model' => 'Model', 'controller' => 'Controller', 'other' => 'Lainnya'];
    private const LAYERS = [
        'blade' => 'Blade',
        'route' => 'Route',
        'controller' => 'Controller',
        'model' => 'Model & Database',
        'request' => 'Request',
    ];
    private const STATUS = ['ok' => 'Berhasil', 'error' => 'Gagal', 'warn' => 'Peringatan', 'skip' => 'Dilewati'];
    private const STATUS_LEVEL = ['ok' => 'OK', 'error' => 'ERROR', 'warn' => 'WARNING', 'skip' => 'SKIP'];
    private const COLORS = [
        'ERROR' => ['FEE2E2', 'DC2626'],
        'WARNING' => ['FEF3C7', 'B45309'],
        'OK' => ['DCFCE7', '15803D'],
        'SKIP' => ['E5E7EB', '6B7280'],
    ];

    public function build(array $payload, array $tabs, array $filters): array
    {
        $sections = [];

        foreach ($tabs as $tab) {
            $report = $payload[$tab] ?? null;
            $issues = array_values(array_filter(
                $report['issues'] ?? [],
                fn ($i) => $this->matches($i, $tab, $filters)
            ));
            usort($issues, fn ($a, $b) => $this->rank($a['level'] ?? '') <=> $this->rank($b['level'] ?? '')
                ?: strcmp((string) ($a['file'] ?? ''), (string) ($b['file'] ?? ''))
                ?: (($a['line'] ?? 0) <=> ($b['line'] ?? 0)));

            $summary = $report['summary'] ?? [];
            $summaryText = ($summary['errors'] ?? 0) . ' error, ' . ($tab === 'features'
                ? ($summary['ok'] ?? 0) . ' sukses'
                : ($summary['warnings'] ?? 0) . ' warning');

            $rows = [];
            $details = [];
            foreach ($issues as $i) {
                $rows[] = $this->row($i, $tab);
                if ($tab === 'features' && !empty($i['checks'])) {
                    $details[] = $this->detail($i);
                }
            }

            $sections[] = [
                'key' => $tab,
                'label' => self::TABS[$tab],
                'group_label' => self::GROUP[$tab],
                'generated_at' => !empty($report['generated_at'])
                    ? date('d/m/Y H:i:s', strtotime($report['generated_at']))
                    : '-',
                'summary_error' => $summary['errors'] ?? 0,
                'summary_other' => $tab === 'features' ? ($summary['ok'] ?? 0) : ($summary['warnings'] ?? 0),
                'summary' => $summaryText,
                'filters' => $this->describeFilters($tab, $filters),
                'rows' => $rows,
                'details' => $details,
            ];
        }

        return $sections;
    }

    public function xlsx(array $sections): Spreadsheet
    {
        $book = new Spreadsheet();
        $book->removeSheetByIndex(0);

        if (count($sections) > 1) {
            $sheet = $book->createSheet();
            $sheet->setTitle('Ringkasan');
            $rows = [];
            foreach ($sections as $s) {
                $rows[] = [
                    $s['label'],
                    $s['generated_at'],
                    (string) $s['summary_error'],
                    (string) $s['summary_other'] . ($s['key'] === 'features' ? ' sukses' : ' warning'),
                    (string) count($s['rows']),
                ];
            }
            $this->fill($sheet, ['Tab', 'Terakhir disinkronkan', 'Error', 'Warning / Sukses', 'Diekspor'], $rows, [], [22, 24, 10, 20, 12]);
        }

        foreach ($sections as $s) {
            $sheet = $book->createSheet();
            $sheet->setTitle($s['label']);

            $headers = ['Level', $s['group_label'], 'Jenis', 'File:Baris', 'Pesan'];
            $widths = [10, 18, 26, 46, 70];
            $rows = [];
            $levels = [];

            foreach ($s['rows'] as $r) {
                $line = [$r['level'], $r['group'], $r['type'], $r['location'], $r['message']];
                if ($s['key'] === 'features') {
                    array_push($line, $r['url'], $r['route'], $r['controller'], $r['blade'], $r['model'], $r['table'], $r['checks']);
                }
                $rows[] = $line;
                $levels[] = $r['level'];
            }

            if ($s['key'] === 'features') {
                array_push($headers, 'URL', 'Route', 'Controller', 'Blade', 'Model', 'Tabel', 'Ringkasan Pemeriksaan');
                array_push($widths, 36, 30, 40, 36, 24, 24, 34);
            }

            $this->fill($sheet, $headers, $rows, $levels, $widths);

            if ($s['key'] === 'features') {
                $detail = $book->createSheet();
                $detail->setTitle('Detail Fitur');
                $dRows = [];
                $dLevels = [];
                foreach ($s['details'] as $d) {
                    foreach ($d['checks'] as $c) {
                        $dRows[] = [
                            $d['feature'], $d['function'], $d['url'],
                            $c['layer'], $c['status'], $c['title'], $c['detail'], $c['location'],
                        ];
                        $dLevels[] = $c['level'];
                    }
                }
                $this->fill(
                    $detail,
                    ['Fitur', 'Fungsi', 'URL', 'Lapisan', 'Status', 'Pemeriksaan', 'Detail', 'File:Baris'],
                    $dRows,
                    $dLevels,
                    [22, 26, 36, 18, 12, 40, 70, 46],
                    4
                );
            }
        }

        $book->setActiveSheetIndex(0);

        return $book;
    }

    public function pdf(array $sections)
    {
        return Pdf::loadView('audit.export-pdf', [
            'sections' => $sections,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');
    }

    private function fill($sheet, array $headers, array $rows, array $levels, array $widths, ?int $levelCol = 0): void
    {
        if (isset($levels[0]) && is_int($levels[0]) && $widths === []) {
            $widths = $levels;
            $levels = [];
        }

        foreach ($headers as $c => $h) {
            $sheet->setCellValueExplicit([$c + 1, 1], $h, DataType::TYPE_STRING);
        }
        foreach ($rows as $r => $row) {
            foreach ($row as $c => $v) {
                $sheet->setCellValueExplicit([$c + 1, $r + 2], mb_substr((string) $v, 0, 32000), DataType::TYPE_STRING);
            }
        }

        $last = Coordinate::stringFromColumnIndex(count($headers));
        $end = max(2, count($rows) + 1);

        $sheet->getStyle("A1:{$last}{$end}")->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);

        $sheet->getStyle("A1:{$last}1")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2563EB']],
        ]);

        foreach ($widths as $c => $w) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c + 1))->setWidth($w);
        }

        foreach ($levels as $r => $level) {
            if (!isset(self::COLORS[$level])) {
                continue;
            }
            $col = Coordinate::stringFromColumnIndex(($levelCol ?? 0) + 1);
            $sheet->getStyle($col . ($r + 2))->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => self::COLORS[$level][1]]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => self::COLORS[$level][0]]],
            ]);
        }

        $sheet->freezePane('A2');
        if ($rows) {
            $sheet->setAutoFilter("A1:{$last}{$end}");
        }
    }

    private function row(array $i, string $tab): array
    {
        $type = (string) ($i['type'] ?? '-');
        $fn = !empty($i['function']) ? (string) $i['function'] : '';
        $typeLabel = $tab === 'features' && $fn !== '' ? str_replace('_', ' ', $fn) . ' · ' . $type : $type;

        $location = !empty($i['file'])
            ? $i['file'] . (!empty($i['line']) ? ':' . $i['line'] : '')
            : ($i['url'] ?? '-');

        $counts = ['error' => 0, 'warn' => 0, 'ok' => 0, 'skip' => 0];
        foreach ($i['checks'] ?? [] as $c) {
            $counts[$c['status'] ?? 'skip'] = ($counts[$c['status'] ?? 'skip'] ?? 0) + 1;
        }

        return [
            'level' => (string) ($i['level'] ?? ''),
            'group' => $tab === 'code'
                ? (self::CATEGORY[$i['category'] ?? 'other'] ?? self::CATEGORY['other'])
                : (string) ($i['feature'] ?? '-'),
            'type' => $typeLabel,
            'location' => (string) $location,
            'message' => (string) ($i['message'] ?? ''),
            'url' => (string) ($i['url'] ?? ''),
            'route' => (string) ($i['route'] ?? $i['uri'] ?? ''),
            'controller' => (string) ($i['controller'] ?? ''),
            'blade' => (string) ($i['blade'] ?? ''),
            'model' => (string) ($i['model'] ?? ''),
            'table' => (string) ($i['table'] ?? ''),
            'checks' => $i['checks'] ?? null
                ? "Gagal {$counts['error']} · Peringatan {$counts['warn']} · Berhasil {$counts['ok']} · Dilewati {$counts['skip']}"
                : '',
        ];
    }

    private function detail(array $i): array
    {
        $parts = [];
        foreach ([
            'Blade' => $i['blade'] ?? null,
            'Route' => $i['route'] ?? $i['uri'] ?? null,
            'Controller' => $i['controller'] ?? null,
            'Model' => !empty($i['model'])
                ? $i['model'] . (!empty($i['table']) ? ' (tabel ' . $i['table'] . ')' : '')
                : ($i['table'] ?? null),
        ] as $label => $value) {
            if ($value) {
                $parts[] = "$label: $value";
            }
        }

        $checks = [];
        foreach ($i['checks'] as $c) {
            $status = $c['status'] ?? 'skip';
            $checks[] = [
                'layer' => self::LAYERS[$c['layer'] ?? ''] ?? (string) ($c['layer'] ?? '-'),
                'status' => self::STATUS[$status] ?? $status,
                'level' => self::STATUS_LEVEL[$status] ?? 'SKIP',
                'title' => (string) ($c['title'] ?? ''),
                'detail' => (string) ($c['detail'] ?? ''),
                'location' => !empty($c['file']) ? $c['file'] . (!empty($c['line']) ? ':' . $c['line'] : '') : '',
            ];
        }

        return [
            'feature' => (string) ($i['feature'] ?? '-'),
            'function' => str_replace('_', ' ', (string) ($i['function'] ?? '')),
            'url' => (string) ($i['url'] ?? ''),
            'chain' => implode('  →  ', $parts),
            'checks' => $checks,
        ];
    }

    private function matches(array $i, string $tab, array $f): bool
    {
        $level = $f['level'] ?? 'all';
        if ($level !== 'all' && ($i['level'] ?? '') !== $level) {
            return false;
        }
        if ($tab === 'code') {
            $cat = $f['category'] ?? 'all';
            if ($cat !== 'all' && ($i['category'] ?? 'other') !== $cat) {
                return false;
            }
        } else {
            $feature = $f['feature'] ?? 'all';
            if ($feature !== 'all' && ($i['feature'] ?? '-') !== $feature) {
                return false;
            }
        }
        $q = mb_strtolower(trim((string) ($f['query'] ?? '')));
        if ($q !== '') {
            $haystack = mb_strtolower(implode(' ', [
                $i['file'] ?? '', $i['type'] ?? '', $i['message'] ?? '', $i['feature'] ?? '', $i['function'] ?? '',
            ]));
            if (!str_contains($haystack, $q)) {
                return false;
            }
        }

        return true;
    }

    private function describeFilters(string $tab, array $f): string
    {
        $parts = [];
        if (($f['level'] ?? 'all') !== 'all') {
            $parts[] = 'Level: ' . $f['level'];
        }
        if ($tab === 'code' && ($f['category'] ?? 'all') !== 'all') {
            $parts[] = 'Kategori: ' . (self::CATEGORY[$f['category']] ?? $f['category']);
        }
        if ($tab !== 'code' && ($f['feature'] ?? 'all') !== 'all') {
            $parts[] = self::GROUP[$tab] . ': ' . $f['feature'];
        }
        if (trim((string) ($f['query'] ?? '')) !== '') {
            $parts[] = 'Pencarian: ' . trim($f['query']);
        }

        return $parts ? implode(' | ', $parts) : 'Semua data';
    }

    private function rank(string $level): int
    {
        return $level === 'ERROR' ? 0 : ($level === 'WARNING' ? 1 : 2);
    }
}