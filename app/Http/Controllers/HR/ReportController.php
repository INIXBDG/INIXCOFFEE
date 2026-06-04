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

    public function __construct(ReportGeneratorService $generatorService)
    {
        $this->generatorService = $generatorService;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────────────

    public function index()
    {
        $templates = ReportTemplate::where('is_active', true)->with('creator')->orderBy('category')->orderBy('name')->get()->groupBy('category');

        return view('HR.template.index', compact('templates'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // CREATE — tampilkan halaman upload + visual mapping
    // ─────────────────────────────────────────────────────────────────────────

    public function create()
    {
        $allowedColumns = $this->getAllowedColumns();
        return view('HR.template.create', compact('allowedColumns'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // UPLOAD & MAP — upload DOCX, render preview, kembalikan kandidat teks
    // ─────────────────────────────────────────────────────────────────────────

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

    // ─────────────────────────────────────────────────────────────────────────
    // SAVE WITH MAPPING — proses file, ganti dummy → {{ field }}, simpan template
    // ─────────────────────────────────────────────────────────────────────────

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
        ]);

        $file = $request->file('template_file');
        $tempPath = $file->getRealPath();

        // Bersihkan replacements — buang yang kosong
        $replacements = [];
        foreach ((array) ($validated['replacements'] ?? []) as $item) {
            $find = trim($item['find'] ?? '');
            $field = trim($item['replace'] ?? '');
            if (!empty($find) && !empty($field)) {
                $replacements[] = ['find' => $find, 'replace' => $field];
            }
        }

        Log::info('saveWithMapping: replacements', ['count' => count($replacements), 'data' => $replacements]);

        try {
            // Proses: ganti teks dummy → {{ field_name }}
            $processedPath = $this->generatorService->replaceDummyWithPlaceholders($tempPath, $replacements);

            // Simpan file template yang sudah diproses
            $filename = 'template_' . time() . '_' . Str::random(10) . '.docx';
            $storedPath = Storage::disk('public')->putFileAs('report_templates', $processedPath, $filename);
            @unlink($processedPath);

            if (!$storedPath) {
                throw new \Exception('Gagal menyimpan file ke storage');
            }

            // Buat record template
            $template = ReportTemplate::create([
                'name' => $validated['name'],
                'code' => $validated['code'],
                'category' => $validated['category'],
                'source_table' => strtolower($validated['source_table']),
                'template_file_path' => $storedPath,
                'description' => $validated['description'] ?? null,
                'available_fields' => array_keys($this->getAllowedColumns()[$validated['source_table']] ?? []),
                'created_by' => Auth::id(),
                'is_active' => true,
            ]);

            // Ekstrak placeholder {{ key }} dari file yang sudah diproses
            $absolutePath = $this->generatorService->getTemplateAbsolutePath($storedPath);
            $placeholders = $this->generatorService->extractPlaceholdersFromDocx($absolutePath);
            $allowedColumns = $this->getAllowedColumns()[$validated['source_table']] ?? [];

            Log::info('saveWithMapping: placeholders extracted', [
                'count' => count($placeholders),
                'keys' => array_column($placeholders, 'key'),
            ]);

            foreach ($placeholders as $index => $ph) {
                TemplatePlaceholder::create([
                    'template_id' => $template->id,
                    'placeholder_key' => $ph['key'],
                    'placeholder_label' => $allowedColumns[$ph['key']] ?? Str::title(str_replace('_', ' ', $ph['key'])),
                    'field_type' => 'text',
                    'is_manual' => false,
                    'source_column' => $ph['key'],
                    'sort_order' => $index,
                ]);
            }

            return response()->json(
                [
                    'success' => true,
                    'message' => 'Template berhasil dibuat dengan ' . count($placeholders) . ' placeholder.',
                    'data' => ['id' => $template->id, 'name' => $template->name],
                ],
                201,
            );
        } catch (\Exception $e) {
            Log::error('saveWithMapping failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal menyimpan template: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────────────────────────────────

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

            if (!empty($validated['manual_fields'])) {
                foreach ($validated['manual_fields'] as $field) {
                    $template->placeholders()->updateOrCreate(
                        ['placeholder_key' => $field['key']],
                        [
                            'placeholder_label' => $field['label'],
                            'field_type' => $field['type'] ?? 'text',
                            'is_manual' => true,
                            'default_value' => $field['default'] ?? null,
                            'options' => !empty($field['options']) ? (is_string($field['options']) ? explode(',', $field['options']) : $field['options']) : null,
                            'sort_order' => $field['sort_order'] ?? null,
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
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update template: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui template'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GENERATE FORM
    // ─────────────────────────────────────────────────────────────────────────

    public function generateForm(ReportTemplate $template)
    {
        $sourceTable = $template->source_table ?? '';
        $sourceData = !empty($sourceTable) ? $this->getSourceOptions($sourceTable) : collect();
        $placeholders = $template->placeholders->sortBy(fn($p) => [$p->is_manual ? 1 : 0, $p->sort_order]);

        return view('HR.template.generate', compact('template', 'sourceData', 'placeholders'));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GENERATE & DOWNLOAD
    // ─────────────────────────────────────────────────────────────────────────

    public function generate(Request $request, ReportTemplate $template)
    {
        try {
            $validated = $request->validate([
                'source_id' => 'required|integer',
                'report_title' => 'required|string|max:255',
                'manual_inputs' => 'nullable|array',
            ]);

            $sourceTable = $template->source_table ?? '';
            $modelClass = $this->getModelClass($sourceTable);
            $sourceExists = $modelClass::find($validated['source_id']);

            if (!$sourceExists) {
                return back()->with('error', 'Data sumber tidak ditemukan');
            }

            $sourceData = $this->generatorService->getSourceData($sourceTable, $validated['source_id']);
            $manualInputs = $validated['manual_inputs'] ?? [];

            Log::info('generate: source data', ['keys' => array_keys($sourceData)]);

            $outputPath = $this->generatorService->generateDocxReport($template, $sourceData, $manualInputs);

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

    // ─────────────────────────────────────────────────────────────────────────
    // DOWNLOAD
    // ─────────────────────────────────────────────────────────────────────────

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
        $mimeType = $extension === 'docx' ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' : 'application/pdf';

        return response()->download($storagePath, Str::slug($generation->report_title) . '.' . $extension, [
            'Content-Type' => $mimeType,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // HISTORY
    // ─────────────────────────────────────────────────────────────────────────

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
        $query = ReportGeneration::with(['template', 'generator'])->where('generated_by', Auth::id());
        if ($template) {
            $query->where('template_id', $template->id);
        }

        $generations = $query->latest()->take(50)->get()->map(
            fn($g) => [
                'id' => $g->id,
                'report_title' => $g->report_title,
                'source_type' => $g->source_type,
                'source_id' => $g->source_id,
                'created_at' => $g->created_at->format('d/m/Y H:i'),
                'user_name' => $g->generator?->name ?? '-',
                'status' => $g->status,
                'download_url' => route('HR.reports.download', $g),
            ],
        );

        return response()->json(['success' => true, 'data' => $generations]);
    }

    public function showDetails(ReportGeneration $generation): JsonResponse
    {
        if ($generation->generated_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $fileSize = Storage::disk('public')->exists($generation->output_file_path) ? round(Storage::disk('public')->size($generation->output_file_path) / 1024, 2) . ' KB' : '-';
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

    // ─────────────────────────────────────────────────────────────────────────
    // PLACEHOLDER MANAGEMENT
    // ─────────────────────────────────────────────────────────────────────────

    public function addPlaceholder(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'template_id' => 'required|exists:report_templates,id',
                'placeholder_label' => 'required|string|max:255',
                'placeholder_key' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9_]+$/', Rule::unique('template_placeholders', 'placeholder_key')->where('template_id', $request->template_id)],
                'field_type' => 'required|in:text,textarea,date,select,checkbox,number,currency',
                'is_manual' => 'required|boolean',
                'source_column' => 'nullable|required_if:is_manual,0|string|max:255',
                'options' => 'nullable|string',
                'default_value' => 'nullable|string',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            $template = ReportTemplate::findOrFail($validated['template_id']);

            if ($template->created_by !== Auth::id() && !Auth::user()->hasRole('admin')) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $placeholder = TemplatePlaceholder::create([
                'template_id' => $validated['template_id'],
                'placeholder_key' => $validated['placeholder_key'],
                'placeholder_label' => $validated['placeholder_label'],
                'field_type' => $validated['field_type'],
                'is_manual' => $validated['is_manual'],
                'source_column' => $validated['source_column'] ?? null,
                'options' => !empty($validated['options']) ? explode(',', $validated['options']) : null,
                'default_value' => $validated['default_value'] ?? null,
                'sort_order' => $validated['sort_order'] ?? (TemplatePlaceholder::where('template_id', $validated['template_id'])->max('sort_order') ?? 0) + 1,
            ]);

            return response()->json(['success' => true, 'message' => 'Field berhasil ditambahkan', 'data' => $placeholder], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Validasi gagal', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Failed to add placeholder: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menambahkan field'], 500);
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
                'field_type' => 'required|in:text,textarea,date,select,checkbox,number,currency',
                'is_manual' => 'required|boolean',
                'source_column' => 'nullable|string|max:255',
                'options' => 'nullable|string',
                'default_value' => 'nullable|string',
                'sort_order' => 'nullable|integer|min:0',
            ]);

            $placeholder->update([
                'placeholder_label' => $validated['placeholder_label'],
                'field_type' => $validated['field_type'],
                'is_manual' => $validated['is_manual'],
                'source_column' => $validated['source_column'] ?? null,
                'options' => !empty($validated['options']) ? explode(',', $validated['options']) : null,
                'default_value' => $validated['default_value'] ?? null,
                'sort_order' => $validated['sort_order'] ?? $placeholder->sort_order,
            ]);

            return response()->json(['success' => true, 'message' => 'Field berhasil diperbarui', 'data' => $placeholder]);
        } catch (\Exception $e) {
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
            return response()->json(['success' => false, 'message' => 'Gagal menghapus field'], 500);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────────

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
}
