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
 
    // ─────────────────────────────────────────────────────────────────────────
    // PLACEHOLDER FORMAT: {{ key }} di dalam DOCX XML
    // PhpWord TemplateProcessor TIDAK digunakan — kita handle sendiri via ZipArchive
    // ─────────────────────────────────────────────────────────────────────────
 
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

        // Daftar bagian XML yang mungkin berisi placeholder
        $xmlParts = ['word/document.xml', 'word/header1.xml', 'word/footer1.xml'];

        $settings = $template->settings ?? [];
        $dateFormat = $settings['date_format'] ?? 'd F Y';

        // Simpan semua isi ZIP ke memory sementara
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $entries[$filename] = $zip->getFromName($filename);
        }
        $zip->close(); // Tutup dulu agar bisa overwrite

        // Proses hanya bagian yang relevan
        foreach ($xmlParts as $part) {
            if (!isset($entries[$part])) continue;

            $xml = $entries[$part];
            $xml = $this->normalizeXmlRuns($xml);

            foreach ($template->placeholders as $placeholder) {
                $key   = $placeholder->placeholder_key;
                $value = $this->resolveValue($placeholder, $sourceData, $manualInputs, $dateFormat);

                $escapedPlaceholder = htmlspecialchars('{{ ' . $key . ' }}', ENT_XML1, 'UTF-8');
                $replacement = htmlspecialchars((string) $value, ENT_XML1, 'UTF-8');

                $xml = str_replace($escapedPlaceholder, $replacement, $xml);
                // Fallback tanpa spasi
                $altPlaceholder = htmlspecialchars('{{' . $key . '}}', ENT_XML1, 'UTF-8');
                $xml = str_replace($altPlaceholder, $replacement, $xml);
            }

            $entries[$part] = $xml;
        }

        // Tulis ulang semua file ke ZIP baru
        $newZip = new \ZipArchive();
        if ($newZip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception('Gagal membuat file DOCX output');
        }

        foreach ($entries as $filename => $content) {
            $newZip->addFromString($filename, $content);
        }

        $newZip->close();

        // Simpan ke storage
        $filename = 'report_' . Str::slug($template->name) . '_' . time() . '_' . Str::random(8) . '.docx';
        $outputPath = 'reports/generated/' . date('Y/m') . '/' . $filename;

        Storage::disk('public')->makeDirectory(dirname($outputPath));
        Storage::disk('public')->put($outputPath, file_get_contents($tempFile));
        unlink($tempFile);

        return $outputPath;
    }
 
    public function extractPlaceholdersFromDocx(string $filePath): array
    {
        if (!file_exists($filePath)) {
            Log::error('extractPlaceholdersFromDocx: file tidak ditemukan - ' . $filePath);
            return [];
        }
 
        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            Log::error('extractPlaceholdersFromDocx: gagal membuka zip');
            return [];
        }
 
        $xmlContent = $zip->getFromName('word/document.xml');
        $zip->close();
 
        if ($xmlContent === false) {
            Log::error('extractPlaceholdersFromDocx: word/document.xml tidak ditemukan');
            return [];
        }
 
        // Normalisasi run agar placeholder yang tersplit tergabung
        $xmlContent = $this->normalizeXmlRuns($xmlContent);
 
        // Cari {{ key }} di XML — mungkin ter-HTML-encode
        // Pola: {{ key }} atau &amp;#x7B;&amp;#x7B; key &amp;#x7D;&amp;#x7D; atau {{ key }}
        $patterns = [
            '/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/',                         // plain text (setelah strip_tags)
            '/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/',                         // sama
        ];
 
        // Decode XML entities dulu, lalu cari
        $decoded = html_entity_decode($xmlContent, ENT_XML1 | ENT_HTML5, 'UTF-8');
 
        $seen         = [];
        $placeholders = [];
 
        preg_match_all('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', $decoded, $matches);
 
        foreach ($matches[1] ?? [] as $match) {
            $key = trim(Str::snake($match));
            if (!isset($seen[$key]) && !empty($key)) {
                $label          = Str::title(str_replace('_', ' ', $key));
                $placeholders[] = ['key' => $key, 'label' => $label];
                $seen[$key]     = true;
            }
        }
 
        Log::info('extractPlaceholdersFromDocx result', [
            'file'   => basename($filePath),
            'found'  => count($placeholders),
            'keys'   => array_column($placeholders, 'key'),
        ]);
 
        return $placeholders;
    }
 
    // ─────────────────────────────────────────────────────────────────────────
    // REPLACE DUMMY → PLACEHOLDER: proses file asli, ganti teks dummy
    // dengan {{ field_name }}, simpan sebagai template baru
    // ─────────────────────────────────────────────────────────────────────────
 
    public function replaceDummyWithPlaceholders(string $docxPath, array $replacements): string
    {
        if (empty($replacements)) {
            // Tidak ada mapping — kembalikan file asli
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
            throw new \Exception('word/document.xml tidak ditemukan di dalam DOCX');
        }
 
        Log::info('replaceDummyWithPlaceholders: mulai', ['replacements' => count($replacements)]);
 
        // Normalisasi XML agar teks tidak tersplit antar run
        $xmlContent = $this->normalizeXmlRuns($xmlContent);
 
        // Urutkan dari teks TERPANJANG ke terpendek (hindari partial match)
        usort($replacements, fn($a, $b) => strlen($b['find']) - strlen($a['find']));
 
        foreach ($replacements as $item) {
            $find  = trim($item['find'] ?? '');
            $field = trim($item['replace'] ?? '');
 
            if (empty($find) || empty($field)) continue;
 
            // Placeholder yang akan dimasukkan ke XML
            $placeholder       = '{{ ' . $field . ' }}';
            $escapedFind       = htmlspecialchars($find, ENT_XML1, 'UTF-8');
            $escapedPlaceholder = htmlspecialchars($placeholder, ENT_XML1, 'UTF-8');
 
            if (strpos($xmlContent, $escapedFind) !== false) {
                // Exact match setelah HTML-escape
                $xmlContent = $this->replaceOnce($xmlContent, $escapedFind, $escapedPlaceholder);
                Log::info('✓ Replaced (escaped exact)', ['find' => $find, 'field' => $field]);
            } elseif (strpos($xmlContent, $find) !== false) {
                // Exact match tanpa escape (jarang tapi mungkin)
                $xmlContent = $this->replaceOnce($xmlContent, $find, $escapedPlaceholder);
                Log::info('✓ Replaced (plain exact)', ['find' => $find, 'field' => $field]);
            } else {
                Log::warning('✗ NOT FOUND', ['find' => $find]);
            }
        }
 
        $zip->addFromString('word/document.xml', $xmlContent);
        $zip->close();
 
        Log::info('replaceDummyWithPlaceholders: selesai', ['output' => $tempOutput]);
 
        return $tempOutput;
    }
 
    private function replaceOnce(string $subject, string $search, string $replace): string
    {
        $pos = strpos($subject, $search);
        if ($pos === false) return $subject;
        return substr($subject, 0, $pos) . $replace . substr($subject, $pos + strlen($search));
    }
 
    // ─────────────────────────────────────────────────────────────────────────
    // NORMALISASI XML: gabungkan <w:t> yang tersplit dalam paragraph
    // agar teks "Budi Santoso" tidak terpecah menjadi "Bu","di"," S","antoso"
    // ─────────────────────────────────────────────────────────────────────────
 
    private function normalizeXmlRuns(string $xml): string
    {
        // Strategy: untuk setiap <w:p>, gabungkan semua teks dalam run yang
        // memiliki formatting sama menjadi satu run.
        // Ini penting karena Word sering memecah teks tanpa alasan visual yang jelas.
 
        return preg_replace_callback(
            '/<w:p\b[^>]*>.*?<\/w:p>/su',
            function ($paraMatch) {
                return $this->normalizeParagraph($paraMatch[0]);
            },
            $xml
        );
    }
 
    private function normalizeParagraph(string $para): string
    {
        // Ambil semua <w:r> dalam paragraph ini
        preg_match_all('/<w:r\b[^>]*>.*?<\/w:r>/su', $para, $runs);
 
        if (empty($runs[0])) return $para;
 
        // Untuk setiap run, extract rPr + text
        $parsed = [];
        foreach ($runs[0] as $run) {
            $rPr = '';
            if (preg_match('/<w:rPr\b[^>]*>.*?<\/w:rPr>/su', $run, $m)) {
                $rPr = $m[0];
            }
 
            $texts = [];
            preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/su', $run, $tMatches);
            foreach ($tMatches[1] as $t) {
                $texts[] = $t;
            }
 
            if (!empty($texts)) {
                $parsed[] = ['rPr' => $rPr, 'text' => implode('', $texts)];
            }
        }
 
        if (empty($parsed)) return $para;
 
        // Gabungkan run yang berturutan dengan rPr sama
        $merged = [];
        foreach ($parsed as $item) {
            if (!empty($merged) && end($merged)['rPr'] === $item['rPr']) {
                $merged[count($merged) - 1]['text'] .= $item['text'];
            } else {
                $merged[] = $item;
            }
        }
 
        // Bangun run baru
        $newRuns = '';
        foreach ($merged as $item) {
            $text    = $item['text'];
            $rPr     = $item['rPr'];
            $preserve = strpos($text, ' ') !== false ? ' xml:space="preserve"' : '';
            $newRuns .= '<w:r>' . $rPr . '<w:t' . $preserve . '>' . $text . '</w:t></w:r>';
        }
 
        // Ganti semua run lama dengan run baru
        $result = preg_replace('/<w:r\b[^>]*>.*?<\/w:r>/su', '', $para);
 
        // Sisipkan run baru sebelum </w:p>
        $result = preg_replace('/<\/w:p>/su', $newRuns . '</w:p>', $result, 1);
 
        return $result;
    }
 
    // ─────────────────────────────────────────────────────────────────────────
    // EXTRACT PLAIN TEXT (untuk preview di frontend)
    // ─────────────────────────────────────────────────────────────────────────
 
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
                    // Normalisasi dulu agar teks tidak tersplit
                    $xmlContent = $this->normalizeXmlRuns($xmlContent);
                    $text = strip_tags(html_entity_decode($xmlContent, ENT_XML1, 'UTF-8'));
                    return preg_replace('/[ \t]+/', ' ', $text);
                }
            }
            return '';
        } catch (\Exception $e) {
            Log::warning('Failed to extract DOCX text: ' . $e->getMessage());
            return '';
        }
    }
 
    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────
 
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
        if (is_null($value) || $value === '') {
            return '-';
        }
 
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
