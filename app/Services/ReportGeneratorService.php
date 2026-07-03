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
    protected array $allowedModels = [
        'karyawan' => \App\Models\karyawan::class,
        'pelamar' => \App\Models\Pelamar::class,
    ];

    protected array $allowedFieldTypes = [
        'text', 'textarea', 'date', 'select', 'checkbox', 'number', 'currency',
        'auto_date', 'formula', 'auth_field', 'relation_single',
        'loop_manual', 'loop_relation',
        'manual_text', 'manual_textarea', 'manual_date', 'manual_number', 'manual_select', 'manual_checkbox'
    ];

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

        foreach ($data as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $formatted[$key] = $value->format('d F Y');
            } elseif (is_null($value)) {
                $formatted[$key] = '-';
            } else {
                $formatted[$key] = $value;
            }
        }

        return $formatted;
    }

    public function generateDocxReport(ReportTemplate $template, array $sourceData, array $manualInputs): string
    {
        $templatePath = $this->getTemplateAbsolutePath($template->template_file_path);

        if (!file_exists($templatePath)) {
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

                $value = $this->resolveValue($placeholder, $sourceData, $manualInputs, $dateFormat);
                $replacements[$key] = (string) $value;
            }

            Log::info('generateDocxReport', [
                'single_keys' => array_keys($replacements),
                'loop_keys' => array_keys($loopData),
            ]);

            foreach ($xmlParts as $part) {
                if (!isset($entries[$part])) continue;

                $xml = $entries[$part];

                if (!empty($loopData) && $part === 'word/document.xml') {
                    $xml = $this->processLoopsInXml($xml, $loopData);
                }

                $xml = $this->replacePlaceholdersInXml($xml, $replacements);
                $entries[$part] = $xml;
            }

            $newZip = new \ZipArchive();
            if ($newZip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \Exception('Gagal membuat file DOCX output');
            }

            foreach ($entries as $filename => $content) {
                $newZip->addFromString($filename, $content);
            }
            $newZip->close();

            $filename   = 'report_' . Str::slug($template->name) . '_' . time() . '_' . Str::random(8) . '.docx';
            $outputPath = 'reports/generated/' . date('Y/m') . '/' . $filename;

            Storage::disk('public')->makeDirectory(dirname($outputPath));
            Storage::disk('public')->put($outputPath, file_get_contents($tempFile));

            return $outputPath;
        } finally {
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
            
            if (!$user) return '-';

            $userFields = ['username', 'jabatan']; 
            
            $karyawanFields = ['nama_lengkap', 'nip', 'email', 'whatsapp'];

            if (in_array($field, $userFields)) {
                return (string) ($user->$field ?? '-');
            }

            if (in_array($field, $karyawanFields)) {
                $karyawan = null;
                if (method_exists($user, 'karyawan')) {
                    $karyawan = $user->karyawan;
                } else {
                    $karyawan = \App\Models\karyawan::where('user_id', $user->id)->first();
                }
                
                return $karyawan ? (string) ($karyawan->$field ?? '-') : '-';
            }

            return '-';
        }

        if ($type === 'relation_single') {
            $relation = $config['relation'] ?? '';
            $field = $config['field'] ?? '';
            if (empty($relation) || empty($field)) return '-';
            return (string) data_get($sourceData, "{$relation}.{$field}", '-');
        }

        // Handle manual input fields
        if (in_array($type, ['manual_text', 'manual_textarea', 'manual_date', 'manual_number', 'manual_select', 'manual_checkbox'])) {
            return $this->resolveManualValue($placeholder, $manualInputs, $dateFormat);
        }

        if ($placeholder->is_manual) {
            $value = $manualInputs[$placeholder->placeholder_key] ?? ($placeholder->default_value ?? '');
        } else {
            $column = $placeholder->source_column;
            $value = data_get($sourceData, $column, data_get($sourceData, Str::snake($column), ''));
        }

        return $this->formatValue($value, $type, $dateFormat);
    }

    private function formatAutoDate(array $config): string
    {
        $dayFormat = $config['day_format'] ?? 'number';
        $monthFormat = $config['month_format'] ?? 'number';
        $yearFormat = $config['year_format'] ?? 'number';
        $separator = $config['separator'] ?? ' ';
        
        $now = now();
        $parts = [];
        
        // Format Hari/Tanggal
        if ($dayFormat !== 'none') {
            $parts[] = $this->formatDayPart($now, $dayFormat);
        }
        
        // Format Bulan
        if ($monthFormat !== 'none') {
            $parts[] = $this->formatMonthPart($now, $monthFormat);
        }
        
        // Format Tahun
        if ($yearFormat !== 'none') {
            $parts[] = $this->formatYearPart($now, $yearFormat);
        }
        
        return implode($separator, $parts);
    }

    private function formatMonth(\DateTimeInterface $date, string $format): string
    {
        switch ($format) {
            case 'number':
                return $date->format('m');
            case 'word_short':
                return $date->format('M');
            case 'word_full':
                return $date->format('F');
            default:
                return $date->format('m');
        }
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
            case 'manual_text':
            case 'manual_textarea':
                return (string) $value;
                
            case 'manual_date':
                return $this->formatManualDate($value, $config);
                
            case 'manual_number':
                return $this->formatManualNumber($value, $config);
                
            case 'manual_select':
                return (string) $value;
                
            case 'manual_checkbox':
                return $value ? 'Ya' : 'Tidak';
                
            default:
                return (string) $value;
        }
    }

    private function formatManualDate($value, array $config): string
    {
        if (empty($value)) return '-';
        
        try {
            $date = is_string($value) ? new \DateTime($value) : $value;
            if (!$date instanceof \DateTimeInterface) {
                return (string) $value;
            }
            
            $dayFormat = $config['day_format'] ?? 'number';
            $monthFormat = $config['month_format'] ?? 'number';
            $yearFormat = $config['year_format'] ?? 'number';
            $separator = $config['separator'] ?? ' ';
            
            $parts = [];
            
            // Format Hari/Tanggal
            if ($dayFormat !== 'none') {
                $parts[] = $this->formatDayPart($date, $dayFormat);
            }
            
            // Format Bulan
            if ($monthFormat !== 'none') {
                $parts[] = $this->formatMonthPart($date, $monthFormat);
            }
            
            // Format Tahun
            if ($yearFormat !== 'none') {
                $parts[] = $this->formatYearPart($date, $yearFormat);
            }
            
            return implode($separator, $parts);
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
            case 'number':
                return $date->format('d');
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
            case 'number':
                return $date->format('m');
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
            case 'number':
                return $date->format('Y');
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
        if ($number === 0) return 'Nol';
        
        $units = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan'];
        $teens = ['Sepuluh', 'Sebelas', 'Dua Belas', 'Tiga Belas', 'Empat Belas', 'Lima Belas', 'Enam Belas', 'Tujuh Belas', 'Delapan Belas', 'Sembilan Belas'];
        
        $convert = function($n) use (&$convert, $units, $teens) {
            if ($n < 10) {
                return $units[$n];
            } elseif ($n < 20) {
                return $teens[$n - 10];
            } elseif ($n < 100) {
                $tens = intdiv($n, 10);
                $remainder = $n % 10;
                $result = $units[$tens] . ' Puluh';
                if ($remainder > 0) {
                    $result .= ' ' . $units[$remainder];
                }
                return $result;
            } elseif ($n < 200) {
                $remainder = $n - 100;
                $result = 'Seratus';
                if ($remainder > 0) {
                    $result .= ' ' . $convert($remainder);
                }
                return $result;
            } elseif ($n < 1000) {
                $hundreds = intdiv($n, 100);
                $remainder = $n % 100;
                $result = $units[$hundreds] . ' Ratus';
                if ($remainder > 0) {
                    $result .= ' ' . $convert($remainder);
                }
                return $result;
            } elseif ($n < 2000) {
                $remainder = $n - 1000;
                $result = 'Seribu';
                if ($remainder > 0) {
                    $result .= ' ' . $convert($remainder);
                }
                return $result;
            } elseif ($n < 10000) {
                $thousands = intdiv($n, 1000);
                $remainder = $n % 1000;
                $result = $units[$thousands] . ' Ribu';
                if ($remainder > 0) {
                    $result .= ' ' . $convert($remainder);
                }
                return $result;
            } elseif ($n < 100000) {
                $tenThousands = intdiv($n, 10000);
                $remainder = $n % 10000;
                $result = $convert($tenThousands) . ' Ribu';
                if ($remainder > 0) {
                    $result .= ' ' . $convert($remainder);
                }
                return $result;
            }
            
            return (string) $n;
        };
        
        return $convert($number);
    }

    private function formatManualNumber($value, array $config): string
    {
        if (!is_numeric($value)) return (string) $value;
        
        $numberType = $config['number_type'] ?? 'number';
        $numValue = (float) $value;
        
        switch ($numberType) {
            case 'currency':
                return 'Rp ' . number_format($numValue, 0, ',', '.');
            case 'integer':
                return number_format((int) $numValue, 0, ',', '.');
            case 'number':
            default:
                return number_format($numValue, 2, ',', '.');
        }
    }

    private function evaluateFormula(array $config, array $sourceData, ?ReportTemplate $template = null): string
    {
        $templateStr = $config['template'] ?? '';
        if (empty($templateStr)) return '';

        $counterKey = $config['counter_key'] ?? ('tpl_' . ($template?->id ?? 0) . '_' . date('Y'));

        return preg_replace_callback('/\{\{\s*([a-z_:0-9]+)\s*\}\}/i', function ($m) use ($config, $counterKey, $sourceData, $template) {
            $var = trim($m[1]);

            if ($var === 'tahun') return date('Y');
            if ($var === 'bulan') return date('m');
            if ($var === 'bulan_nama') return now()->translatedFormat('F');
            if ($var === 'bulan_romawi') return $this->toRoman((int) date('m'));
            if ($var === 'tanggal') return date('d');
            if ($var === 'hari') return now()->translatedFormat('l');
            if ($var === 'hari_tanggal') return now()->translatedFormat('l, d F Y');

            if (preg_match('/^urutan:(\d+)$/', $var, $cm)) {
                $pad = (int) $cm[1];
                $startFrom = (int) ($config['last_number'] ?? 0);
                $val = $this->incrementCounter($template, $counterKey, $startFrom);
                return str_pad($val, $pad, '0', STR_PAD_LEFT);
            }
            if ($var === 'urutan_romawi') {
                $startFrom = (int) ($config['last_number'] ?? 0);
                $val = $this->incrementCounter($template, $counterKey, $startFrom);
                return $this->toRoman($val);
            }
            if ($var === 'urutan') {
                $startFrom = (int) ($config['last_number'] ?? 0);
                return (string) $this->incrementCounter($template, $counterKey, $startFrom);
            }
            if (str_starts_with($var, 'auth:')) {
                $field = substr($var, 5);
                $user = Auth::user();
                return $user ? (string) ($user->$field ?? '') : '';
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
        if (!$template) return $startFrom + 1;

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
        if ($number <= 0 || $number > 3999) return (string) $number;
        $map = [
            1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD',
            100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL',
            10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I'
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
            if (empty($relation)) return [];

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
        if (empty($loopData)) return $xml;

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

        $xmlDecl = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        return $xmlDecl . $dom->saveXML($dom->documentElement);
    }

    private function processCollectionLoop($dom, $xpath, string $collectionName, array $items): void
    {
        $tNodes = $xpath->query('//w:t[contains(., "{{ loop:' . $collectionName . '")]');

        if ($tNodes->length === 0) return;

        $firstT = $tNodes->item(0);
        $rowNode = $this->findAncestor($firstT, 'tr');

        if (!$rowNode) {
            $rowNode = $this->findAncestor($firstT, 'p');
        }

        if (!$rowNode || !$rowNode->parentNode) return;

        $parent = $rowNode->parentNode;

        if (empty($items)) {
            $parent->removeChild($rowNode);
            return;
        }

        $this->replaceLoopPlaceholdersInNode($rowNode, $xpath, $collectionName, $items[0]);

        $currentRow = $rowNode;
        for ($i = 1; $i < count($items); $i++) {
            $clone = $rowNode->cloneNode(true);
            $this->replaceLoopPlaceholdersInNode($clone, $xpath, $collectionName, $items[$i]);

            if ($currentRow->nextSibling) {
                $parent->insertBefore($clone, $currentRow->nextSibling);
            } else {
                $parent->appendChild($clone);
            }
            $currentRow = $clone;
        }
    }

    private function findAncestor($node, string $localName)
    {
        $current = $node->parentNode;
        while ($current) {
            if ($current->localName === $localName) return $current;
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
        if (empty($replacements)) return $xml;

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

        $paragraphs = $xpath->query('//w:p');
        foreach ($paragraphs as $para) {
            foreach ($replacements as $key => $value) {
                $this->replacePlaceholderInParagraph($para, $xpath, $key, $value);
            }
        }

        libxml_clear_errors();

        $xmlDecl = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        return $xmlDecl . $dom->saveXML($dom->documentElement);
    }

    private function replacePlaceholderInParagraph($para, \DOMXPath $xpath, string $key, string $value): void
    {
        $tNodes = $xpath->query('.//w:t', $para);
        if ($tNodes->length === 0) return;

        $fullText = '';
        $nodeMap  = [];
        foreach ($tNodes as $t) {
            $start = mb_strlen($fullText, 'UTF-8');
            $text  = $t->nodeValue ?? '';
            $fullText .= $text;
            $nodeMap[] = [
                'node'   => $t,
                'start'  => $start,
                'length' => mb_strlen($text, 'UTF-8'),
            ];
        }

        $pattern = '/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/u';
        if (!preg_match_all($pattern, $fullText, $matches, PREG_OFFSET_CAPTURE)) {
            return;
        }

        $allMatches = [];
        for ($i = 0; $i < count($matches[0]); $i++) {
            $allMatches[] = [
                'text'  => $matches[0][$i][0],
                'start' => $matches[0][$i][1],
                'end'   => $matches[0][$i][1] + mb_strlen($matches[0][$i][0], 'UTF-8'),
            ];
        }
        usort($allMatches, fn($a, $b) => $b['start'] - $a['start']);

        foreach ($allMatches as $match) {
            $affected = [];
            foreach ($nodeMap as $pos) {
                $nodeStart = $pos['start'];
                $nodeEnd   = $nodeStart + $pos['length'];
                if ($nodeStart < $match['end'] && $nodeEnd > $match['start']) {
                    $affected[] = $pos;
                }
            }
            if (empty($affected)) continue;

            $safeValue = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

            if (count($affected) === 1) {
                $node       = $affected[0]['node'];
                $localStart = $match['start'] - $affected[0]['start'];
                $matchLen   = mb_strlen($match['text'], 'UTF-8');
                $origText   = $node->nodeValue;

                $node->nodeValue =
                    mb_substr($origText, 0, $localStart, 'UTF-8')
                    . $safeValue
                    . mb_substr($origText, $localStart + $matchLen, null, 'UTF-8');
            } else {
                $firstNode  = $affected[0]['node'];
                $localStart = $match['start'] - $affected[0]['start'];
                $origText   = $firstNode->nodeValue;

                $firstNode->nodeValue =
                    mb_substr($origText, 0, $localStart, 'UTF-8')
                    . $safeValue;

                for ($i = 1; $i < count($affected); $i++) {
                    $node      = $affected[$i]['node'];
                    $nodeStart = $affected[$i]['start'];
                    $nodeEnd   = $nodeStart + $affected[$i]['length'];
                    $nodeText  = $node->nodeValue;

                    if ($nodeEnd <= $match['end']) {
                        $node->nodeValue = '';
                    } else {
                        $cutLen = $match['end'] - $nodeStart;
                        $node->nodeValue = mb_substr($nodeText, $cutLen, null, 'UTF-8');
                    }
                }
            }
        }
    }

    private function replaceWithStringFallback(string $xml, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $safeValue = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

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

        if ($xmlContent === false) return [];

        $placeholders = $this->extractPlaceholdersWithDom($xmlContent);

        Log::info('extractPlaceholdersFromDocx result', [
            'file'  => basename($filePath),
            'found' => count($placeholders),
            'keys'  => array_column($placeholders, 'key'),
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

        $seen         = [];
        $placeholders = [];

        $paragraphs = $xpath->query('//w:p');
        foreach ($paragraphs as $para) {
            $tNodes   = $xpath->query('.//w:t', $para);
            $fullText = '';
            foreach ($tNodes as $t) {
                $fullText .= $t->nodeValue;
            }

            if (preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/u', $fullText, $matches)) {
                foreach ($matches[1] as $match) {
                    $key = trim(Str::snake($match));
                    if (!empty($key) && !isset($seen[$key])) {
                        $seen[$key]     = true;
                        $placeholders[] = [
                            'key'   => $key,
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
                    'key'   => $key,
                    'label' => Str::title(str_replace('_', ' ', $key)),
                ];
            }
        }
        return $placeholders;
    }

    public function replaceDummyWithPlaceholders(string $docxPath, array $replacements): string
    {
        if (empty($replacements)) {
            $tempOutput = tempnam(sys_get_temp_dir(), 'docx_noop_');
            copy($docxPath, $tempOutput);
            return $tempOutput;
        }

        foreach ($replacements as $item) {
            $find = trim($item['find'] ?? '');
            if (str_contains($find, '{') || str_contains($find, '}')) {
                throw new \Exception(
                    'Teks yang dipilih ("' . $find . '") mengandung tanda kurung kurawal. ' .
                    'Bersihkan dokumen dari placeholder lama sebelum membuat template baru.'
                );
            }
        }

        $zip        = new \ZipArchive();
        $tempOutput = tempnam(sys_get_temp_dir(), 'docx_mapped_');
        copy($docxPath, $tempOutput);

        if ($zip->open($tempOutput) !== true) {
            throw new \Exception('Gagal membuka file DOCX untuk diproses');
        }

        $xmlContent = $zip->getFromName('word/document.xml');
        if ($xmlContent === false) {
            $zip->close();
            throw new \Exception('word/document.xml tidak ditemukan');
        }

        Log::info('replaceDummyWithPlaceholders: mulai', ['count' => count($replacements)]);

        usort($replacements, fn($a, $b) => mb_strlen($b['find'], 'UTF-8') - mb_strlen($a['find'], 'UTF-8'));

        $xmlContent = $this->replaceDummyTextWithDom($xmlContent, $replacements);

        $zip->addFromString('word/document.xml', $xmlContent);
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

        $paragraphs = $xpath->query('//w:p');
        foreach ($paragraphs as $para) {
            foreach ($replacements as $item) {
                $find  = trim($item['find'] ?? '');
                $field = trim($item['replace'] ?? '');
                if (empty($find) || empty($field)) continue;

                $placeholder = '{{ ' . $field . ' }}';
                $this->replaceTextInParagraph($para, $xpath, $find, $placeholder);
            }
        }

        libxml_clear_errors();

        $xmlDecl = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
        return $xmlDecl . $dom->saveXML($dom->documentElement);
    }

    private function replaceTextInParagraph($para, \DOMXPath $xpath, string $find, string $replace): void
    {
        $tNodes = $xpath->query('.//w:t', $para);
        if ($tNodes->length === 0) return;

        $fullText = '';
        $nodeMap  = [];
        foreach ($tNodes as $t) {
            $start = mb_strlen($fullText, 'UTF-8');
            $text  = $t->nodeValue ?? '';
            $fullText .= $text;
            $nodeMap[] = [
                'node'   => $t,
                'start'  => $start,
                'length' => mb_strlen($text, 'UTF-8'),
            ];
        }

        $pos = mb_strpos($fullText, $find, 0, 'UTF-8');
        if ($pos === false) return;

        $matchStart = $pos;
        $matchEnd   = $pos + mb_strlen($find, 'UTF-8');
        $safeValue  = htmlspecialchars($replace, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $affected = [];
        foreach ($nodeMap as $p) {
            $nodeStart = $p['start'];
            $nodeEnd   = $nodeStart + $p['length'];
            if ($nodeStart < $matchEnd && $nodeEnd > $matchStart) {
                $affected[] = $p;
            }
        }
        if (empty($affected)) return;

        if (count($affected) === 1) {
            $node       = $affected[0]['node'];
            $localStart = $matchStart - $affected[0]['start'];
            $origText   = $node->nodeValue;
            $node->nodeValue =
                mb_substr($origText, 0, $localStart, 'UTF-8')
                . $safeValue
                . mb_substr($origText, $localStart + mb_strlen($find, 'UTF-8'), null, 'UTF-8');
        } else {
            $firstNode  = $affected[0]['node'];
            $localStart = $matchStart - $affected[0]['start'];
            $origText   = $firstNode->nodeValue;
            $firstNode->nodeValue =
                mb_substr($origText, 0, $localStart, 'UTF-8')
                . $safeValue;

            for ($i = 1; $i < count($affected); $i++) {
                $node      = $affected[$i]['node'];
                $nodeStart = $affected[$i]['start'];
                $nodeEnd   = $nodeStart + $affected[$i]['length'];
                $nodeText  = $node->nodeValue;

                if ($nodeEnd <= $matchEnd) {
                    $node->nodeValue = '';
                } else {
                    $cutLen = $matchEnd - $nodeStart;
                    $node->nodeValue = mb_substr($nodeText, $cutLen, null, 'UTF-8');
                }
            }
        }
    }

    private function replaceDummyTextWithString(string $xml, array $replacements): string
    {
        foreach ($replacements as $item) {
            $find        = trim($item['find'] ?? '');
            $field       = trim($item['replace'] ?? '');
            $placeholder = '{{ ' . $field . ' }}';
            if (empty($find)) continue;

            $escapedFind        = htmlspecialchars($find, ENT_XML1, 'UTF-8');
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
        if ($pos === false) return $subject;
        return substr($subject, 0, $pos) . $replace . substr($subject, $pos + strlen($search));
    }

    public function extractTextFromDocx(string $filePath): string
    {
        if (!file_exists($filePath)) return '';
        try {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                $xmlContent = $zip->getFromName('word/document.xml');
                $zip->close();
                if ($xmlContent !== false) {
                    $text = $this->extractPlainTextWithDom($xmlContent);
                    return preg_replace('/[ \t]+/', ' ', $text);
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

        $result     = '';
        $paragraphs = $xpath->query('//w:p');
        foreach ($paragraphs as $para) {
            $paraText = '';
            $tNodes   = $xpath->query('.//w:t', $para);
            foreach ($tNodes as $t) {
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
        if (file_exists($path)) return $path;

        $path = storage_path('app/' . $relativePath);
        if (file_exists($path)) return $path;

        return storage_path('app/public/' . $relativePath);
    }

    private function formatValue($value, ?string $fieldType, string $dateFormat): string
    {
        if (is_null($value) || $value === '') return '-';

        if ($fieldType === 'date' && !empty($value)) {
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
        $errors   = [];
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
            'valid'    => empty($errors),
            'errors'   => $errors,
            'warnings' => $warnings,
        ];
    }
}