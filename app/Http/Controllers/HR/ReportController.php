<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\karyawan;
use App\Models\Kegiatan;
use App\Models\ReportTemplate;
use App\Models\TemplatePlaceholder;
use App\Models\ReportGeneration;
use App\Services\ReportGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
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

        return view('HR.template.index', compact('templates'));
    }

    public function create()
    {
        $allowedColumns = $this->getAllowedColumns();
        return view('HR.template.create', compact('allowedColumns'));
    }

    public function uploadAndMap(Request $request): JsonResponse
    {
        $request->validate([
            'template_file' => 'required|file|mimes:docx,doc|max:10240',
            'source_table' => 'required|string',
        ]);

        $file = $request->file('template_file');
        $text = $this->generatorService->extractTextFromDocx($file->getRealPath());
        $columns = $this->getAllowedColumns()[$request->source_table] ?? [];

        return response()->json([
            'success' => true,
            'text' => $text,
            'columns' => $columns,
        ]);
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

        $file = $request->file('template_file');
        $tempPath = $file->getRealPath();

        // Kumpulkan replacements dari mapping teks biasa (DB fields)
        $replacements = [];
        foreach ((array) ($validated['replacements'] ?? []) as $item) {
            $find = trim($item['find'] ?? '');
            $field = trim($item['replace'] ?? '');
            if (!empty($find) && !empty($field)) {
                $replacements[] = ['find' => $find, 'replace' => $field];
            }
        }

        // Decode special fields dari JSON
        $specialFields = [];
        if (!empty($validated['special_fields'])) {
            $decoded = json_decode($validated['special_fields'], true);
            if (is_array($decoded)) {
                $specialFields = $decoded;
            }
        }

        // Validasi special fields
        foreach ($specialFields as $idx => $sf) {
            if (empty($sf['placeholder_key']) || empty($sf['field_type'])) {
                unset($specialFields[$idx]);
                continue;
            }
            // Validasi tipe field
            if (!in_array($sf['field_type'], self::ALL_FIELD_TYPES)) {
                return response()->json([
                    'success' => false,
                    'message' => "Tipe field tidak valid: {$sf['field_type']}",
                ], 422);
            }
            // Validasi key placeholder
            if (!preg_match('/^[a-z0-9_]+$/', $sf['placeholder_key'])) {
                return response()->json([
                    'success' => false,
                    'message' => "Key placeholder tidak valid: {$sf['placeholder_key']}. Hanya huruf kecil, angka, dan underscore.",
                ], 422);
            }
        }
        $specialFields = array_values($specialFields);

        // Gabungkan special fields ke replacements agar dummy text ter-replace
        foreach ($specialFields as $sf) {
            if (!empty($sf['find_text']) && !empty($sf['placeholder_key'])) {
                $replacements[] = [
                    'find' => $sf['find_text'],
                    'replace' => $sf['placeholder_key']
                ];
            }
        }

        Log::info('saveWithMapping', [
            'replacements_count' => count($replacements),
            'special_fields_count' => count($specialFields),
            'special_field_types' => array_column($specialFields, 'field_type'),
        ]);

        try {
            // Proses replace dummy text dengan placeholder di file DOCX
            $processedPath = $this->generatorService->replaceDummyWithPlaceholders($tempPath, $replacements);

            $filename = 'template_' . time() . '_' . Str::random(10) . '.docx';
            $storedPath = Storage::disk('public')->putFileAs('report_templates', $processedPath, $filename);
            @unlink($processedPath);

            if (!$storedPath) {
                throw new \Exception('Gagal menyimpan file ke storage');
            }

            // Ambil available fields dari kolom database
            $availableFields = array_keys($this->getAllowedColumns()[$validated['source_table']] ?? []);
            // Tambahkan juga key dari special fields
            foreach ($specialFields as $sf) {
                if (!in_array($sf['placeholder_key'], $availableFields)) {
                    $availableFields[] = $sf['placeholder_key'];
                }
            }

            // Simpan template
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

            // Ekstrak placeholder dari file yang sudah diproses
            $absolutePath = $this->generatorService->getTemplateAbsolutePath($storedPath);
            $placeholders = $this->generatorService->extractPlaceholdersFromDocx($absolutePath);
            $allowedColumns = $this->getAllowedColumns()[$validated['source_table']] ?? [];

            Log::info('saveWithMapping: placeholders extracted', [
                'count' => count($placeholders),
                'keys' => array_column($placeholders, 'key'),
            ]);

            // Simpan setiap placeholder ke database
            foreach ($placeholders as $index => $ph) {
                $specialMatch = null;
                foreach ($specialFields as $sf) {
                    if ($sf['placeholder_key'] === $ph['key']) {
                        $specialMatch = $sf;
                        break;
                    }
                }

                if ($specialMatch) {
                    // Placeholder dari special field (auto_date, formula, manual, dll)
                    $fieldType = $specialMatch['field_type'];
                    $isManual = $this->isManualFieldType($fieldType);
                    $config = $specialMatch['config'] ?? null;

                    // Pastikan config tersimpan dengan benar
                    if (is_array($config)) {
                        // Bersihkan config dari key yang tidak perlu
                        $config = $this->cleanConfig($fieldType, $config);
                    }

                    TemplatePlaceholder::create([
                        'template_id' => $template->id,
                        'placeholder_key' => $specialMatch['placeholder_key'],
                        'placeholder_label' => $specialMatch['placeholder_label'] 
                            ?? $this->generateLabelFromKey($specialMatch['placeholder_key']),
                        'field_type' => $fieldType,
                        'is_manual' => $isManual,
                        'source_column' => $this->isManualFieldType($fieldType) ? null : null,
                        'config' => $config,
                        'default_value' => $this->extractDefaultValue($fieldType, $config),
                        'options' => $this->extractOptions($fieldType, $config),
                        'sort_order' => $index,
                    ]);
                } else {
                    // Placeholder biasa dari kolom database
                    TemplatePlaceholder::create([
                        'template_id' => $template->id,
                        'placeholder_key' => $ph['key'],
                        'placeholder_label' => $allowedColumns[$ph['key']] 
                            ?? $this->generateLabelFromKey($ph['key']),
                        'field_type' => 'text',
                        'is_manual' => false,
                        'source_column' => $ph['key'],
                        'sort_order' => $index,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil dibuat dengan ' . count($placeholders) . ' placeholder.',
                'data' => ['id' => $template->id, 'name' => $template->name],
            ], 201);
        } catch (\Exception $e) {
            Log::error('saveWithMapping failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan template: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function edit(ReportTemplate $template)
    {
        if ($template->created_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(403, 'Unauthorized action');
        }

        $availableColumns = $this->getAllowedColumns()[$template->source_table] ?? [];
        $placeholders = $template->placeholders->sortBy('sort_order');

        return view('HR.template.edit', compact('template', 'availableColumns', 'placeholders'));
    }

    public function update(Request $request, ReportTemplate $template): JsonResponse
    {
        try {
            if ($template->created_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'is_active' => 'boolean',
                'field_mappings' => 'nullable|array',
                'labels' => 'nullable|array',
                'types' => 'nullable|array',
                'manual_fields' => 'nullable|array',
            ]);

            $template->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? $template->description,
                'is_active' => $validated['is_active'] ?? $template->is_active,
            ]);

            // Update field mappings dari database
            if (!empty($validated['field_mappings'])) {
                foreach ($validated['field_mappings'] as $key => $column) {
                    if (!empty($column)) {
                        $template->placeholders()->updateOrCreate(
                            ['placeholder_key' => $key],
                            [
                                'placeholder_label' => $request->input("labels.{$key}", $key),
                                'source_column' => $column,
                                'field_type' => $request->input("types.{$key}", 'text'),
                                'is_manual' => false,
                            ],
                        );
                    }
                }
            }

            // Update manual fields
            if (!empty($validated['manual_fields'])) {
                foreach ($validated['manual_fields'] as $field) {
                    // Validasi tipe field
                    if (!in_array($field['type'] ?? 'text', self::ALL_FIELD_TYPES)) {
                        continue;
                    }

                    $isManual = $this->isManualFieldType($field['type'] ?? 'text');

                    $template->placeholders()->updateOrCreate(
                        ['placeholder_key' => $field['key']],
                        [
                            'placeholder_label' => $field['label'],
                            'field_type' => $field['type'] ?? 'text',
                            'is_manual' => $isManual,
                            'default_value' => $field['default'] ?? null,
                            'options' => !empty($field['options']) 
                                ? (is_string($field['options']) ? explode(',', $field['options']) : $field['options']) 
                                : null,
                            'sort_order' => $field['sort_order'] ?? null,
                            'config' => $field['config'] ?? null,
                        ],
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil diperbarui',
                'data' => $template->fresh(['creator']),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Validasi gagal', 
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update template: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Gagal memperbarui template'
            ], 500);
        }
    }

    public function generateForm(ReportTemplate $template)
    {
        $sourceTable = $template->source_table ?? '';
        $sourceData = !empty($sourceTable) ? $this->getSourceOptions($sourceTable) : collect();
        
        // Sort placeholders: non-manual dulu, lalu manual, berdasarkan sort_order
        $placeholders = $template->placeholders->sortBy(fn($p) => [
            $this->isManualFieldType($p->field_type) ? 1 : 0, 
            $p->sort_order
        ]);

        return view('HR.template.generate', compact('template', 'sourceData', 'placeholders'));
    }

    public function generate(Request $request, ReportTemplate $template)
    {
        try {
            $validated = $request->validate([
                'source_id' => 'required|integer',
                'report_title' => 'required|string|max:255',
                'manual_inputs' => 'nullable|array',
            ]);

            // Validasi field loop_manual
            $loopManualFields = $template->placeholders->filter(fn($p) => $p->field_type === 'loop_manual');
            foreach ($loopManualFields as $field) {
                $data = $validated['manual_inputs'][$field->placeholder_key] ?? [];
                if (empty($data)) {
                    return back()
                        ->with('error', "Field loop \"{$field->placeholder_label}\" wajib diisi minimal 1 baris.")
                        ->withInput();
                }
                foreach ($data as $idx => $row) {
                    if (empty(array_filter($row, fn($v) => $v !== '' && $v !== null))) {
                        return back()
                            ->with('error', 'Baris ke-' . ($idx + 1) . " pada \"{$field->placeholder_label}\" tidak boleh kosong.")
                            ->withInput();
                    }
                }
            }

            // Validasi field manual input yang required
            $manualInputFields = $template->placeholders->filter(
                fn($p) => in_array($p->field_type, self::MANUAL_INPUT_TYPES)
            );
            foreach ($manualInputFields as $field) {
                $config = $field->config ?? [];
                $isRequired = $config['required'] ?? false;
                $value = $validated['manual_inputs'][$field->placeholder_key] ?? null;

                if ($isRequired && ($value === null || $value === '')) {
                    return back()
                        ->with('error', "Field \"{$config['label']}\" wajib diisi.")
                        ->withInput();
                }

                if ($field->field_type === 'manual_select' && !empty($value)) {
                    $options = $config['options'] ?? [];
                    if (!empty($options) && !in_array($value, $options)) {
                        return back()
                            ->with('error', "Pilihan tidak valid untuk field \"{$config['label']}\".")
                            ->withInput();
                    }
                }

                if ($field->field_type === 'manual_number' && !empty($value)) {
                    if (!is_numeric($value)) {
                        return back()
                            ->with('error', "Field \"{$config['label']}\" harus berupa angka.")
                            ->withInput();
                    }
                }

                if ($field->field_type === 'manual_date' && !empty($value)) {
                    try {
                        new \DateTime($value);
                    } catch (\Exception $e) {
                        return back()
                            ->with('error', "Format tanggal tidak valid untuk field \"{$config['label']}\".")
                            ->withInput();
                    }
                }
            }

            // Validasi source data
            $sourceTable = $template->source_table ?? '';
            $modelClass = $this->getModelClass($sourceTable);
            $sourceExists = $modelClass::find($validated['source_id']);

            if (!$sourceExists) {
                return back()->with('error', 'Data sumber tidak ditemukan');
            }

            $sourceData = $this->generatorService->getSourceData($sourceTable, $validated['source_id']);
            $manualInputs = $validated['manual_inputs'] ?? [];

            Log::info('generate: source data', ['keys' => array_keys($sourceData)]);

            // Generate laporan
            $outputPath = $this->generatorService->generateDocxReport($template, $sourceData, $manualInputs);

            // Simpan riwayat generate
            $generation = ReportGeneration::create([
                'template_id' => $template->id,
                'report_title' => $validated['report_title'],
                'source_type' => $sourceTable,
                'source_id' => $validated['source_id'],
                'manual_inputs' => $manualInputs,
                'generated_data' => $sourceData,
                'output_file_path' => $outputPath,
                'status' => 'completed',
                'generated_by' => Auth::id(),
            ]);

            return redirect()->route('HR.reports.download', $generation);
        } catch (\Exception $e) {
            Log::error('Report generation failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Gagal generate laporan: ' . $e->getMessage());
        }
    }

    public function previewFormula(Request $request): JsonResponse
    {
        $template = $request->input('template', '');
        if (empty($template)) {
            return response()->json(['success' => false, 'message' => 'Template kosong']);
        }

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
                    return str_pad(1, (int) $cm[1], '0', STR_PAD_LEFT);
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
            $template,
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
            1000 => 'M',
            900 => 'CM',
            500 => 'D',
            400 => 'CD',
            100 => 'C',
            90 => 'XC',
            50 => 'L',
            40 => 'XL',
            10 => 'X',
            9 => 'IX',
            5 => 'V',
            4 => 'IV',
            1 => 'I',
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

    public function history(Request $request, ?ReportTemplate $template = null)
    {
        $query = ReportGeneration::with(['template', 'generator'])->where('generated_by', Auth::id());
        if ($template) {
            $query->where('template_id', $template->id);
        }
        $generations = $query->latest()->paginate(20);

        return view('HR.template.history', compact('generations', 'template'));
    }

    public function getHistoryData(Request $request, ?ReportTemplate $template = null): JsonResponse
    {
        $query = ReportGeneration::with(['template', 'generator'])
            ->where('generated_by', Auth::id());
        
        if ($template) {
            $query->where('template_id', $template->id);
        }

        $generations = $query->latest()->take(50)->get()->map(
            fn($g) => [
                'id' => $g->id,
                'template_id' => $g->template_id,
                'template_name' => $g->template?->name ?? 'Template Tidak Diketahui',
                'report_title' => $g->report_title,
                'source_type' => $g->source_type,
                'source_id' => $g->source_id,
                'created_at' => $g->created_at->format('d/m/Y H:i'),
                'user_name' => $g->generator?->name ?? '-',
                'status' => $g->status,
                'file_extension' => pathinfo($g->output_file_path, PATHINFO_EXTENSION),
                'download_url' => route('HR.reports.download', $g),
            ],
        );

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
            'Access-Control-Allow-Origin' => '*',
        ]);
    }

    public function showDetails(ReportGeneration $generation): JsonResponse
    {
        if ($generation->generated_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $fileSize = Storage::disk('public')->exists($generation->output_file_path) 
            ? round(Storage::disk('public')->size($generation->output_file_path) / 1024, 2) . ' KB' 
            : '-';
        $extension = pathinfo($generation->output_file_path, PATHINFO_EXTENSION);

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
                    'required', 
                    'string', 
                    'max:100', 
                    'regex:/^[a-z0-9_\.]+$/', 
                    Rule::unique('template_placeholders', 'placeholder_key')->where('template_id', $request->template_id)
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

            // Validasi config sesuai tipe field
            $this->validatePlaceholderConfig($validated['field_type'], $validated['config'] ?? []);

            // Auto-set is_manual berdasarkan tipe field
            $isManual = $this->isManualFieldType($validated['field_type']);

            $placeholder = TemplatePlaceholder::create([
                'template_id' => $validated['template_id'],
                'placeholder_key' => $validated['placeholder_key'],
                'placeholder_label' => $validated['placeholder_label'],
                'field_type' => $validated['field_type'],
                'is_manual' => $isManual,
                'source_column' => $validated['source_column'] ?? null,
                'options' => !empty($validated['options']) ? explode(',', $validated['options']) : null,
                'default_value' => $validated['default_value'] ?? null,
                'sort_order' => $validated['sort_order'] 
                    ?? ((TemplatePlaceholder::where('template_id', $validated['template_id'])->max('sort_order') ?? 0) + 1),
                'config' => $validated['config'] ?? null,
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Field berhasil ditambahkan', 
                'data' => $placeholder
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Validasi gagal', 
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to add placeholder: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menambahkan field'
            ], 500);
        }
    }

    /**
     * Validasi konfigurasi placeholder berdasarkan tipe field
     */
    private function validatePlaceholderConfig(string $fieldType, array $config): void
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
            ],
            'auth_field' => [
                'field' => 'required|string',
            ],
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

            // Validasi config sesuai tipe field
            $this->validatePlaceholderConfig($validated['field_type'], $validated['config'] ?? []);

            // Auto-set is_manual berdasarkan tipe field
            $isManual = $this->isManualFieldType($validated['field_type']);

            $placeholder->update([
                'placeholder_label' => $validated['placeholder_label'],
                'field_type' => $validated['field_type'],
                'is_manual' => $isManual,
                'source_column' => $validated['source_column'] ?? null,
                'options' => !empty($validated['options']) ? explode(',', $validated['options']) : null,
                'default_value' => $validated['default_value'] ?? null,
                'sort_order' => $validated['sort_order'] ?? $placeholder->sort_order,
                'config' => $validated['config'] ?? $placeholder->config,
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'Field berhasil diperbarui', 
                'data' => $placeholder
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Validasi gagal', 
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update placeholder: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Gagal memperbarui field'
            ], 500);
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
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menghapus field'
            ], 500);
        }
    }

    /**
     * Cek apakah tipe field termasuk input manual yang diisi saat generate
     */
    private function isManualFieldType(string $fieldType): bool
    {
        return in_array($fieldType, self::MANUAL_INPUT_TYPES) 
            || in_array($fieldType, ['text', 'textarea', 'date', 'select', 'checkbox', 'number', 'currency', 'loop_manual']);
    }

    /**
     * Generate label human-friendly dari key placeholder
     */
    private function generateLabelFromKey(string $key): string
    {
        return Str::title(str_replace('_', ' ', $key));
    }

    /**
     * Bersihkan config dari key yang tidak perlu untuk tipe field tertentu
     */
    private function cleanConfig(string $fieldType, array $config): array
    {
        $allowedKeys = match ($fieldType) {
            'auto_date' => ['day_format', 'month_format', 'year_format', 'separator', 'label'],
            'formula' => ['template', 'counter_key', 'label'],
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

    /**
     * Ekstrak default value dari config berdasarkan tipe field
     */
    private function extractDefaultValue(string $fieldType, ?array $config): ?string
    {
        if (empty($config)) return null;

        return match ($fieldType) {
            'manual_text', 'manual_textarea', 'manual_date', 'manual_number', 'manual_select' 
                => $config['default'] ?? null,
            'manual_checkbox' 
                => isset($config['default']) ? (string) $config['default'] : null,
            default 
                => null,
        };
    }

    /**
     * Ekstrak options dari config berdasarkan tipe field
     */
    private function extractOptions(string $fieldType, ?array $config): ?array
    {
        if (empty($config)) return null;

        if ($fieldType === 'manual_select' && !empty($config['options'])) {
            return is_array($config['options']) ? $config['options'] : explode(',', $config['options']);
        }

        return null;
    }

    private function getAvailableTables(): array
    {
        return [
            'karyawan' => 'Data Karyawan',
            'kegiatan' => 'Data Kegiatan',
        ];
    }

    private function getModelClass(string $sourceTable): string
    {
        return match ($sourceTable) {
            'karyawan' => karyawan::class,
            'kegiatan' => Kegiatan::class,
            default => throw new \Exception('Source table tidak dikenali: ' . $sourceTable),
        };
    }

    private function getSourceOptions(string $sourceTable)
    {
        return match ($sourceTable) {
            'karyawan' => karyawan::orderBy('nama_lengkap')->get(['id', 'nip', 'nama_lengkap']),
            'kegiatan' => Kegiatan::orderBy('nama_kegiatan')->get(['id', 'nama_kegiatan']),
            default => collect(),
        };
    }

    private function getAllowedColumns(): array
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
            ],
            'kegiatan' => [
                'nama_kegiatan' => 'Nama Kegiatan',
                'waktu_kegiatan' => 'Tanggal & Waktu Kegiatan',
                'lama_kegiatan' => 'Durasi',
                'pic' => 'PIC',
                'status' => 'Status',
                'realisasi' => 'Realisasi',
            ],
        ];
    }

    public function destroy(ReportTemplate $template): JsonResponse
    {
        try {
            $generationCount = ReportGeneration::where('template_id', $template->id)->count();
            
            if ($generationCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => "Template tidak dapat dihapus karena sudah digunakan {$generationCount} kali untuk generate laporan. Non-aktifkan template jika tidak ingin digunakan lagi."
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

            return response()->json([
                'success' => true,
                'message' => 'Template berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete template: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus template: ' . $e->getMessage()
            ], 500);
        }
    }
}