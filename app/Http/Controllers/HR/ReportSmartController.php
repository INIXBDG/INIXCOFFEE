<?php

namespace App\Http\Controllers\HR;

use App\Models\ReportTemplate;
use App\Models\TemplatePlaceholder;
use App\Services\ReportGeneratorService;
use App\Services\ReportSmartScanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Alur baru: Upload -> Scan garis bawah -> Review tebakan ML -> Simpan.
 *
 * Extends ReportController supaya bisa memakai helper-nya
 * (method private di ReportController harus diubah menjadi protected).
 * ReportController lama TIDAK diubah perilakunya, jadi template lama aman.
 */
class ReportSmartController extends ReportController
{
    /** Tipe yang config-nya boleh diedit dari UI baru (tanpa menyentuh file DOCX). */
    protected const EDITABLE_TYPES = [
        'auto_date', 'formula', 'auth_field',
        'manual_text', 'manual_textarea', 'manual_date', 'manual_number', 'manual_select', 'manual_checkbox',
    ];

    public function __construct(ReportGeneratorService $generatorService, protected ReportSmartScanner $scanner)
    {
        parent::__construct($generatorService);
    }

    // =========================================================
    // SCAN (file baru yang di-upload)
    // =========================================================
    public function scan(Request $request): JsonResponse
    {
        $request->validate([
            'template_file' => 'required|file|mimes:docx|max:10240',
            'source_table' => ['required', 'string', Rule::in(array_keys($this->getAvailableTables()))],
        ]);

        try {
            $table = strtolower($request->input('source_table'));
            $columns = $this->getAllowedColumns()[$table] ?? [];

            $items = $this->scanner->scan($request->file('template_file')->getRealPath(), $table, $columns);

            return response()->json(['success' => true, 'data' => $items, 'columns' => $columns]);
        } catch (\Throwable $e) {
            Log::error('Smart scan gagal: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Gagal memindai dokumen: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================
    // DATA UNTUK MODE EDIT (field terpasang + sisa garis bawah)
    // =========================================================
    public function data(ReportTemplate $template): JsonResponse
    {
        $table = strtolower($template->source_table ?: 'karyawan');
        $columns = $this->getAllowedColumns()[$table] ?? [];

        $fields = $template->placeholders->sortBy('sort_order')->values()->map(fn ($p) => [
            'key' => $p->placeholder_key,
            'label' => $p->placeholder_label,
            'field_type' => $p->field_type,
            'is_manual' => (bool) $p->is_manual,
            'source_column' => $p->source_column,
            'config' => $p->config,
        ])->all();

        $blanks = [];
        if ($template->template_file_path) {
            $abs = $this->generatorService->getTemplateAbsolutePath($template->template_file_path);
            if (is_file($abs)) {
                $blanks = $this->scanner->scan($abs, $table, $columns, array_column($fields, 'key'));
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $template->id,
                'name' => $template->name,
                'code' => $template->code,
                'category' => $template->category,
                'source_table' => $table,
                'columns' => $columns,
                'fields' => $fields,
                'blanks' => $blanks,
                'template_url' => $template->template_file_path ? asset('storage/' . $template->template_file_path) : null,
            ],
        ]);
    }

    // =========================================================
    // SIMPAN TEMPLATE BARU
    // =========================================================
    public function save(Request $request): JsonResponse
    {
        $v = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:report_templates,code',
            'category' => 'required|string|max:100',
            'source_table' => ['required', 'string', Rule::in(array_keys($this->getAvailableTables()))],
            'template_file' => 'required|file|mimes:docx|max:10240',
            'items' => 'nullable|string',
        ], [
            'code.unique' => 'Kode Template sudah dipakai template lain.',
        ]);

        $table = strtolower($v['source_table']);
        $columns = $this->getAllowedColumns()[$table] ?? [];

        try {
            $items = $this->parseItems($v['items'] ?? '[]', $columns);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }

        $storedPath = null;

        try {
            $processed = $this->generatorService->replaceDummyWithPlaceholders(
                $request->file('template_file')->getRealPath(),
                $this->toReplacements($items)
            );

            $filename = 'template_' . time() . '_' . Str::random(10) . '.docx';
            $storedPath = Storage::disk('public')->putFileAs('report_templates', $processed, $filename);
            @unlink($processed);

            if (!$storedPath) {
                throw new \RuntimeException('Gagal menyimpan file ke storage.');
            }

            $extracted = $this->generatorService->extractPlaceholdersFromDocx(
                $this->generatorService->getTemplateAbsolutePath($storedPath)
            );

            $template = DB::transaction(function () use ($v, $table, $columns, $items, $storedPath, $extracted) {
                $available = array_keys($columns);
                foreach ($items as $it) {
                    if (!in_array($it['key'], $available, true)) {
                        $available[] = $it['key'];
                    }
                }

                $tpl = ReportTemplate::create([
                    'name' => $v['name'],
                    'code' => $v['code'],
                    'category' => $v['category'],
                    'source_table' => $table,
                    'template_file_path' => $storedPath,
                    'description' => null,
                    'available_fields' => $available,
                    'created_by' => auth()->id(),
                    'is_active' => true,
                ]);

                $this->createPlaceholderRows($tpl, $extracted, $items, $columns, [], 0);

                return $tpl;
            });

            $this->scanner->learn($items);

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil dibuat dengan ' . count($extracted) . ' placeholder.',
                'data' => ['id' => $template->id, 'name' => $template->name],
            ], 201);
        } catch (\Throwable $e) {
            if ($storedPath) {
                Storage::disk('public')->delete($storedPath);
            }
            Log::error('Smart save gagal: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['success' => false, 'message' => 'Gagal menyimpan template: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================
    // UPDATE TEMPLATE YANG SUDAH ADA (AMAN)
    //  - field lama: hanya label / kolom sumber / config yang berubah (DOCX tidak disentuh)
    //  - garis bawah baru: dipetakan ke file DOCX tersimpan, field lama tidak dihapus
    // =========================================================
    public function updateSmart(Request $request, ReportTemplate $template): JsonResponse
    {
        $v = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:50', Rule::unique('report_templates', 'code')->ignore($template->id)],
            'category' => 'nullable|string|max:100',
            'fields' => 'nullable|string',
            'items' => 'nullable|string',
        ], [
            'code.unique' => 'Kode Template sudah dipakai template lain.',
        ]);

        $table = strtolower($template->source_table ?: 'karyawan');
        $columns = $this->getAllowedColumns()[$table] ?? [];
        $existing = $template->placeholders()->get();
        $existingKeys = $existing->pluck('placeholder_key')->all();

        $newPath = null;
        $oldPath = $template->template_file_path;

        try {
            $fields = json_decode($v['fields'] ?? '[]', true);
            if (!is_array($fields)) {
                throw new \InvalidArgumentException('Format data field tidak valid.');
            }
            $items = $this->parseItems($v['items'] ?? '[]', $columns, $existingKeys);

            $extracted = [];
            if (!empty($items)) {
                $abs = $this->generatorService->getTemplateAbsolutePath((string) $oldPath);
                if (!is_file($abs)) {
                    throw new \RuntimeException('File template lama tidak ditemukan.');
                }

                $processed = $this->generatorService->replaceDummyWithPlaceholders($abs, $this->toReplacements($items));
                $filename = 'template_' . time() . '_' . Str::random(10) . '.docx';
                $newPath = Storage::disk('public')->putFileAs('report_templates', $processed, $filename);
                @unlink($processed);

                if (!$newPath) {
                    throw new \RuntimeException('Gagal menyimpan file hasil proses.');
                }

                $extracted = $this->generatorService->extractPlaceholdersFromDocx(
                    $this->generatorService->getTemplateAbsolutePath($newPath)
                );
            }

            DB::transaction(function () use ($template, $v, $fields, $items, $extracted, $existing, $existingKeys, $columns, $newPath) {
                $meta = [
                    'name' => $v['name'],
                    'code' => $v['code'],
                    'category' => $v['category'] ?? $template->category,
                ];

                if ($newPath) {
                    $available = (array) ($template->available_fields ?? array_keys($columns));
                    foreach ($items as $it) {
                        if (!in_array($it['key'], $available, true)) {
                            $available[] = $it['key'];
                        }
                    }
                    $meta['template_file_path'] = $newPath;
                    $meta['available_fields'] = array_values($available);
                }

                $template->update($meta);

                // --- field lama: update in-place ---
                foreach ($fields as $f) {
                    $ph = $existing->firstWhere('placeholder_key', $f['key'] ?? '');
                    if (!$ph) {
                        continue;
                    }

                    $data = [];
                    if (isset($f['label']) && trim((string) $f['label']) !== '') {
                        $data['placeholder_label'] = mb_substr(trim($f['label']), 0, 255);
                    }

                    $isDb = !$ph->is_manual && $ph->field_type === 'text';

                    if ($isDb) {
                        if (!empty($f['source_column']) && isset($columns[$f['source_column']])) {
                            $data['source_column'] = $f['source_column'];
                        }
                    } elseif (in_array($ph->field_type, self::EDITABLE_TYPES, true) && is_array($f['config'] ?? null)) {
                        $cfg = $this->cleanConfig($ph->field_type, array_merge((array) $ph->config, $f['config']));

                        if ($ph->field_type === 'formula' && empty($cfg['template'])) {
                            throw new \InvalidArgumentException('Template rumus tidak boleh kosong.');
                        }
                        if ($ph->field_type === 'manual_select' && empty($cfg['options'])) {
                            throw new \InvalidArgumentException('Opsi dropdown tidak boleh kosong.');
                        }

                        $data['config'] = $cfg;
                        $data['default_value'] = $this->extractDefaultValue($ph->field_type, $cfg);
                        $data['options'] = $this->extractOptions($ph->field_type, $cfg);
                    }

                    if ($data) {
                        $ph->update($data);
                    }
                }

                // --- garis bawah baru: tambah field, tidak menghapus yang lama ---
                if (!empty($items)) {
                    $max = (int) $template->placeholders()->max('sort_order');
                    $this->createPlaceholderRows($template, $extracted, $items, $columns, $existingKeys, $max + 1);
                }
            });

            if ($newPath && $oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            $this->scanner->learn($items);

            return response()->json(['success' => true, 'message' => 'Template berhasil diperbarui.']);
        } catch (\InvalidArgumentException $e) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }
            Log::error('Smart update gagal: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['success' => false, 'message' => 'Gagal memperbarui template: ' . $e->getMessage()], 500);
        }
    }

    // =========================================================
    // HELPER
    // =========================================================

    /**
     * Validasi & normalisasi daftar isian dari UI.
     * Item: {find, blank, key, type, config, label, context}
     */
    protected function parseItems(string $json, array $columns, array $reservedKeys = []): array
    {
        $raw = json_decode($json, true);
        if (!is_array($raw)) {
            throw new \InvalidArgumentException('Format data mapping tidak valid.');
        }

        $items = [];
        $typeByKey = [];

        foreach ($raw as $r) {
            $find = trim((string) ($r['find'] ?? ''));
            $blank = trim((string) ($r['blank'] ?? ''));
            $key = trim((string) ($r['key'] ?? ''));
            $type = (string) ($r['type'] ?? '');

            if ($find === '' || $blank === '' || $key === '' || !str_contains($find, $blank)) {
                continue;
            }

            $config = null;

            if ($type === 'db') {
                if (!isset($columns[$key])) {
                    throw new \InvalidArgumentException("Kolom database tidak dikenal: {$key}");
                }
                $label = $columns[$key];
            } else {
                if (!in_array($type, self::ALL_FIELD_TYPES, true)) {
                    throw new \InvalidArgumentException("Tipe field tidak valid: {$type}");
                }
                if (!preg_match('/^[a-z0-9_]+$/', $key)) {
                    throw new \InvalidArgumentException("Key placeholder tidak valid: {$key}");
                }
                if (isset($columns[$key]) || in_array($key, $reservedKeys, true)) {
                    throw new \InvalidArgumentException("Key \"{$key}\" bentrok dengan field lain. Muat ulang halaman lalu coba lagi.");
                }

                $config = $this->cleanConfig($type, is_array($r['config'] ?? null) ? $r['config'] : []);

                if ($type === 'formula' && empty($config['template'])) {
                    throw new \InvalidArgumentException('Template rumus tidak boleh kosong.');
                }
                if ($type === 'manual_select' && empty($config['options'])) {
                    throw new \InvalidArgumentException('Opsi dropdown tidak boleh kosong.');
                }

                $label = trim((string) ($r['label'] ?? '')) ?: ($config['label'] ?? $this->generateLabelFromKey($key));
                $config['label'] = $label;
            }

            if (isset($typeByKey[$key]) && ($typeByKey[$key] === 'db') !== ($type === 'db')) {
                throw new \InvalidArgumentException("Key \"{$key}\" dipakai oleh dua jenis isian berbeda.");
            }
            $typeByKey[$key] = $type;

            $items[] = [
                'find' => $find,
                'blank' => $blank,
                'key' => $key,
                'type' => $type,
                'config' => $config,
                'label' => $label,
                'context' => (string) ($r['context'] ?? ''),
            ];
        }

        return $items;
    }

    protected function toReplacements(array $items): array
    {
        return array_map(fn ($it) => [
            'find' => $it['find'],
            'replace' => $it['key'],
            'blank' => $it['blank'],
        ], $items);
    }

    /**
     * Buat baris TemplatePlaceholder dari tag {{ }} yang ada di DOCX hasil proses.
     */
    protected function createPlaceholderRows(ReportTemplate $tpl, array $extracted, array $items, array $columns, array $skipKeys, int $sort): int
    {
        $special = [];
        foreach ($items as $it) {
            if ($it['type'] !== 'db') {
                $special[$it['key']] ??= $it;
            }
        }

        $n = 0;
        foreach ($extracted as $ph) {
            $key = $ph['key'];
            if (in_array($key, $skipKeys, true)) {
                continue;
            }

            if (isset($special[$key])) {
                $it = $special[$key];
                $type = $it['type'];
                $cfg = $it['config'];

                TemplatePlaceholder::create([
                    'template_id' => $tpl->id,
                    'placeholder_key' => $key,
                    'placeholder_label' => $it['label'],
                    'field_type' => $type,
                    'is_manual' => $this->isManualFieldType($type),
                    'source_column' => null,
                    'config' => $cfg,
                    'default_value' => $this->extractDefaultValue($type, $cfg),
                    'options' => $this->extractOptions($type, $cfg),
                    'sort_order' => $sort++,
                ]);
            } else {
                TemplatePlaceholder::create([
                    'template_id' => $tpl->id,
                    'placeholder_key' => $key,
                    'placeholder_label' => $columns[$key] ?? $this->generateLabelFromKey($key),
                    'field_type' => 'text',
                    'is_manual' => false,
                    'source_column' => $key,
                    'sort_order' => $sort++,
                ]);
            }
            $n++;
        }

        return $n;
    }
}