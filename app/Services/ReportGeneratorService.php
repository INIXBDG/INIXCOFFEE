<?php

namespace App\Services;

use App\Models\ReportTemplate;
use App\Models\TemplatePlaceholder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportGeneratorService
{
    protected bool $previewMode = false;

    protected array $allowedModels = [
        'karyawan' => \App\Models\karyawan::class,
        'pelamar' => \App\Models\Pelamar::class,
    ];

    protected array $allowedFieldTypes = [
        'text', 'textarea', 'date', 'select', 'checkbox', 'number', 'currency',
        'auto_date', 'formula', 'auth_field', 'relation_single',
        'loop_manual', 'loop_relation',
        'manual_text', 'manual_textarea', 'manual_date', 'manual_number', 'manual_select', 'manual_checkbox',
    ];

    public function scanBlankSpaces(string $filePath): array
    {
        $unique = [];

        foreach ($this->extractParagraphsFromDocx($filePath) as $paragraph) {
            $found = preg_match_all(
                '/(?<![\p{L}\p{N}])([^\r\n_]{2,80}?)\s*([:=]?)\s*(_{3,}|\.{5,}|…{2,})/u',
                $paragraph,
                $matches,
                PREG_SET_ORDER
            );

            if (!$found) {
                continue;
            }

            foreach ($matches as $m) {
                $original = trim($m[0]);
                $context = $this->cleanContext($m[1]);

                if ($context === '' || isset($unique[$original])) {
                    continue;
                }

                $unique[$original] = [
                    'original_text' => $original,
                    'context' => $context,
                    'punctuation' => $m[2],
                    'blank_part' => $m[3],
                ];
            }
        }

        return array_values($unique);
    }

    private function cleanContext(string $raw): string
    {
        $context = preg_replace('/\s+/u', ' ', $raw) ?? $raw;
        $context = preg_replace('/^[\s\d\.\)\(\-–•*]+/u', '', $context) ?? $context;
        $context = preg_replace('/[\s:=\-–]+$/u', '', $context) ?? $context;
        $context = trim($context);

        if ($context === '' || !preg_match('/\p{L}/u', $context)) {
            return '';
        }

        $words = explode(' ', $context);
        if (count($words) > 5) {
            $words = array_slice($words, -5);
        }

        return trim(implode(' ', $words));
    }

    public function extractParagraphsFromDocx(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return [];
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            return [];
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        if (stripos($xml, '<?xml') === false) {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xml;
        }

        if (!@$dom->loadXML($xml)) {
            libxml_clear_errors();
            return [];
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $paragraphs = [];
        foreach ($xpath->query('//w:p') as $para) {
            $text = '';
            foreach ($xpath->query('.//w:t', $para) as $t) {
                $text .= $t->nodeValue;
            }
            if (trim($text) !== '') {
                $paragraphs[] = $text;
            }
        }

        libxml_clear_errors();

        return $paragraphs;
    }

    public function getSourceData(?string $sourceType, int $sourceId): array
    {
        if (empty($sourceType) || !isset($this->allowedModels[$sourceType])) {
            return [];
        }

        $modelClass = $this->allowedModels[$sourceType];
        if (!class_exists($modelClass)) {
            return [];
        }

        $data = $modelClass::with($this->getRelations($sourceType))->find($sourceId);

        return $data ? $this->formatSourceData($data, $sourceType) : [];
    }

    private function getRelations(?string $sourceType): array
    {
        return [
            'karyawan' => [],
            'pelamar' => [],
        ][$sourceType] ?? [];
    }

    private function formatSourceData($model, string $sourceType): array
    {
        $data = $model->toArray();
        $formatted = [];
        $dateConfig = ['day_format' => 'number', 'month_format' => 'month_name', 'year_format' => 'number', 'separator' => ' '];

        foreach ($data as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $formatted[$key] = $this->composeDate($value, $dateConfig);
            } elseif (is_null($value) || $value === '') {
                $formatted[$key] = '-';
            } elseif (
                is_string($value)
                && preg_match('/^\d{4}-\d{2}-\d{2}([T ]\d{2}:\d{2}(:\d{2})?)?/', $value)
                && preg_match('/(tanggal|tgl|awal|akhir|lahir|mulai|masuk|melamar)/i', (string) $key)
            ) {
                try {
                    $formatted[$key] = $this->composeDate(new \DateTime($value), $dateConfig);
                } catch (\Throwable $e) {
                    $formatted[$key] = $value;
                }
            } else {
                $formatted[$key] = $value;
            }
        }

        return $formatted;
    }

    public function generateDocxReport(ReportTemplate $template, array $sourceData, array $manualInputs, bool $preview = false): string
    {
        $this->previewMode = $preview;

        $templatePath = $this->getTemplateAbsolutePath($template->template_file_path);

        if (!file_exists($templatePath)) {
            $this->previewMode = false;
            throw new \Exception('Template file tidak ditemukan di: ' . $templatePath);
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'docx_gen_');
        copy($templatePath, $tempFile);

        try {
            $zip = new \ZipArchive();
            if ($zip->open($tempFile) !== true) {
                throw new \Exception('Gagal membuka file DOCX template');
            }

            $entries = [];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                $entries[$filename] = $zip->getFromName($filename);
            }
            $zip->close();

            $xmlParts = ['word/document.xml'];
            foreach (array_keys($entries) as $file) {
                if (preg_match('#^word/(header|footer)\d*\.xml$#', $file)) {
                    $xmlParts[] = $file;
                }
            }

            $settings = $template->settings ?? [];
            $dateFormat = $settings['date_format'] ?? 'd F Y';

            $replacements = [];
            $loopData = [];

            foreach ($template->placeholders as $placeholder) {
                $key = $placeholder->placeholder_key;
                $type = $placeholder->field_type;

                if (in_array($type, ['loop_manual', 'loop_relation'])) {
                    $loopData[$key] = $this->collectLoopData($placeholder, $sourceData, $manualInputs);
                    continue;
                }

                $replacements[$key] = (string) $this->resolveValue($placeholder, $sourceData, $manualInputs, $dateFormat);
            }

            Log::info('generateDocxReport', [
                'single_keys' => array_keys($replacements),
                'loop_keys' => array_keys($loopData),
                'preview' => $preview,
            ]);

            foreach ($xmlParts as $part) {
                if (!isset($entries[$part])) {
                    continue;
                }

                $xml = $entries[$part];

                if (!empty($loopData) && $part === 'word/document.xml') {
                    $xml = $this->processLoopsInXml($xml, $loopData);
                }

                $entries[$part] = $this->replacePlaceholdersInXml($xml, $replacements);
            }

            $newZip = new \ZipArchive();
            if ($newZip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Gagal membuat file DOCX output');
            }

            foreach ($entries as $filename => $content) {
                $newZip->addFromString($filename, $content);
            }
            $newZip->close();

            $filename = 'report_' . Str::slug($template->name) . '_' . time() . '_' . Str::random(8) . '.docx';
            $outputPath = 'reports/generated/' . date('Y/m') . '/' . $filename;

            Storage::disk('public')->makeDirectory(dirname($outputPath));
            Storage::disk('public')->put($outputPath, file_get_contents($tempFile));

            return $outputPath;
        } finally {
            $this->previewMode = false;
            @unlink($tempFile);
        }
    }

    private function resolveValue(TemplatePlaceholder $placeholder, array $sourceData, array $manualInputs, string $dateFormat): string
    {
        $type = $placeholder->field_type;
        $config = $placeholder->config ?? [];

        if ($type === 'auto_date') {
            return $this->formatAutoDate($config);
        }

        if ($type === 'formula') {
            return $this->evaluateFormula($config, $sourceData, $placeholder->template);
        }

        if ($type === 'auth_field') {
            $field = $config['field'] ?? 'username';
            $user = auth()->user();

            if (!$user) {
                return '-';
            }

            $userFields = ['username', 'jabatan'];
            $karyawanFields = ['nama_lengkap', 'nip', 'email', 'whatsapp'];

            if (in_array($field, $userFields)) {
                return (string) ($user->$field ?? '-');
            }

            if (in_array($field, $karyawanFields)) {
                $karyawan = method_exists($user, 'karyawan')
                    ? $user->karyawan
                    : \App\Models\karyawan::where('user_id', $user->id)->first();

                return $karyawan ? (string) ($karyawan->$field ?? '-') : '-';
            }

            return '-';
        }

        if ($type === 'relation_single') {
            $relation = $config['relation'] ?? '';
            $field = $config['field'] ?? '';
            if (empty($relation) || empty($field)) {
                return '-';
            }

            return (string) data_get($sourceData, "{$relation}.{$field}", '-');
        }

        if (in_array($type, ['manual_text', 'manual_textarea', 'manual_date', 'manual_number', 'manual_select', 'manual_checkbox'])) {
            return $this->resolveManualValue($placeholder, $manualInputs, $dateFormat);
        }

        if ($placeholder->is_manual) {
            $value = $manualInputs[$placeholder->placeholder_key] ?? ($placeholder->default_value ?? '');
        } else {
            $column = (string) $placeholder->source_column;
            $value = data_get($sourceData, $column, data_get($sourceData, Str::snake($column), ''));
        }

        return $this->formatValue($value, $type, $dateFormat);
    }

    private function formatAutoDate(array $config): string
    {
        return $this->composeDate(now(), $config);
    }

    private function composeDate(\DateTimeInterface $date, array $config): string
    {
        $dayFormat = $config['day_format'] ?? 'number';
        $monthFormat = $config['month_format'] ?? 'number';
        $yearFormat = $config['year_format'] ?? 'number';
        $separator = $config['separator'] ?? ' ';

        $parts = [];

        if ($dayFormat !== 'none') {
            $parts[] = $this->formatDayPart($date, $dayFormat);
        }

        if ($monthFormat !== 'none') {
            $parts[] = $this->formatMonthPart($date, $monthFormat);
        }

        if ($yearFormat !== 'none') {
            $parts[] = $this->formatYearPart($date, $yearFormat);
        }

        return implode($separator, $parts);
    }

    private function resolveManualValue(TemplatePlaceholder $placeholder, array $manualInputs, string $dateFormat): string
    {
        $type = $placeholder->field_type;
        $config = $placeholder->config ?? [];
        $key = $placeholder->placeholder_key;

        $value = $manualInputs[$key] ?? ($placeholder->default_value ?? '');

        if (empty($value) && $value !== '0') {
            return '-';
        }

        switch ($type) {
            case 'manual_date':
                return $this->formatManualDate($value, $config);
            case 'manual_number':
                return $this->formatManualNumber($value, $config);
            case 'manual_checkbox':
                return $value ? 'Ya' : 'Tidak';
            default:
                return (string) $value;
        }
    }

    private function formatManualDate($value, array $config): string
    {
        if (empty($value)) {
            return '-';
        }

        try {
            $date = is_string($value) ? new \DateTime($value) : $value;
            if (!$date instanceof \DateTimeInterface) {
                return (string) $value;
            }

            return $this->composeDate($date, $config);
        } catch (\Exception $e) {
            return (string) $value;
        }
    }

    private function formatDayPart(\DateTimeInterface $date, string $format): string
    {
        $dayNumber = (int) $date->format('d');
        $dayOfWeek = (int) $date->format('w');

        $dayNames = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];

        switch ($format) {
            case 'word':
                return $this->numberToWords($dayNumber);
            case 'word_upper':
                return strtoupper($this->numberToWords($dayNumber));
            case 'day_name':
                return $dayNames[$dayOfWeek];
            case 'day_name_upper':
                return strtoupper($dayNames[$dayOfWeek]);
            default:
                return $date->format('d');
        }
    }

    private function formatMonthPart(\DateTimeInterface $date, string $format): string
    {
        $monthNumber = (int) $date->format('n');

        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        switch ($format) {
            case 'month_name':
                return $monthNames[$monthNumber];
            case 'month_name_upper':
                return strtoupper($monthNames[$monthNumber]);
            default:
                return $date->format('m');
        }
    }

    private function formatYearPart(\DateTimeInterface $date, string $format): string
    {
        $yearNumber = (int) $date->format('Y');

        switch ($format) {
            case 'word':
                return $this->numberToWords($yearNumber);
            case 'word_upper':
                return strtoupper($this->numberToWords($yearNumber));
            default:
                return $date->format('Y');
        }
    }

    private function numberToWords(int $number): string
    {
        if ($number === 0) {
            return 'Nol';
        }

        $units = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan'];
        $teens = ['Sepuluh', 'Sebelas', 'Dua Belas', 'Tiga Belas', 'Empat Belas', 'Lima Belas', 'Enam Belas', 'Tujuh Belas', 'Delapan Belas', 'Sembilan Belas'];

        $convert = function ($n) use (&$convert, $units, $teens) {
            if ($n < 10) {
                return $units[$n];
            }
            if ($n < 20) {
                return $teens[$n - 10];
            }
            if ($n < 100) {
                $result = $units[intdiv($n, 10)] . ' Puluh';
                if ($n % 10 > 0) {
                    $result .= ' ' . $units[$n % 10];
                }

                return $result;
            }
            if ($n < 200) {
                $result = 'Seratus';
                if ($n - 100 > 0) {
                    $result .= ' ' . $convert($n - 100);
                }

                return $result;
            }
            if ($n < 1000) {
                $result = $units[intdiv($n, 100)] . ' Ratus';
                if ($n % 100 > 0) {
                    $result .= ' ' . $convert($n % 100);
                }

                return $result;
            }
            if ($n < 2000) {
                $result = 'Seribu';
                if ($n - 1000 > 0) {
                    $result .= ' ' . $convert($n - 1000);
                }

                return $result;
            }
            if ($n < 1000000) {
                $result = $convert(intdiv($n, 1000)) . ' Ribu';
                if ($n % 1000 > 0) {
                    $result .= ' ' . $convert($n % 1000);
                }

                return $result;
            }

            return (string) $n;
        };

        return $convert($number);
    }

    private function formatManualNumber($value, array $config): string
    {
        if (!is_numeric($value)) {
            return (string) $value;
        }

        $numValue = (float) $value;

        switch ($config['number_type'] ?? 'number') {
            case 'currency':
                return 'Rp ' . number_format($numValue, 0, ',', '.');
            case 'integer':
                return number_format((int) $numValue, 0, ',', '.');
            default:
                return number_format($numValue, 2, ',', '.');
        }
    }

    private function evaluateFormula(array $config, array $sourceData, ?ReportTemplate $template = null): string
    {
        $templateStr = $config['template'] ?? '';
        if (empty($templateStr)) {
            return '';
        }

        $templateStr = preg_replace('/(?<!\{)\{([a-z_:0-9]+)\}(?!\})/i', '{{$1}}', $templateStr) ?? $templateStr;

        $counterKey = $config['counter_key'] ?? ('tpl_' . ($template?->id ?? 0) . '_' . date('Y'));

        return preg_replace_callback('/\{\{\s*([a-z_:0-9]+)\s*\}\}/i', function ($m) use ($config, $counterKey, $sourceData, $template) {
            $var = trim($m[1]);

            if ($var === 'tahun') {
                return date('Y');
            }
            if ($var === 'bulan') {
                return date('m');
            }
            if ($var === 'bulan_nama') {
                return now()->translatedFormat('F');
            }
            if ($var === 'bulan_romawi') {
                return $this->toRoman((int) date('m'));
            }
            if ($var === 'tanggal') {
                return date('d');
            }
            if ($var === 'hari') {
                return now()->translatedFormat('l');
            }
            if ($var === 'hari_tanggal') {
                return now()->translatedFormat('l, d F Y');
            }

            $startFrom = (int) ($config['last_number'] ?? 0);

            if (preg_match('/^urutan:(\d+)$/', $var, $cm)) {
                $val = $this->incrementCounter($template, $counterKey, $startFrom);

                return str_pad((string) $val, (int) $cm[1], '0', STR_PAD_LEFT);
            }
            if ($var === 'urutan_romawi') {
                return $this->toRoman($this->incrementCounter($template, $counterKey, $startFrom));
            }
            if ($var === 'urutan') {
                return (string) $this->incrementCounter($template, $counterKey, $startFrom);
            }
            if (str_starts_with($var, 'auth:')) {
                $user = Auth::user();

                return $user ? (string) ($user->{substr($var, 5)} ?? '') : '';
            }

            if (array_key_exists($var, $sourceData)) {
                $v = $sourceData[$var];

                return is_scalar($v) ? (string) $v : json_encode($v);
            }

            return $m[0];
        }, $templateStr);
    }

    private function incrementCounter(?ReportTemplate $template, string $key, int $startFrom = 0): int
    {
        if (!$template) {
            return $startFrom + 1;
        }

        if ($this->previewMode) {
            $counters = ($template->settings ?? [])['counters'] ?? [];

            return isset($counters[$key]) ? ((int) $counters[$key] + 1) : ($startFrom + 1);
        }

        return DB::transaction(function () use ($template, $key, $startFrom) {
            $template = ReportTemplate::where('id', $template->id)->lockForUpdate()->first();
            $settings = $template->settings ?? [];
            $counters = $settings['counters'] ?? [];

            $current = isset($counters[$key]) ? ($counters[$key] + 1) : ($startFrom + 1);

            $counters[$key] = $current;
            $settings['counters'] = $counters;

            $template->update(['settings' => $settings]);

            return $current;
        });
    }

    private function toRoman(int $number): string
    {
        if ($number <= 0 || $number > 3999) {
            return (string) $number;
        }

        $map = [
            1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD',
            100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL',
            10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I',
        ];

        $result = '';
        foreach ($map as $value => $symbol) {
            while ($number >= $value) {
                $result .= $symbol;
                $number -= $value;
            }
        }

        return $result;
    }

    private function collectLoopData(TemplatePlaceholder $placeholder, array $sourceData, array $manualInputs): array
    {
        $type = $placeholder->field_type;
        $config = $placeholder->config ?? [];
        $key = $placeholder->placeholder_key;

        if ($type === 'loop_manual') {
            $data = $manualInputs[$key] ?? [];

            return is_array($data) ? array_values($data) : [];
        }

        if ($type === 'loop_relation') {
            $relation = $config['relation'] ?? '';
            if (empty($relation)) {
                return [];
            }

            $relatedData = data_get($sourceData, $relation, []);

            if ($relatedData instanceof \Illuminate\Support\Collection) {
                $relatedData = $relatedData->toArray();
            }

            return is_array($relatedData) ? array_values($relatedData) : [];
        }

        return [];
    }

    private function processLoopsInXml(string $xml, array $loopData): string
    {
        if (empty($loopData)) {
            return $xml;
        }

        libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;

        if (stripos($xml, '<?xml') === false) {
            $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . $xml;
        }

        if (!@$dom->loadXML($xml)) {
            libxml_clear_errors();

            return $xml;
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        foreach ($loopData as $collectionName => $items) {
            $this->processCollectionLoop($dom, $xpath, $collectionName, $items);
        }

        libxml_clear_errors();

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . $dom->saveXML($dom->documentElement);
    }

    private function processCollectionLoop($dom, $xpath, string $collectionName, array $items): void
    {
        $tNodes = $xpath->query('//w:t[contains(., "{{ loop:' . $collectionName . '")]');

        if ($tNodes->length === 0) {
            return;
        }

        $firstT = $tNodes->item(0);
        $rowNode = $this->findAncestor($firstT, 'tr') ?? $this->findAncestor($firstT, 'p');

        if (!$rowNode || !$rowNode->parentNode) {
            return;
        }

        $parent = $rowNode->parentNode;

        if (empty($items)) {
            $parent->removeChild($rowNode);

            return;
        }

        $template = $rowNode->cloneNode(true);
        $this->replaceLoopPlaceholdersInNode($rowNode, $xpath, $collectionName, $items[0]);

        $currentRow = $rowNode;
        for ($i = 1; $i < count($items); $i++) {
            $clone = $template->cloneNode(true);
            $parent->insertBefore($clone, $currentRow->nextSibling);
            $this->replaceLoopPlaceholdersInNode($clone, $xpath, $collectionName, $items[$i]);
            $currentRow = $clone;
        }
    }

    private function findAncestor($node, string $localName)
    {
        $current = $node->parentNode;
        while ($current) {
            if ($current->localName === $localName) {
                return $current;
            }
            $current = $current->parentNode;
        }

        return null;
    }

    private function replaceLoopPlaceholdersInNode($node, $xpath, string $collectionName, $item): void
    {
        $tNodes = $xpath->query('.//w:t', $node);
        foreach ($tNodes as $t) {
            $text = $t->nodeValue ?? '';
            $newText = preg_replace_callback(
                '/\{\{\s*loop:' . preg_quote($collectionName, '/') . '\.([a-z0-9_]+)\s*\}\}/i',
                function ($m) use ($item) {
                    $field = $m[1];
                    $value = data_get($item, $field, data_get($item, Str::snake($field), '-'));
                    if ($value instanceof \DateTimeInterface) {
                        $value = $value->format('d F Y');
                    }

                    return htmlspecialchars((string) ($value ?? '-'), ENT_XML1 | ENT_QUOTES, 'UTF-8');
                },
                $text
            );
            if ($newText !== $text) {
                $t->nodeValue = $newText;
            }
        }
    }

    private function replacePlaceholdersInXml(string $xml, array $replacements): string
    {
        if (empty($replacements)) {
            return $xml;
        }

        $result = $this->replaceWithDom($xml, $replacements);
        if ($result !== null) {
            return $result;
        }

        Log::warning('DOM parse gagal, fallback ke string replacement');

        return $this->replaceWithStringFallback($xml, $replacements);
    }

    private function replaceWithDom(string $xml, array $replacements): ?string
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;

        if (stripos($xml, '<?xml') === false) {
            $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . $xml;
        }

        if (!@$dom->loadXML($xml)) {
            libxml_clear_errors();

            return null;
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        foreach ($xpath->query('//w:p') as $para) {
            foreach ($replacements as $key => $value) {
                $this->replacePlaceholderInParagraph($para, $xpath, (string) $key, (string) $value);
            }
        }

        libxml_clear_errors();

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . $dom->saveXML($dom->documentElement);
    }

    private function replacePlaceholderInParagraph($para, \DOMXPath $xpath, string $key, string $value): void
    {
        $tNodes = $xpath->query('.//w:t', $para);
        if ($tNodes->length === 0) {
            return;
        }

        $fullText = '';
        $nodeMap = [];
        foreach ($tNodes as $t) {
            $text = $t->nodeValue ?? '';
            $nodeMap[] = [
                'node' => $t,
                'start' => mb_strlen($fullText, 'UTF-8'),
                'length' => mb_strlen($text, 'UTF-8'),
            ];
            $fullText .= $text;
        }

        $pattern = '/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/u';
        if (!preg_match_all($pattern, $fullText, $matches, PREG_OFFSET_CAPTURE)) {
            return;
        }

        $allMatches = [];
        foreach ($matches[0] as $hit) {
            $charStart = mb_strlen(substr($fullText, 0, $hit[1]), 'UTF-8');
            $allMatches[] = [
                'text' => $hit[0],
                'start' => $charStart,
                'end' => $charStart + mb_strlen($hit[0], 'UTF-8'),
            ];
        }
        usort($allMatches, fn ($a, $b) => $b['start'] - $a['start']);

        $safeValue = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        foreach ($allMatches as $match) {
            $affected = [];
            foreach ($nodeMap as $pos) {
                if ($pos['start'] < $match['end'] && $pos['start'] + $pos['length'] > $match['start']) {
                    $affected[] = $pos;
                }
            }
            if (empty($affected)) {
                continue;
            }

            $firstNode = $affected[0]['node'];
            $localStart = $match['start'] - $affected[0]['start'];
            $origText = $firstNode->nodeValue;

            if (count($affected) === 1) {
                $matchLen = mb_strlen($match['text'], 'UTF-8');
                $firstNode->nodeValue =
                    mb_substr($origText, 0, $localStart, 'UTF-8')
                    . $safeValue
                    . mb_substr($origText, $localStart + $matchLen, null, 'UTF-8');
                continue;
            }

            $firstNode->nodeValue = mb_substr($origText, 0, $localStart, 'UTF-8') . $safeValue;

            for ($i = 1; $i < count($affected); $i++) {
                $node = $affected[$i]['node'];
                $nodeStart = $affected[$i]['start'];
                $nodeEnd = $nodeStart + $affected[$i]['length'];

                if ($nodeEnd <= $match['end']) {
                    $node->nodeValue = '';
                } else {
                    $node->nodeValue = mb_substr($node->nodeValue, $match['end'] - $nodeStart, null, 'UTF-8');
                }
            }
        }
    }

    private function replaceWithStringFallback(string $xml, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $safeValue = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

            $variants = [
                '{{ ' . $key . ' }}',
                '{{' . $key . '}}',
                htmlspecialchars('{{ ' . $key . ' }}', ENT_XML1, 'UTF-8'),
                htmlspecialchars('{{' . $key . '}}', ENT_XML1, 'UTF-8'),
            ];

            foreach ($variants as $variant) {
                $xml = str_replace($variant, $safeValue, $xml);
            }
        }

        return $xml;
    }

    public function extractPlaceholdersFromDocx(string $filePath): array
    {
        if (!file_exists($filePath)) {
            Log::error('extractPlaceholdersFromDocx: file tidak ditemukan - ' . $filePath);

            return [];
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        $xmlContent = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xmlContent === false) {
            return [];
        }

        $placeholders = $this->extractPlaceholdersWithDom($xmlContent);

        Log::info('extractPlaceholdersFromDocx result', [
            'file' => basename($filePath),
            'found' => count($placeholders),
            'keys' => array_column($placeholders, 'key'),
        ]);

        return $placeholders;
    }

    private function extractPlaceholdersWithDom(string $xml): array
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        if (stripos($xml, '<?xml') === false) {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xml;
        }
        if (!@$dom->loadXML($xml)) {
            libxml_clear_errors();

            return $this->extractPlaceholdersWithRegex($xml);
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $seen = [];
        $placeholders = [];

        foreach ($xpath->query('//w:p') as $para) {
            $fullText = '';
            foreach ($xpath->query('.//w:t', $para) as $t) {
                $fullText .= $t->nodeValue;
            }

            if (preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/u', $fullText, $matches)) {
                foreach ($matches[1] as $match) {
                    $key = trim(Str::snake($match));
                    if (!empty($key) && !isset($seen[$key])) {
                        $seen[$key] = true;
                        $placeholders[] = [
                            'key' => $key,
                            'label' => Str::title(str_replace('_', ' ', $key)),
                        ];
                    }
                }
            }
        }

        libxml_clear_errors();

        return $placeholders;
    }

    private function extractPlaceholdersWithRegex(string $xml): array
    {
        $decoded = html_entity_decode($xml, ENT_XML1 | ENT_HTML5, 'UTF-8');
        preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $decoded, $matches);

        $seen = [];
        $placeholders = [];
        foreach ($matches[1] ?? [] as $match) {
            $key = trim(Str::snake($match));
            if (!empty($key) && !isset($seen[$key])) {
                $seen[$key] = true;
                $placeholders[] = [
                    'key' => $key,
                    'label' => Str::title(str_replace('_', ' ', $key)),
                ];
            }
        }

        return $placeholders;
    }

    public function replaceDummyWithPlaceholders(string $docxPath, array $replacements): string
    {
        $tempOutput = tempnam(sys_get_temp_dir(), 'docx_mapped_');
        copy($docxPath, $tempOutput);

        if (empty($replacements)) {
            return $tempOutput;
        }

        $zip = new \ZipArchive();
        if ($zip->open($tempOutput) !== true) {
            @unlink($tempOutput);
            throw new \Exception('Gagal membuka file DOCX untuk diproses');
        }

        $xmlContent = $zip->getFromName('word/document.xml');
        if ($xmlContent === false) {
            $zip->close();
            @unlink($tempOutput);
            throw new \Exception('word/document.xml tidak ditemukan');
        }

        Log::info('replaceDummyWithPlaceholders: mulai', ['count' => count($replacements)]);

        usort($replacements, fn ($a, $b) => mb_strlen($b['find'] ?? '', 'UTF-8') - mb_strlen($a['find'] ?? '', 'UTF-8'));

        $zip->addFromString('word/document.xml', $this->replaceDummyTextWithDom($xmlContent, $replacements));
        $zip->close();

        Log::info('replaceDummyWithPlaceholders: selesai');

        return $tempOutput;
    }

    private function replaceDummyTextWithDom(string $xml, array $replacements): string
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;

        if (stripos($xml, '<?xml') === false) {
            $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . $xml;
        }

        if (!@$dom->loadXML($xml)) {
            libxml_clear_errors();

            return $this->replaceDummyTextWithString($xml, $replacements);
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        foreach ($xpath->query('//w:p') as $para) {
            foreach ($replacements as $item) {
                $find = trim($item['find'] ?? '');
                $field = trim($item['replace'] ?? '');
                $blank = trim($item['blank'] ?? '');

                if ($find === '' || $field === '') {
                    continue;
                }

                $placeholder = ($blank !== '' && str_contains($find, $blank))
                    ? str_replace($blank, '{{ ' . $field . ' }}', $find)
                    : '{{ ' . $field . ' }}';

                $this->replaceTextInParagraph($para, $xpath, $find, $placeholder);
            }
        }

        libxml_clear_errors();

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . $dom->saveXML($dom->documentElement);
    }

    private function replaceTextInParagraph($para, \DOMXPath $xpath, string $find, string $replace): void
    {
        $tNodes = $xpath->query('.//w:t', $para);
        if ($tNodes->length === 0) {
            return;
        }

        $fullText = '';
        $nodeMap = [];
        foreach ($tNodes as $t) {
            $text = $t->nodeValue ?? '';
            $nodeMap[] = [
                'node' => $t,
                'start' => mb_strlen($fullText, 'UTF-8'),
                'length' => mb_strlen($text, 'UTF-8'),
            ];
            $fullText .= $text;
        }

        $pos = mb_strpos($fullText, $find, 0, 'UTF-8');
        if ($pos === false && (str_contains($find, '{') || str_contains($find, '}'))) {
            $cleanKey = trim(str_replace(['{', '}'], '', $find));
            $variations = [
                '{{ ' . $cleanKey . ' }}',
                '{{' . $cleanKey . '}}',
                '{{ ' . $cleanKey . '}}',
                '{{' . $cleanKey . ' }}',
                '{{{' . $cleanKey . '}}}',
                '{{{ ' . $cleanKey . ' }}}',
            ];
            foreach ($variations as $var) {
                $varPos = mb_strpos($fullText, $var, 0, 'UTF-8');
                if ($varPos !== false) {
                    $find = $var;
                    $pos = $varPos;
                    break;
                }
            }
        }
        if ($pos === false) {
            return;
        }

        $matchStart = $pos;
        $matchEnd = $pos + mb_strlen($find, 'UTF-8');
        $safeValue = htmlspecialchars($replace, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $affected = [];
        foreach ($nodeMap as $p) {
            if ($p['start'] < $matchEnd && $p['start'] + $p['length'] > $matchStart) {
                $affected[] = $p;
            }
        }
        if (empty($affected)) {
            return;
        }

        $firstNode = $affected[0]['node'];
        $localStart = $matchStart - $affected[0]['start'];
        $origText = $firstNode->nodeValue;

        if (count($affected) === 1) {
            $firstNode->nodeValue =
                mb_substr($origText, 0, $localStart, 'UTF-8')
                . $safeValue
                . mb_substr($origText, $localStart + mb_strlen($find, 'UTF-8'), null, 'UTF-8');

            return;
        }

        $firstNode->nodeValue = mb_substr($origText, 0, $localStart, 'UTF-8') . $safeValue;

        for ($i = 1; $i < count($affected); $i++) {
            $node = $affected[$i]['node'];
            $nodeStart = $affected[$i]['start'];
            $nodeEnd = $nodeStart + $affected[$i]['length'];

            if ($nodeEnd <= $matchEnd) {
                $node->nodeValue = '';
            } else {
                $node->nodeValue = mb_substr($node->nodeValue, $matchEnd - $nodeStart, null, 'UTF-8');
            }
        }
    }

    private function replaceDummyTextWithString(string $xml, array $replacements): string
    {
        foreach ($replacements as $item) {
            $find = trim($item['find'] ?? '');
            $field = trim($item['replace'] ?? '');
            $blank = trim($item['blank'] ?? '');
            if ($find === '' || $field === '') {
                continue;
            }

            $placeholder = ($blank !== '' && str_contains($find, $blank))
                ? str_replace($blank, '{{ ' . $field . ' }}', $find)
                : '{{ ' . $field . ' }}';

            $escapedFind = htmlspecialchars($find, ENT_XML1, 'UTF-8');
            $escapedPlaceholder = htmlspecialchars($placeholder, ENT_XML1, 'UTF-8');

            if (strpos($xml, $escapedFind) !== false) {
                $xml = $this->replaceOnce($xml, $escapedFind, $escapedPlaceholder);
            } elseif (strpos($xml, $find) !== false) {
                $xml = $this->replaceOnce($xml, $find, $escapedPlaceholder);
            }
        }

        return $xml;
    }

    private function replaceOnce(string $subject, string $search, string $replace): string
    {
        $pos = strpos($subject, $search);
        if ($pos === false) {
            return $subject;
        }

        return substr($subject, 0, $pos) . $replace . substr($subject, $pos + strlen($search));
    }

    public function extractTextFromDocx(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return '';
        }

        try {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                $xmlContent = $zip->getFromName('word/document.xml');
                $zip->close();
                if ($xmlContent !== false) {
                    return preg_replace('/[ \t]+/', ' ', $this->extractPlainTextWithDom($xmlContent));
                }
            }

            return '';
        } catch (\Exception $e) {
            Log::warning('Failed to extract DOCX text: ' . $e->getMessage());

            return '';
        }
    }

    private function extractPlainTextWithDom(string $xml): string
    {
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        if (stripos($xml, '<?xml') === false) {
            $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . $xml;
        }
        if (!@$dom->loadXML($xml)) {
            libxml_clear_errors();

            return strip_tags(html_entity_decode($xml, ENT_XML1, 'UTF-8'));
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $result = '';
        foreach ($xpath->query('//w:p') as $para) {
            $paraText = '';
            foreach ($xpath->query('.//w:t', $para) as $t) {
                $paraText .= $t->nodeValue;
            }
            $result .= $paraText . "\n";
        }

        libxml_clear_errors();

        return trim($result);
    }

    public function getTemplateAbsolutePath(string $relativePath): string
    {
        $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\'));

        $path = storage_path('app/public/' . $relativePath);
        if (file_exists($path)) {
            return $path;
        }

        $path = storage_path('app/' . $relativePath);
        if (file_exists($path)) {
            return $path;
        }

        return storage_path('app/public/' . $relativePath);
    }

    private function formatValue($value, ?string $fieldType, string $dateFormat): string
    {
        if (is_null($value) || $value === '') {
            return '-';
        }

        if ($fieldType === 'date') {
            try {
                return is_string($value)
                    ? date($dateFormat, strtotime($value))
                    : ($value instanceof \DateTimeInterface ? $value->format($dateFormat) : (string) $value);
            } catch (\Exception $e) {
                return (string) $value;
            }
        }

        if ($fieldType === 'currency' && is_numeric($value)) {
            return 'Rp ' . number_format((float) $value, 0, ',', '.');
        }
        if ($fieldType === 'number' && is_numeric($value)) {
            return number_format((float) $value, 2, ',', '.');
        }
        if ($fieldType === 'checkbox') {
            return $value ? 'Ya' : 'Tidak';
        }
        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return (string) $value;
    }

    public function validateTemplate(ReportTemplate $template): array
    {
        $errors = [];
        $warnings = [];

        if (empty($template->source_table)) {
            $errors[] = 'Source table tidak dikonfigurasi';
        } elseif (!isset($this->allowedModels[$template->source_table])) {
            $errors[] = 'Source table tidak valid: ' . $template->source_table;
        }

        if (empty($template->template_file_path) || !file_exists($this->getTemplateAbsolutePath($template->template_file_path))) {
            $errors[] = 'File template tidak ditemukan';
        }

        $placeholders = $template->placeholders;
        if ($placeholders->isEmpty()) {
            $warnings[] = 'Tidak ada field placeholder yang dikonfigurasi';
        }

        foreach ($placeholders as $ph) {
            if (!$ph->is_manual && empty($ph->source_column)) {
                $warnings[] = "Source column kosong untuk auto field: {$ph->placeholder_key}";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }
}