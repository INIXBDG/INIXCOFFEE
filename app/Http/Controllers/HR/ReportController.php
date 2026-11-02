<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\karyawan;
use App\Models\Pelamar;
use App\Models\ReportGeneration;
use App\Models\ReportTemplate;
use App\Models\TemplatePlaceholder;
use App\Services\ReportGeneratorService;
use App\Services\ReportSmartScanner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    protected ReportGeneratorService $generatorService;

    protected const MANUAL_INPUT_TYPES = [
        'manual_text',
        'manual_textarea',
        'manual_date',
        'manual_number',
        'manual_select',
        'manual_checkbox',
    ];

    protected const ALL_FIELD_TYPES = [
        'text', 'textarea', 'date', 'select', 'checkbox', 'number', 'currency',
        'auto_date', 'formula', 'auth_field', 'relation_single',
        'loop_manual', 'loop_relation',
        'manual_text', 'manual_textarea', 'manual_date', 'manual_number', 'manual_select', 'manual_checkbox',
    ];

    public function __construct(ReportGeneratorService $generatorService)
    {
        $this->generatorService = $generatorService;
        $this->middleware('auth');
    }

    public function index()
    {
        $templates = ReportTemplate::where('is_active', true)
            ->with('creator')
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        $allowedColumns = $this->getAllowedColumns();

        return view('HR.template.index', compact('templates', 'allowedColumns'));
    }

    public function create()
    {
        return redirect()->route('HR.reports.index');
    }

    public function history()
    {
        return redirect()->route('HR.reports.index');
    }

    public function saveWithMapping(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:report_templates,code',
            'category' => 'required|string',
            'source_table' => ['required', 'string', Rule::in(array_keys($this->getAvailableTables()))],
            'template_file' => 'required|file|mimes:docx,doc|max:10240',
            'description' => 'nullable|string|max:1000',
            'replacements' => 'nullable|array',
            'special_fields' => 'nullable|string',
        ]);

        try {
            $file = $request->file('template_file');

            if (!$file || !$file->isValid()) {
                throw new \Exception('File template tidak valid.');
            }

            $tempPath = $file->getRealPath();

            if (!$tempPath || !file_exists($tempPath)) {
                throw new \Exception('File template tidak ditemukan.');
            }

            $replacements = [];

            foreach ((array) ($validated['replacements'] ?? []) as $item) {
                $find = trim($item['find'] ?? '');
                $field = trim($item['replace'] ?? '');
                $blank = trim($item['blank'] ?? '');

                if ($find === '' || $field === '') {
                    continue;
                }

                $replacements[md5($find . '|' . $field)] = ['find' => $find, 'replace' => $field, 'blank' => $blank];
            }

            $specialFields = [];

            if (!empty($validated['special_fields'])) {
                $decoded = json_decode($validated['special_fields'], true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    return response()->json(['success' => false, 'message' => 'Format special fields tidak valid.'], 422);
                }

                if (is_array($decoded)) {
                    $specialFields = $decoded;
                }
            }

            foreach ($specialFields as $idx => $sf) {
                if (empty($sf['placeholder_key']) || empty($sf['field_type'])) {
                    unset($specialFields[$idx]);
                    continue;
                }

                if (!in_array($sf['field_type'], self::ALL_FIELD_TYPES, true)) {
                    return response()->json(['success' => false, 'message' => "Tipe field tidak valid: {$sf['field_type']}"], 422);
                }

                if (!preg_match('/^[a-z0-9_]+$/', $sf['placeholder_key'])) {
                    return response()->json([
                        'success' => false,
                        'message' => "Key placeholder tidak valid: {$sf['placeholder_key']}. Hanya huruf kecil, angka, dan underscore.",
                    ], 422);
                }

                if (!empty($sf['find_text'])) {
                    $findText = trim($sf['find_text']);
                    $placeholderKey = trim($sf['placeholder_key']);
                    $replacements[md5($findText . '|' . $placeholderKey)] = [
                        'find' => $findText,
                        'replace' => $placeholderKey,
                        'blank' => trim($sf['blank'] ?? ''),
                    ];
                }
            }

            $specialFields = array_values($specialFields);
            $replacements = array_values($replacements);

            Log::info('saveWithMapping', [
                'replacements_count' => count($replacements),
                'special_fields_count' => count($specialFields),
                'special_field_types' => array_column($specialFields, 'field_type'),
            ]);

            $processedPath = $this->generatorService->replaceDummyWithPlaceholders($tempPath, $replacements);

            if (!$processedPath || !file_exists($processedPath)) {
                throw new \Exception('Gagal memproses file template.');
            }

            $filename = 'template_' . time() . '_' . Str::random(10) . '.docx';
            $storedPath = Storage::disk('public')->putFileAs('report_templates', $processedPath, $filename);

            @unlink($processedPath);

            if (!$storedPath) {
                throw new \Exception('Gagal menyimpan file ke storage.');
            }

            $allowedColumnsByTable = $this->getAllowedColumns();
            $availableFields = array_keys($allowedColumnsByTable[$validated['source_table']] ?? []);

            foreach ($specialFields as $sf) {
                if (!in_array($sf['placeholder_key'], $availableFields, true)) {
                    $availableFields[] = $sf['placeholder_key'];
                }
            }

            $template = ReportTemplate::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'category' => $validated['category'],
                'source_table' => strtolower($validated['source_table']),
                'template_file_path' => $storedPath,
                'description' => $validated['description'] ?? null,
                'available_fields' => $availableFields,
                'created_by' => Auth::id(),
                'is_active' => true,
            ]);

            $absolutePath = $this->generatorService->getTemplateAbsolutePath($storedPath);

            if (!$absolutePath || !file_exists($absolutePath)) {
                throw new \Exception('File template hasil proses tidak ditemukan.');
            }

            $placeholders = $this->generatorService->extractPlaceholdersFromDocx($absolutePath);
            $allowedColumns = $allowedColumnsByTable[$validated['source_table']] ?? [];

            foreach ($placeholders as $index => $ph) {
                $placeholderKey = $ph['key'];
                $specialMatch = collect($specialFields)->firstWhere('placeholder_key', $placeholderKey);

                if ($specialMatch) {
                    $fieldType = $specialMatch['field_type'];
                    $config = $specialMatch['config'] ?? null;

                    if (is_array($config)) {
                        $config = $this->cleanConfig($fieldType, $config);
                    }

                    TemplatePlaceholder::create([
                        'template_id' => $template->id,
                        'placeholder_key' => $specialMatch['placeholder_key'],
                        'placeholder_label' => $specialMatch['placeholder_label']
                            ?? $this->generateLabelFromKey($specialMatch['placeholder_key']),
                        'field_type' => $fieldType,
                        'is_manual' => $this->isManualFieldType($fieldType),
                        'source_column' => null,
                        'config' => $config,
                        'default_value' => $this->extractDefaultValue($fieldType, $config),
                        'options' => $this->extractOptions($fieldType, $config),
                        'sort_order' => $index,
                    ]);

                    continue;
                }

                TemplatePlaceholder::create([
                    'template_id' => $template->id,
                    'placeholder_key' => $placeholderKey,
                    'placeholder_label' => $allowedColumns[$placeholderKey] ?? $this->generateLabelFromKey($placeholderKey),
                    'field_type' => 'text',
                    'is_manual' => false,
                    'source_column' => $placeholderKey,
                    'sort_order' => $index,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil dibuat dengan ' . count($placeholders) . ' placeholder.',
                'data' => ['id' => $template->id, 'name' => $template->name],
            ], 201);
        } catch (\Throwable $e) {
            Log::error('saveWithMapping failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['success' => false, 'message' => 'Gagal menyimpan template: ' . $e->getMessage()], 500);
        }
    }

    public function getEditData(ReportTemplate $template): JsonResponse
    {
        $existingMappings = [];
        foreach ($template->placeholders->sortBy('sort_order') as $p) {
            $existingMappings[] = [
                'key' => $p->placeholder_key,
                'label' => $p->placeholder_label,
                'type' => $p->field_type,
                'is_manual' => (bool) $p->is_manual,
                'source_column' => $p->source_column,
                'config' => $p->config,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $template->id,
                'name' => $template->name,
                'code' => $template->code,
                'category' => $template->category,
                'source_table' => $template->source_table ?? 'karyawan',
                'description' => $template->description,
                'template_url' => $template->template_file_path ? asset('storage/' . $template->template_file_path) : null,
                'existingMappings' => $existingMappings,
                'allowedColumns' => $this->getAllowedColumns(),
            ],
        ]);
    }

    public function update(Request $request, ReportTemplate $template): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'code' => ['required', 'string', 'max:50', Rule::unique('report_templates', 'code')->ignore($template->id)],
                'category' => 'nullable|string|max:100',
                'source_table' => ['nullable', 'string', Rule::in(array_keys($this->getAvailableTables()))],
                'description' => 'nullable|string|max:1000',
                'is_active' => 'nullable|boolean',
                'template_file' => 'nullable|file|mimes:docx,doc|max:10240',
                'replacements' => 'nullable|array',
                'special_fields' => 'nullable|string',
            ], [
                'code.unique' => 'Kode Template ":input" sudah digunakan oleh template lain.',
                'name.required' => 'Nama Template wajib diisi.',
                'code.required' => 'Kode Template wajib diisi.',
                'template_file.mimes' => 'File harus bertipe .docx atau .doc',
            ]);

            $sourceTable = strtolower($validated['source_table'] ?? $template->source_table);

            $allDbMappings = [];
            $replacements = [];
            foreach ((array) ($validated['replacements'] ?? []) as $item) {
                $find = trim($item['find'] ?? '');
                $field = trim($item['replace'] ?? '');
                $blank = trim($item['blank'] ?? '');
                if ($field !== '') {
                    $allDbMappings[$field] = ['find' => $find, 'replace' => $field];
                    if ($find !== '') {
                        $replacements[md5($find . '|' . $field)] = ['find' => $find, 'replace' => $field, 'blank' => $blank];
                    }
                }
            }
            $allDbMappings = array_values($allDbMappings);

            $specialFields = [];
            if (!empty($validated['special_fields'])) {
                $decoded = json_decode($validated['special_fields'], true);
                if (is_array($decoded)) {
                    $specialFields = $decoded;
                }
            }

            foreach ($specialFields as $idx => $sf) {
                if (empty($sf['placeholder_key']) || empty($sf['field_type'])) {
                    unset($specialFields[$idx]);
                    continue;
                }
                if (!in_array($sf['field_type'], self::ALL_FIELD_TYPES, true)) {
                    return response()->json(['success' => false, 'message' => "Tipe field tidak valid: {$sf['field_type']}"], 422);
                }
                if (!preg_match('/^[a-z0-9_]+$/', $sf['placeholder_key'])) {
                    return response()->json(['success' => false, 'message' => "Key placeholder tidak valid: {$sf['placeholder_key']}"], 422);
                }
            }
            $specialFields = array_values($specialFields);

            foreach ($specialFields as $sf) {
                if (!empty($sf['find_text'])) {
                    $find = trim($sf['find_text']);
                    $replacements[md5($find . '|' . $sf['placeholder_key'])] = [
                        'find' => $find,
                        'replace' => $sf['placeholder_key'],
                        'blank' => trim($sf['blank'] ?? ''),
                    ];
                }
            }
            $replacements = array_values($replacements);

            Log::info('update template', [
                'id' => $template->id,
                'has_new_file' => $request->hasFile('template_file'),
                'new_replacements' => count($replacements),
                'db_mappings' => count($allDbMappings),
                'special_fields' => count($specialFields),
            ]);

            if ($request->hasFile('template_file')) {
                $tempPath = $request->file('template_file')->getRealPath();

                $processedPath = $this->generatorService->replaceDummyWithPlaceholders($tempPath, $replacements);

                $filename = 'template_' . time() . '_' . Str::random(10) . '.docx';
                $storedPath = Storage::disk('public')->putFileAs('report_templates', $processedPath, $filename);
                @unlink($processedPath);

                if (!$storedPath) {
                    throw new \Exception('Gagal menyimpan file ke storage');
                }

                if ($template->template_file_path && Storage::disk('public')->exists($template->template_file_path)) {
                    Storage::disk('public')->delete($template->template_file_path);
                }

                $availableFields = array_keys($this->getAllowedColumns()[$sourceTable] ?? []);
                foreach ($specialFields as $sf) {
                    if (!in_array($sf['placeholder_key'], $availableFields, true)) {
                        $availableFields[] = $sf['placeholder_key'];
                    }
                }

                $template->update([
                    'name' => $validated['name'],
                    'code' => $validated['code'],
                    'category' => $validated['category'] ?? $template->category,
                    'source_table' => $sourceTable,
                    'template_file_path' => $storedPath,
                    'description' => $validated['description'] ?? $template->description,
                    'available_fields' => $availableFields,
                    'is_active' => $validated['is_active'] ?? $template->is_active,
                ]);

                $template->placeholders()->delete();
                $absolutePath = $this->generatorService->getTemplateAbsolutePath($storedPath);
                $placeholders = $this->generatorService->extractPlaceholdersFromDocx($absolutePath);
                $allowedColumns = $this->getAllowedColumns()[$sourceTable] ?? [];

                foreach ($placeholders as $index => $ph) {
                    $specialMatch = collect($specialFields)->firstWhere('placeholder_key', $ph['key']);

                    if ($specialMatch) {
                        $fieldType = $specialMatch['field_type'];
                        $config = is_array($specialMatch['config'] ?? null)
                            ? $this->cleanConfig($fieldType, $specialMatch['config'])
                            : null;

                        TemplatePlaceholder::create([
                            'template_id' => $template->id,
                            'placeholder_key' => $specialMatch['placeholder_key'],
                            'placeholder_label' => $specialMatch['placeholder_label'] ?? $this->generateLabelFromKey($specialMatch['placeholder_key']),
                            'field_type' => $fieldType,
                            'is_manual' => $this->isManualFieldType($fieldType),
                            'source_column' => null,
                            'config' => $config,
                            'default_value' => $this->extractDefaultValue($fieldType, $config),
                            'options' => $this->extractOptions($fieldType, $config),
                            'sort_order' => $index,
                        ]);
                    } else {
                        TemplatePlaceholder::create([
                            'template_id' => $template->id,
                            'placeholder_key' => $ph['key'],
                            'placeholder_label' => $allowedColumns[$ph['key']] ?? $this->generateLabelFromKey($ph['key']),
                            'field_type' => 'text',
                            'is_manual' => false,
                            'source_column' => $ph['key'],
                            'sort_order' => $index,
                        ]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Template berhasil diupdate dengan file baru (' . count($placeholders) . ' placeholder).',
                ]);
            }

            if (!empty($replacements)) {
                $oldPath = $template->template_file_path;
                $existingAbsolute = $this->generatorService->getTemplateAbsolutePath($oldPath);

                $processedPath = $this->generatorService->replaceDummyWithPlaceholders($existingAbsolute, $replacements);

                $filename = 'template_' . time() . '_' . Str::random(10) . '.docx';
                $storedPath = Storage::disk('public')->putFileAs('report_templates', $processedPath, $filename);
                @unlink($processedPath);

                if (!$storedPath) {
                    throw new \Exception('Gagal menyimpan file hasil proses');
                }

                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
                $template->template_file_path = $storedPath;
            }

            $availableFields = array_keys($this->getAllowedColumns()[$sourceTable] ?? []);
            foreach (array_merge($specialFields, $allDbMappings) as $f) {
                $key = $f['placeholder_key'] ?? $f['replace'] ?? null;
                if ($key && !in_array($key, $availableFields, true)) {
                    $availableFields[] = $key;
                }
            }

            $template->update([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'category' => $validated['category'] ?? $template->category,
                'source_table' => $sourceTable,
                'description' => $validated['description'] ?? $template->description,
                'available_fields' => $availableFields,
                'is_active' => $validated['is_active'] ?? $template->is_active,
            ]);

            $template->placeholders()->delete();

            $allowedColumns = $this->getAllowedColumns()[$sourceTable] ?? [];
            $sort = 0;

            foreach ($allDbMappings as $m) {
                TemplatePlaceholder::create([
                    'template_id' => $template->id,
                    'placeholder_key' => $m['replace'],
                    'placeholder_label' => $allowedColumns[$m['replace']] ?? $this->generateLabelFromKey($m['replace']),
                    'field_type' => 'text',
                    'is_manual' => false,
                    'source_column' => $m['replace'],
                    'sort_order' => $sort++,
                ]);
            }

            foreach ($specialFields as $sf) {
                $fieldType = $sf['field_type'];
                $config = is_array($sf['config'] ?? null) ? $this->cleanConfig($fieldType, $sf['config']) : null;

                TemplatePlaceholder::create([
                    'template_id' => $template->id,
                    'placeholder_key' => $sf['placeholder_key'],
                    'placeholder_label' => $sf['placeholder_label'] ?? $this->generateLabelFromKey($sf['placeholder_key']),
                    'field_type' => $fieldType,
                    'is_manual' => $this->isManualFieldType($fieldType),
                    'source_column' => null,
                    'config' => $config,
                    'default_value' => $this->extractDefaultValue($fieldType, $config),
                    'options' => $this->extractOptions($fieldType, $config),
                    'sort_order' => $sort++,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil diperbarui (' . $sort . ' placeholder tersimpan).',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update template: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['success' => false, 'message' => 'Gagal memperbarui template: ' . $e->getMessage()], 500);
        }
    }

    public function generate(Request $request, ReportTemplate $template)
    {
        try {
            $validated = $request->validate([
                'source_id' => 'required|integer',
                'report_title' => 'required|string|max:255',
                'manual_inputs' => 'nullable|array',
            ]);

            $loopManualFields = $template->placeholders->filter(fn ($p) => $p->field_type === 'loop_manual');
            foreach ($loopManualFields as $field) {
                $data = $validated['manual_inputs'][$field->placeholder_key] ?? [];
                if (empty($data)) {
                    return back()->with('error', "Field loop \"{$field->placeholder_label}\" wajib diisi minimal 1 baris.")->withInput();
                }
                foreach ($data as $idx => $row) {
                    if (empty(array_filter($row, fn ($v) => $v !== '' && $v !== null))) {
                        return back()->with('error', 'Baris ke-' . ($idx + 1) . " pada \"{$field->placeholder_label}\" tidak boleh kosong.")->withInput();
                    }
                }
            }

            $manualInputFields = $template->placeholders->filter(fn ($p) => in_array($p->field_type, self::MANUAL_INPUT_TYPES, true));
            foreach ($manualInputFields as $field) {
                $config = $field->config ?? [];
                $value = $validated['manual_inputs'][$field->placeholder_key] ?? null;

                if (($config['required'] ?? false) && ($value === null || $value === '')) {
                    return back()->with('error', 'Field "' . ($config['label'] ?? $field->placeholder_label) . '" wajib diisi.')->withInput();
                }
            }

            $sourceTable = $template->source_table ?? '';
            $modelClass = $this->getModelClass($sourceTable);

            if (!$modelClass::find($validated['source_id'])) {
                return back()->with('error', 'Data sumber tidak ditemukan');
            }

            $sourceData = $this->generatorService->getSourceData($sourceTable, $validated['source_id']);
            $manualInputs = $validated['manual_inputs'] ?? [];

            $generation = ReportGeneration::create([
                'template_id' => $template->id,
                'report_title' => $validated['report_title'],
                'source_type' => $sourceTable,
                'source_id' => $validated['source_id'],
                'manual_inputs' => $manualInputs,
                'generated_data' => $sourceData,
                'output_file_path' => '',
                'status' => 'pending',
                'generated_by' => Auth::id(),
            ]);

            \App\Jobs\GenerateReportJob::dispatchSync($generation);

            $generation->refresh();

            if ($generation->status === 'failed') {
                return back()->with('error', 'Gagal membuat laporan. Silakan coba lagi.');
            }

            return redirect()->route('HR.reports.download', $generation);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Report generation failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return back()->with('error', 'Gagal generate laporan: ' . $e->getMessage());
        }
    }

    public function generateFormModal(ReportTemplate $template)
    {
        $sourceTable = $template->source_table ?? '';
        $sourceData = !empty($sourceTable) ? $this->getSourceOptions($sourceTable) : collect();
        $placeholders = $template->placeholders->sortBy(fn ($p) => [
            $this->isManualFieldType($p->field_type) ? 1 : 0,
            $p->sort_order,
        ]);

        return view('HR.template.partials.generate-form', compact('template', 'sourceData', 'placeholders'));
    }

    public function previewFormula(Request $request): JsonResponse
    {
        $template = (string) $request->input('template', '');
        if ($template === '') {
            return response()->json(['success' => false, 'message' => 'Template kosong']);
        }

        $template = preg_replace('/(?<!\{)\{([a-z_:0-9]+)\}(?!\})/i', '{{$1}}', $template) ?? $template;

        $preview = preg_replace_callback(
            '/\{\{\s*([a-z_:0-9]+)\s*\}\}/i',
            function ($m) {
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
                if (preg_match('/^urutan:(\d+)$/', $var, $cm)) {
                    return str_pad('1', (int) $cm[1], '0', STR_PAD_LEFT);
                }
                if ($var === 'urutan_romawi') {
                    return 'I';
                }
                if ($var === 'urutan') {
                    return '1';
                }
                if (str_starts_with($var, 'auth:')) {
                    $field = substr($var, 5);
                    $user = Auth::user();

                    return $user ? (string) ($user->$field ?? '') : '[user.' . $field . ']';
                }

                return $m[0];
            },
            $template
        );

        return response()->json(['success' => true, 'preview' => $preview]);
    }

    public function resetCounter(ReportTemplate $template, string $counterKey): JsonResponse
    {
        if ($template->created_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $settings = $template->settings ?? [];
        if (isset($settings['counters'][$counterKey])) {
            $settings['counters'][$counterKey] = 0;
            $template->update(['settings' => $settings]);
        }

        return response()->json(['success' => true, 'message' => 'Counter berhasil direset']);
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

    public function download(ReportGeneration $generation)
    {
        if ($generation->generated_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        if ($generation->status === 'pending') {
            return response()->make('
                <html>
                <head>
                    <title>Generating Report...</title>
                    <meta http-equiv="refresh" content="2">
                    <style>
                        body { font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; background: #f3f4f6; color: #374151; margin: 0; }
                        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); text-align: center; max-width: 400px; width: 100%; }
                        .spinner { border: 4px solid rgba(0, 0, 0, 0.1); width: 36px; height: 36px; border-radius: 50%; border-left-color: #3b82f6; animation: spin 1s linear infinite; margin: 0 auto 1rem auto; }
                        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
                        p { color: #6b7280; font-size: 0.875rem; }
                    </style>
                </head>
                <body>
                    <div class="card">
                        <div class="spinner"></div>
                        <h2 style="margin: 0 0 0.5rem 0; font-size: 1.25rem;">Sedang memproses laporan...</h2>
                        <p style="margin: 0;">Halaman ini akan otomatis mengunduh laporan saat file siap.</p>
                    </div>
                </body>
                </html>
            ');
        }

        if ($generation->status === 'failed') {
            return response()->make('
                <html>
                <head>
                    <title>Generation Failed</title>
                    <style>
                        body { font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; background: #f3f4f6; color: #374151; margin: 0; }
                        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); text-align: center; max-width: 400px; width: 100%; }
                        .error-icon { color: #ef4444; font-size: 3rem; margin-bottom: 1rem; }
                        p { color: #6b7280; font-size: 0.875rem; }
                        .btn { display: inline-block; margin-top: 1rem; padding: 0.5rem 1rem; background: #3b82f6; color: white; text-decoration: none; border-radius: 4px; font-size: 0.875rem; }
                    </style>
                </head>
                <body>
                    <div class="card">
                        <div class="error-icon">❌</div>
                        <h2 style="margin: 0 0 0.5rem 0; font-size: 1.25rem;">Gagal membuat laporan</h2>
                        <p style="margin: 0;">Terjadi kesalahan saat memproses laporan Anda.</p>
                        <a href="' . route('HR.reports.index') . '" class="btn">Kembali</a>
                    </div>
                </body>
                </html>
            ');
        }

        $storagePath = storage_path('app/public/' . $generation->output_file_path);

        if (!file_exists($storagePath)) {
            abort(404, 'File tidak ditemukan');
        }

        $extension = pathinfo($storagePath, PATHINFO_EXTENSION);
        $mimeType = $extension === 'docx'
            ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            : 'application/pdf';

        return response()->download($storagePath, Str::slug($generation->report_title) . '.' . $extension, [
            'Content-Type' => $mimeType,
        ]);
    }

    public function getHistoryData(Request $request, ?ReportTemplate $template = null): JsonResponse
    {
        $query = ReportGeneration::with(['template', 'generator'])->where('generated_by', Auth::id());

        if ($template) {
            $query->where('template_id', $template->id);
        }

        $generations = $query->latest()->take(50)->get()->map(fn ($g) => [
            'id' => $g->id,
            'template_id' => $g->template_id,
            'template_name' => $g->template?->name ?? 'Template Tidak Diketahui',
            'report_title' => $g->report_title,
            'source_type' => $g->source_type,
            'source_id' => $g->source_id,
            'created_at' => $g->created_at->format('d/m/Y H:i'),
            'user_name' => $g->generator?->nama_lengkap ?? $g->generator?->name ?? $g->generator?->username ?? '-',
            'status' => $g->status,
            'file_extension' => pathinfo((string) $g->output_file_path, PATHINFO_EXTENSION),
            'download_url' => route('HR.reports.download', $g),
        ]);

        return response()->json(['success' => true, 'data' => $generations]);
    }

    public function preview(ReportGeneration $generation)
    {
        if ($generation->generated_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }

        $storagePath = storage_path('app/public/' . $generation->output_file_path);

        if (!file_exists($storagePath)) {
            abort(404, 'File tidak ditemukan');
        }

        $extension = pathinfo($storagePath, PATHINFO_EXTENSION);
        $mimeType = $extension === 'docx'
            ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            : 'application/pdf';

        return response()->file($storagePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($storagePath) . '"',
        ]);
    }

    public function showDetails(ReportGeneration $generation): JsonResponse
    {
        if ($generation->generated_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $exists = $generation->output_file_path && Storage::disk('public')->exists($generation->output_file_path);
        $fileSize = $exists ? round(Storage::disk('public')->size($generation->output_file_path) / 1024, 2) . ' KB' : '-';
        $extension = pathinfo((string) $generation->output_file_path, PATHINFO_EXTENSION);

        return response()->json([
            'report_title' => $generation->report_title,
            'template_name' => $generation->template?->name ?? '-',
            'created_at' => $generation->created_at->format('d/m/Y H:i'),
            'generator' => $generation->generator?->name ?? '-',
            'status' => $generation->status,
            'file_extension' => strtoupper($extension),
            'file_size' => $fileSize,
            'manual_inputs' => $generation->manual_inputs,
            'download_url' => route('HR.reports.download', $generation),
        ]);
    }

    public function addPlaceholder(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'template_id' => 'required|exists:report_templates,id',
                'placeholder_label' => 'required|string|max:255',
                'placeholder_key' => [
                    'required', 'string', 'max:100', 'regex:/^[a-z0-9_\.]+$/',
                    Rule::unique('template_placeholders', 'placeholder_key')->where('template_id', $request->template_id),
                ],
                'field_type' => ['required', Rule::in(self::ALL_FIELD_TYPES)],
                'is_manual' => 'required|boolean',
                'source_column' => 'nullable|string|max:255',
                'options' => 'nullable|string',
                'default_value' => 'nullable|string',
                'sort_order' => 'nullable|integer|min:0',
                'config' => 'nullable|array',
            ]);

            $template = ReportTemplate::findOrFail($validated['template_id']);

            if ($template->created_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $this->validatePlaceholderConfig($validated['field_type'], $validated['config'] ?? []);

            $placeholder = TemplatePlaceholder::create([
                'template_id' => $validated['template_id'],
                'placeholder_key' => $validated['placeholder_key'],
                'placeholder_label' => $validated['placeholder_label'],
                'field_type' => $validated['field_type'],
                'is_manual' => $this->isManualFieldType($validated['field_type']),
                'source_column' => $validated['source_column'] ?? null,
                'options' => !empty($validated['options']) ? explode(',', $validated['options']) : null,
                'default_value' => $validated['default_value'] ?? null,
                'sort_order' => $validated['sort_order']
                    ?? ((TemplatePlaceholder::where('template_id', $validated['template_id'])->max('sort_order') ?? 0) + 1),
                'config' => $validated['config'] ?? null,
            ]);

            return response()->json(['success' => true, 'message' => 'Field berhasil ditambahkan', 'data' => $placeholder], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to add placeholder: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Gagal menambahkan field'], 500);
        }
    }

    protected function validatePlaceholderConfig(string $fieldType, array $config): void
    {
        $rules = match ($fieldType) {
            'auto_date' => [
                'day_format' => 'nullable|string|in:none,number,word,word_upper,day_name,day_name_upper',
                'month_format' => 'nullable|string|in:none,number,month_name,month_name_upper',
                'year_format' => 'nullable|string|in:none,number,word,word_upper',
                'separator' => 'nullable|string',
                'label' => 'nullable|string',
            ],
            'formula' => [
                'template' => 'required|string',
                'counter_key' => 'nullable|string|max:100',
                'last_number' => 'nullable|integer|min:0',
            ],
            'auth_field' => ['field' => 'required|string'],
            'relation_single' => [
                'relation' => 'required|string',
                'field' => 'required|string',
            ],
            'loop_manual' => [
                'columns' => 'required|array|min:1',
                'columns.*.key' => 'required|string',
                'columns.*.label' => 'required|string',
                'columns.*.type' => 'nullable|string|in:text,number,date',
            ],
            'loop_relation' => [
                'relation' => 'required|string',
                'fields' => 'nullable|array',
            ],
            'manual_text' => [
                'label' => 'nullable|string|max:255',
                'default' => 'nullable|string',
                'placeholder' => 'nullable|string',
                'required' => 'nullable|boolean',
            ],
            'manual_textarea' => [
                'label' => 'nullable|string|max:255',
                'rows' => 'nullable|integer|min:2|max:20',
                'default' => 'nullable|string',
                'required' => 'nullable|boolean',
            ],
            'manual_date' => [
                'label' => 'nullable|string|max:255',
                'day_format' => 'nullable|string|in:none,number,word,word_upper,day_name,day_name_upper',
                'month_format' => 'nullable|string|in:none,number,month_name,month_name_upper',
                'year_format' => 'nullable|string|in:none,number,word,word_upper',
                'separator' => 'nullable|string',
                'default' => 'nullable|string',
                'required' => 'nullable|boolean',
            ],
            'manual_number' => [
                'label' => 'nullable|string|max:255',
                'number_type' => 'nullable|string|in:number,currency,integer',
                'default' => 'nullable|numeric',
                'required' => 'nullable|boolean',
            ],
            'manual_select' => [
                'label' => 'nullable|string|max:255',
                'options' => 'required|array|min:1',
                'default' => 'nullable|string',
                'required' => 'nullable|boolean',
            ],
            'manual_checkbox' => [
                'label' => 'nullable|string|max:255',
                'default' => 'nullable|in:0,1',
            ],
            default => [],
        };

        if (!empty($rules)) {
            validator($config, $rules)->validate();
        }
    }

    public function updatePlaceholder(Request $request, TemplatePlaceholder $placeholder): JsonResponse
    {
        try {
            if ($placeholder->template->created_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'placeholder_label' => 'required|string|max:255',
                'field_type' => ['required', Rule::in(self::ALL_FIELD_TYPES)],
                'is_manual' => 'required|boolean',
                'source_column' => 'nullable|string|max:255',
                'options' => 'nullable|string',
                'default_value' => 'nullable|string',
                'sort_order' => 'nullable|integer|min:0',
                'config' => 'nullable|array',
            ]);

            $this->validatePlaceholderConfig($validated['field_type'], $validated['config'] ?? []);

            $placeholder->update([
                'placeholder_label' => $validated['placeholder_label'],
                'field_type' => $validated['field_type'],
                'is_manual' => $this->isManualFieldType($validated['field_type']),
                'source_column' => $validated['source_column'] ?? null,
                'options' => !empty($validated['options']) ? explode(',', $validated['options']) : null,
                'default_value' => $validated['default_value'] ?? null,
                'sort_order' => $validated['sort_order'] ?? $placeholder->sort_order,
                'config' => $validated['config'] ?? $placeholder->config,
            ]);

            return response()->json(['success' => true, 'message' => 'Field berhasil diperbarui', 'data' => $placeholder]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update placeholder: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Gagal memperbarui field'], 500);
        }
    }

    public function deletePlaceholder(TemplatePlaceholder $placeholder): JsonResponse
    {
        try {
            if ($placeholder->template->created_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $placeholder->delete();

            return response()->json(['success' => true, 'message' => 'Field berhasil dihapus']);
        } catch (\Exception $e) {
            Log::error('Failed to delete placeholder: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Gagal menghapus field'], 500);
        }
    }

    public function scanDocxForBlanks(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'template_file' => 'required|file|mimes:docx',
                'source_table' => ['nullable', 'string', Rule::in(array_keys($this->getAvailableTables()))],
            ]);

            $table = strtolower($request->input('source_table', 'karyawan'));
            $columns = $this->getAllowedColumns()[$table] ?? [];

            $items = app(ReportSmartScanner::class)->scan($request->file('template_file')->getRealPath(), $table, $columns);

            return response()->json(['success' => true, 'data' => $items, 'columns' => $columns]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Scan DOCX error: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    protected function isManualFieldType(string $fieldType): bool
    {
        return in_array($fieldType, self::MANUAL_INPUT_TYPES, true)
            || in_array($fieldType, ['text', 'textarea', 'date', 'select', 'checkbox', 'number', 'currency', 'loop_manual'], true);
    }

    protected function generateLabelFromKey(string $key): string
    {
        return Str::title(str_replace('_', ' ', $key));
    }

    protected function cleanConfig(string $fieldType, array $config): array
    {
        $allowedKeys = match ($fieldType) {
            'auto_date' => ['day_format', 'month_format', 'year_format', 'separator', 'label'],
            'formula' => ['template', 'counter_key', 'last_number', 'label'],
            'auth_field' => ['field', 'label'],
            'relation_single' => ['relation', 'field', 'label'],
            'loop_manual' => ['columns', 'label'],
            'loop_relation' => ['relation', 'fields', 'label'],
            'manual_text' => ['label', 'default', 'placeholder', 'required'],
            'manual_textarea' => ['label', 'rows', 'default', 'required'],
            'manual_date' => ['label', 'day_format', 'month_format', 'year_format', 'separator', 'default', 'required'],
            'manual_number' => ['label', 'number_type', 'default', 'required'],
            'manual_select' => ['label', 'options', 'default', 'required'],
            'manual_checkbox' => ['label', 'default'],
            default => array_keys($config),
        };

        return array_intersect_key($config, array_flip($allowedKeys));
    }

    protected function extractDefaultValue(string $fieldType, ?array $config): ?string
    {
        if (empty($config)) {
            return null;
        }

        return match ($fieldType) {
            'manual_text', 'manual_textarea', 'manual_date', 'manual_number', 'manual_select' => $config['default'] ?? null,
            'manual_checkbox' => isset($config['default']) ? (string) $config['default'] : null,
            default => null,
        };
    }

    protected function extractOptions(string $fieldType, ?array $config): ?array
    {
        if (empty($config)) {
            return null;
        }

        if ($fieldType === 'manual_select' && !empty($config['options'])) {
            return is_array($config['options']) ? $config['options'] : explode(',', $config['options']);
        }

        return null;
    }

    protected function getAvailableTables(): array
    {
        return [
            'karyawan' => 'Data Karyawan',
            'pelamar' => 'Data Rekturan',
        ];
    }

    protected function getModelClass(string $sourceTable): string
    {
        return match ($sourceTable) {
            'karyawan' => karyawan::class,
            'pelamar' => Pelamar::class,
            default => throw new \Exception('Source table tidak dikenali: ' . $sourceTable),
        };
    }

    protected function getSourceOptions(string $sourceTable)
    {
        return match ($sourceTable) {
            'karyawan' => karyawan::orderBy('nama_lengkap')->get(['id', 'nip', 'nama_lengkap']),
            'pelamar' => Pelamar::orderBy('nama_lengkap')->get(['id', 'nama_lengkap']),
            default => collect(),
        };
    }

    protected function getAllowedColumns(): array
    {
        return [
            'karyawan' => [
                'nip' => 'NIP',
                'nama_lengkap' => 'Nama Lengkap',
                'alamat_lengkap' => 'Alamat',
                'gender' => 'Jenis Kelamin',
                'tempat_lahir' => 'Tempat Lahir',
                'tanggal_lahir' => 'Tanggal Lahir',
                'religion' => 'Agama',
                'provinsi' => 'Provinsi',
                'kota' => 'Kota',
                'divisi' => 'Divisi',
                'jabatan' => 'Jabatan',
                'awal_probation' => 'Tanggal Awal Probation',
                'akhir_probation' => 'Tanggal Akhir Probation',
                'awal_kontrak' => 'Tanggal Awal Kontrak',
                'akhir_kontrak' => 'Tanggal Akhir Kontrak',
                'awal_tetap' => 'Tanggal Awal Tetap',
                'akhir_tetap' => 'Tanggal Akhir Tetap',
                'email' => 'Email',
                'gaji' => 'Gaji Karyawan',
                'tunjangan_jabatan' => 'Tunjangan Jabatan',
                'whatsapp' => 'Nomor Whatsapp',
                'Telepon' => 'Nomor Telepon',
                'resigned_at' => 'Tanggal Resign',
                'alasan_resign' => 'Alasan Resign'
            ],
            'pelamar' => [
                'nama_lengkap' => 'Nama Lengkap',
                'email' => 'Email',
                'no_telepon' => 'No. Telepon',
                'domisili' => 'Domisili',
                'tanggal_lahir' => 'Tanggal Lahir',
                'jenis_kelamin' => 'Jenis Kelamin',
                'pendidikan_terakhir' => 'Pendidikan Terakhir',
                'jurusan' => 'Jurusan',
                'institusi' => 'Institusi / Universitas',
                'ipk' => 'IPK',
                'pengalaman_tahun' => 'Pengalaman (Tahun)',
                'keahlian' => 'Keahlian',
                'divisi' => 'Divisi',
                'jabatan' => 'Jabatan',
                'detail_jabatan' => 'Spesifikasi Jabatan',
                'tanggal_melamar' => 'Tanggal Melamar',
                'sumber_lamaran' => 'Sumber Melamar',
                'gaji_diharapkan' => 'Gaji Diharapkan',
                'tahap_rekrutmen' => 'Tahap Rekrutmen',
                'rating' => 'Rating Pelamar',
                'catatan_hr' => 'Catatan HR',
                'tahap_interview' => 'Tahap Interview',
                'jadwal_interview' => 'Jadwal Interview',
                'metode_interview' => 'Metode Interview',
                'link_meeting' => 'Link Meeting',
                'lokasi_interview' => 'Lokasi Interview',
                'interviewer' => 'Interviewer',
                'gaji_ditawarkan' => 'Gaji Ditawarkan',
                'tunjangan_makan' => 'Tunjangan Makan',
                'tunjangan_transport' => 'Tunjangan Transportasi',
                'tanggal_mulai_kerja' => 'Tanggal Masuk Kerja',
                'status_kepegawaian' => 'Status Kepegawaian',
                'benefit_lainnya' => 'Benefit Lainnya',
                'status_offer' => 'Status Offer',
                'tanggal_offer_dikirim' => 'Tanggal Offer Dikirim',
                'alasan_penolakan' => 'Alasan Penolakan',
                'catatan_internal' => 'Catatan Internal',
                'nik_karyawan' => 'NIK Karyawan',
                'atasan_langsung' => 'Atasan Langsung',
                'talent_pool_catatan' => 'Catatan Talent Pool',
            ],
        ];
    }
    public function updateHistory(Request $request, ReportGeneration $generation): JsonResponse
    {
        try {
            if ($generation->generated_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate(
                ['report_title' => 'required|string|max:255'],
                ['report_title.required' => 'Judul laporan wajib diisi.']
            );

            $generation->update(['report_title' => $validated['report_title']]);

            return response()->json(['success' => true, 'message' => 'Riwayat berhasil diperbarui.']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update history: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Gagal update: ' . $e->getMessage()], 500);
        }
    }

    public function destroyHistory(ReportGeneration $generation): JsonResponse
    {
        try {
            if ($generation->generated_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            if ($generation->output_file_path) {
                $filePath = storage_path('app/public/' . $generation->output_file_path);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            $generation->delete();

            return response()->json(['success' => true, 'message' => 'Riwayat berhasil dihapus.']);
        } catch (\Exception $e) {
            Log::error('Failed to delete history: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Gagal hapus: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(ReportTemplate $template): JsonResponse
    {
        try {
            $generationCount = ReportGeneration::where('template_id', $template->id)->count();

            if ($generationCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Template tidak dapat dihapus karena sudah digunakan {$generationCount} kali untuk generate laporan. Non-aktifkan template jika tidak ingin digunakan lagi.",
                ], 422);
            }

            if ($template->template_file_path) {
                $filePath = storage_path('app/public/' . $template->template_file_path);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }

            $template->placeholders()->delete();
            $template->delete();

            return response()->json(['success' => true, 'message' => 'Template berhasil dihapus.']);
        } catch (\Exception $e) {
            Log::error('Failed to delete template: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Gagal menghapus template: ' . $e->getMessage()], 500);
        }
    }

    public function previewGenerate(Request $request, ReportTemplate $template)
    {
        try {
            $validated = $request->validate([
                'source_id' => 'required|integer',
                'manual_inputs' => 'nullable|array',
            ]);

            $sourceTable = $template->source_table ?? '';
            $modelClass = $this->getModelClass($sourceTable);

            if (!$modelClass::find($validated['source_id'])) {
                return response()->json(['success' => false, 'message' => 'Data sumber tidak ditemukan'], 422);
            }

            $sourceData = $this->generatorService->getSourceData($sourceTable, $validated['source_id']);
            $manualInputs = $validated['manual_inputs'] ?? [];

            $outputPath = $this->generatorService->generateDocxReport($template, $sourceData, $manualInputs, true);

            $content = Storage::disk('public')->get($outputPath);
            Storage::disk('public')->delete($outputPath);

            return response($content, 200, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'inline; filename="preview_' . $template->code . '.docx"',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Preview generate failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['success' => false, 'message' => 'Gagal membuat preview: ' . $e->getMessage()], 500);
        }
    }
}