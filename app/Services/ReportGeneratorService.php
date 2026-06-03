<?php

namespace App\Services;

use App\Models\ReportTemplate;
use App\Models\TemplatePlaceholder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ReportGeneratorService
{
    protected array $allowedModels = [
        'karyawan' => \App\Models\karyawan::class,
        'kegiatan' => \App\Models\Kegiatan::class,
    ];

    protected array $allowedFieldTypes = ['text', 'textarea', 'date', 'select', 'checkbox', 'number', 'currency'];

    public function getTableColumns(string $tableName): array
    {
        if (!DB::getSchemaBuilder()->hasTable($tableName)) {
            return [];
        }
        $columns = DB::getSchemaBuilder()->getColumnListing($tableName);
        $excluded = ['password', 'remember_token', 'deleted_at', 'created_at', 'updated_at'];
        return array_values(array_filter($columns, fn($col) => !in_array($col, $excluded)));
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
        return $data ? $this->formatSourceData($data->toArray(), $sourceType) : [];
    }

    private function getRelations(?string $sourceType): array
    {
        return [
            'karyawan' => [],
            'kegiatan' => [],
        ][$sourceType] ?? [];
    }

    private function formatSourceData(array $data, string $sourceType): array
    {
        $formatted = $data;
        foreach ($formatted as $key => $value) {
            if ($value instanceof \DateTimeInterface) {
                $formatted[$key] = $value->format('d F Y');
            }
            if (is_null($value)) {
                $formatted[$key] = '-';
            }
        }
        return $formatted;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // GENERATE REPORT — PENDEKATAN DOM-BASED (AMAN, TIDAK MERUSAK LAYOUT)
    // ═════════════════════════════════════════════════════════════════════════

    public function generateDocxReport(ReportTemplate $template, array $sourceData, array $manualInputs): string
    {
        $templatePath = $this->getTemplateAbsolutePath($template->template_file_path);

        if (!file_exists($templatePath)) {
            throw new \Exception('Template file tidak ditemukan di: ' . $templatePath);
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'docx_gen_');
        copy($templatePath, $tempFile);

        $zip = new \ZipArchive();
        if ($zip->open($tempFile) !== true) {
            throw new \Exception('Gagal membuka file DOCX template');
        }

        // ✅ BACA SEMUA ENTRIES — JANGAN HILANGKAN SATU PUN
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $entries[$filename] = $zip->getFromName($filename);
        }
        $zip->close();

        // ✅ Cari semua file XML yang mungkin berisi placeholder
        $xmlParts = ['word/document.xml'];
        foreach (array_keys($entries) as $file) {
            if (preg_match('#^word/(header|footer)\d*\.xml$#', $file)) {
                $xmlParts[] = $file;
            }
        }

        $settings = $template->settings ?? [];
        $dateFormat = $settings['date_format'] ?? 'd F Y';

        // Bangun mapping: key => value
        $replacements = [];
        foreach ($template->placeholders as $placeholder) {
            $key = $placeholder->placeholder_key;
            $value = $this->resolveValue($placeholder, $sourceData, $manualInputs, $dateFormat);
            $replacements[$key] = (string) $value;
        }

        Log::info('generateDocxReport: replacements', ['keys' => array_keys($replacements)]);

        // ✅ Proses setiap file XML dengan DOM-based replacement
        foreach ($xmlParts as $part) {
            if (!isset($entries[$part])) continue;

            $xml = $entries[$part];
            $xml = $this->replacePlaceholdersInXml($xml, $replacements);
            $entries[$part] = $xml;
        }

        // ✅ TULIS ULANG SEMUA ENTRIES — struktur DOCX tetap utuh
        $newZip = new \ZipArchive();
        if ($newZip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Gagal membuat file DOCX output');
        }

        foreach ($entries as $filename => $content) {
            $newZip->addFromString($filename, $content);
        }
        $newZip->close();

        // Simpan ke storage
        $filename   = 'report_' . Str::slug($template->name) . '_' . time() . '_' . Str::random(8) . '.docx';
        $outputPath = 'reports/generated/' . date('Y/m') . '/' . $filename;

        Storage::disk('public')->makeDirectory(dirname($outputPath));
        Storage::disk('public')->put($outputPath, file_get_contents($tempFile));
        @unlink($tempFile);

        return $outputPath;
    }

    // ═════════════════════════════════════════════════════════════════════════
    // INTI PENGGANTIAN: DOM-BASED, HANDLE PLACEHOLDER YANG TER-SPLIT
    // ═════════════════════════════════════════════════════════════════════════

    private function replacePlaceholdersInXml(string $xml, array $replacements): string
    {
        if (empty($replacements)) return $xml;

        // Coba pendekatan DOM dulu (paling aman, pertahankan formatting)
        $result = $this->replaceWithDom($xml, $replacements);
        if ($result !== null) {
            return $result;
        }

        // Fallback: string replacement jika DOM gagal parse
        Log::warning('DOM parse gagal, fallback ke string replacement');
        return $this->replaceWithStringFallback($xml, $replacements);
    }

    private function replaceWithDom(string $xml, array $replacements): ?string
    {
        libxml_use_internal_errors(true);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;

        // Pastikan ada XML declaration
        if (stripos($xml, '<?xml') === false) {
            $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n" . $xml;
        }

        if (!@$dom->loadXML($xml)) {
            libxml_clear_errors();
            return null;
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        // Proses per paragraph — ini kunci agar placeholder yang ter-split tetap terdeteksi
        $paragraphs = $xpath->query('//w:p');
        foreach ($paragraphs as $para) {
            foreach ($replacements as $key => $value) {
                $this->replacePlaceholderInParagraph($para, $xpath, $key, $value);
            }
        }

        // ✅ Simpan HANYA documentElement — tanpa XML declaration baru
        // Ini penting agar namespace & struktur asli Word tidak rusak
        $result = $dom->saveXML($dom->documentElement);

        libxml_clear_errors();
        return $result;
    }

    /**
     * Ganti {{ key }} dalam satu paragraph, meskipun placeholder ter-split
     * di beberapa <w:t> (misal: "<w:t>{{ </w:t><w:t>nama</w:t><w:t> }}</w:t>")
     */
    private function replacePlaceholderInParagraph($para, \DOMXPath $xpath, string $key, string $value): void
    {
        $tNodes = $xpath->query('.//w:t', $para);
        if ($tNodes->length === 0) return;

        // Gabungkan semua text node untuk mencari placeholder
        $fullText = '';
        $nodeMap  = []; // posisi setiap text node di fullText
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

        // Cari placeholder: {{ key }} dengan spasi fleksibel
        $pattern = '/\{\{\s*' . preg_quote($key, '/') . '\s*\}\}/u';
        if (!preg_match_all($pattern, $fullText, $matches, PREG_OFFSET_CAPTURE)) {
            return;
        }

        // Proses dari BELAKANG agar posisi tidak bergeser
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
            // Cari text node mana saja yang terpengaruh
            $affected = [];
            foreach ($nodeMap as $pos) {
                $nodeStart = $pos['start'];
                $nodeEnd   = $nodeStart + $pos['length'];
                if ($nodeStart < $match['end'] && $nodeEnd > $match['start']) {
                    $affected[] = $pos;
                }
            }
            if (empty($affected)) continue;

            // Escape value untuk XML
            $safeValue = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

            if (count($affected) === 1) {
                // ✅ Kasus sederhana: placeholder ada di satu <w:t>
                $node       = $affected[0]['node'];
                $localStart = $match['start'] - $affected[0]['start'];
                $matchLen   = mb_strlen($match['text'], 'UTF-8');
                $origText   = $node->nodeValue;

                $node->nodeValue =
                    mb_substr($origText, 0, $localStart, 'UTF-8')
                    . $safeValue
                    . mb_substr($origText, $localStart + $matchLen, null, 'UTF-8');
            } else {
                // ✅ Kasus SPLIT: placeholder tersebar di beberapa <w:t>
                // Letakkan nilai di <w:t> pertama, kosongkan yang lain
                $firstNode  = $affected[0]['node'];
                $localStart = $match['start'] - $affected[0]['start'];
                $origText   = $firstNode->nodeValue;

                $firstNode->nodeValue =
                    mb_substr($origText, 0, $localStart, 'UTF-8')
                    . $safeValue;

                // Kosongkan / potong node berikutnya
                for ($i = 1; $i < count($affected); $i++) {
                    $node      = $affected[$i]['node'];
                    $nodeStart = $affected[$i]['start'];
                    $nodeEnd   = $nodeStart + $affected[$i]['length'];
                    $nodeText  = $node->nodeValue;

                    if ($nodeEnd <= $match['end']) {
                        // Seluruhnya di dalam placeholder → kosongkan
                        $node->nodeValue = '';
                    } else {
                        // Hanya sebagian → potong bagian yang termasuk placeholder
                        $cutLen = $match['end'] - $nodeStart;
                        $node->nodeValue = mb_substr($nodeText, $cutLen, null, 'UTF-8');
                    }
                }
            }
        }
    }

    /**
     * Fallback: string replacement (jika DOMDocument gagal parse XML)
     */
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

    // ═════════════════════════════════════════════════════════════════════════
    // EXTRACT PLACEHOLDERS — juga pakai DOM agar akurat
    // ═════════════════════════════════════════════════════════════════════════

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

        // ✅ Gunakan DOM untuk gabungkan semua text node per paragraph
        // sehingga placeholder yang ter-split tetap terbaca
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
            // Fallback ke regex
            return $this->extractPlaceholdersWithRegex($xml);
        }

        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

        $seen         = [];
        $placeholders = [];

        // Gabungkan text per paragraph agar placeholder ter-split terbaca
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

    // ═════════════════════════════════════════════════════════════════════════
    // REPLACE DUMMY → PLACEHOLDER
    // Pakai pendekatan yang sama: cari di level paragraph, handle split text
    // ═════════════════════════════════════════════════════════════════════════

    public function replaceDummyWithPlaceholders(string $docxPath, array $replacements): string
    {
        if (empty($replacements)) {
            $tempOutput = tempnam(sys_get_temp_dir(), 'docx_noop_');
            copy($docxPath, $tempOutput);
            return $tempOutput;
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

        // ✅ Urutkan dari yang TERPANJANG agar tidak ada partial match
        usort($replacements, fn($a, $b) => mb_strlen($b['find'], 'UTF-8') - mb_strlen($a['find'], 'UTF-8'));

        // ✅ Gunakan DOM-based replacement untuk dummy text juga
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
            // Fallback ke string replacement
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

        $result = $dom->saveXML($dom->documentElement);
        libxml_clear_errors();
        return $result;
    }

    /**
     * Ganti teks dummy dalam satu paragraph, meskipun teks ter-split di beberapa <w:t>
     */
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

        // Cari dummy text (case-sensitive, first occurrence only)
        $pos = mb_strpos($fullText, $find, 0, 'UTF-8');
        if ($pos === false) return;

        $matchStart = $pos;
        $matchEnd   = $pos + mb_strlen($find, 'UTF-8');
        $safeValue  = htmlspecialchars($replace, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        // Cari text node yang terpengaruh
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

    // ═════════════════════════════════════════════════════════════════════════
    // EXTRACT PLAIN TEXT (untuk preview)
    // ═════════════════════════════════════════════════════════════════════════

    public function extractTextFromDocx(string $filePath): string
    {
        if (!file_exists($filePath)) return '';
        try {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                $xmlContent = $zip->getFromName('word/document.xml');
                $zip->close();
                if ($xmlContent !== false) {
                    // ✅ Gunakan DOM untuk extract text — tidak merusak XML
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

    // ═════════════════════════════════════════════════════════════════════════
    // HELPERS
    // ═════════════════════════════════════════════════════════════════════════

    public function getTemplateAbsolutePath(string $relativePath): string
    {
        $relativePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, ltrim($relativePath, '/\\'));
        $path = storage_path('app/public/' . $relativePath);
        if (file_exists($path)) return $path;

        $path = storage_path('app/' . $relativePath);
        if (file_exists($path)) return $path;

        return storage_path('app/public/' . $relativePath);
    }

    private function resolveValue(TemplatePlaceholder $placeholder, array $sourceData, array $manualInputs, string $dateFormat): string
    {
        if ($placeholder->is_manual) {
            $value = $manualInputs[$placeholder->placeholder_key] ?? ($placeholder->default_value ?? '');
        } else {
            $column = $placeholder->source_column;
            $value  = data_get($sourceData, $column, data_get($sourceData, Str::snake($column), ''));
        }
        return $this->formatValue($value, $placeholder->field_type, $dateFormat);
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