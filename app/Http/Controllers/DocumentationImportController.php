<?php

namespace App\Http\Controllers;

use App\Models\CodeDocumentation;
use App\Models\FeatureDocumentation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Illuminate\Support\Str;

class DocumentationImportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected array $validStatuses = ['draft', 'development', 'production', 'deprecated'];
    protected array $validFlowTypes = ['text', 'mermaid'];
    protected array $validLanguages = ['php', 'javascript', 'sql', 'html', 'css', 'bash'];

    /**
     * Download Excel template (4 sheet + petunjuk pengisian).
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $this->buildFeaturesSheet($spreadsheet);
        $this->buildCodeDocsSheet($spreadsheet);
        $this->buildCodeBlocksSheet($spreadsheet);
        $this->buildChangeLogsSheet($spreadsheet);
        $this->buildInstructionsSheet($spreadsheet);

        $spreadsheet->setActiveSheetIndex(0);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $filename = 'Template_Import_Dokumentasi.xlsx';
        $tempPath = storage_path('app/' . $filename);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    protected function buildFeaturesSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Features');

        $headers = ['no', 'parent_no', 'name', 'category', 'status', 'short_description', 'purpose', 'problem_solved', 'how_it_works', 'user_access'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaderRow($sheet, count($headers));

        $example = [[1, '', 'Manajemen KPI', 'HRD', 'production', 'Modul untuk mengelola KPI karyawan', 'Membantu HRD menilai kinerja karyawan secara terukur', 'Penilaian kinerja masih manual via Excel', 'HRD input target, karyawan input capaian, sistem hitung skor', 'HRD (full access), Karyawan (input capaian)'], [2, 1, 'Target Divisi', 'HRD', 'development', 'Sub fitur untuk menetapkan target per divisi', 'Menjabarkan target KPI perusahaan ke level divisi', '', '', '']];
        $sheet->fromArray($example, null, 'A2');

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setWidth(28);
        }
    }

    protected function buildCodeDocsSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('CodeDocs');

        $headers = ['no', 'feature_no', 'title', 'description', 'flow_type', 'flow_content', 'relations', 'future_development'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaderRow($sheet, count($headers));

        $example = [[1, 1, 'Implementasi Controller Target', 'Menjelaskan alur controller penyimpanan target KPI', 'mermaid', "graph TD\nA[Request] --> B[Validasi]\nB --> C[Simpan ke DB]", 'User, KpiTarget', 'Tambah validasi rentang tanggal | Tambah export PDF']];
        $sheet->fromArray($example, null, 'A2');

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setWidth(28);
        }
    }

    protected function buildCodeBlocksSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('CodeBlocks');

        $headers = ['codedoc_no', 'description', 'language', 'code'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaderRow($sheet, count($headers));

        $example = [[1, 'Method store pada controller', 'php', "public function store(Request \$request)\n{\n    // ...\n}"]];
        $sheet->fromArray($example, null, 'A2');

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setWidth(35);
        }
    }

    protected function buildChangeLogsSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ChangeLogs');

        $headers = ['codedoc_no', 'version', 'date', 'summary', 'details'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaderRow($sheet, count($headers));

        $example = [[1, '1.0', '2026-01-15', 'Rilis awal', 'Implementasi pertama fitur target divisi']];
        $sheet->fromArray($example, null, 'A2');

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setWidth(28);
        }
    }

    protected function buildInstructionsSheet(Spreadsheet $spreadsheet): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Petunjuk');
        $sheet->getColumnDimension('A')->setWidth(110);

        $lines = [
            'PETUNJUK PENGISIAN TEMPLATE IMPORT DOKUMENTASI',
            '',
            '1. Sheet "Features" = daftar fitur/sub-fitur.',
            '   - Kolom "no" wajib diisi angka unik (1, 2, 3, ...), hanya penghubung antar baris, tidak disimpan ke database.',
            '   - Kolom "parent_no" diisi nilai "no" fitur induknya. Kosongkan jika ini fitur utama.',
            '   - status hanya boleh: draft, development, production, deprecated.',
            '',
            '2. Sheet "CodeDocs" = dokumentasi kode, wajib terhubung ke sebuah fitur.',
            '   - "feature_no" wajib diisi sesuai "no" pada sheet Features.',
            '   - "flow_type" hanya boleh: text atau mermaid (kosongkan jika tidak ada flow).',
            '   - "relations" diisi nama model dipisah koma. Contoh: User, Product, Order',
            '   - "future_development" diisi list ide dipisah tanda " | ". Contoh: Ide A | Ide B',
            '',
            '3. Sheet "CodeBlocks" (opsional) = potongan kode untuk sebuah CodeDoc.',
            '   - "codedoc_no" wajib sesuai "no" pada sheet CodeDocs. Boleh diulang untuk banyak blok kode.',
            '   - "language": php, javascript, sql, html, css, bash.',
            '',
            '4. Sheet "ChangeLogs" (opsional) = riwayat versi untuk sebuah CodeDoc.',
            '   - "codedoc_no" wajib sesuai "no" pada sheet CodeDocs. "date" format YYYY-MM-DD.',
            '',
            '5. Setiap import SELALU membuat data baru, walaupun nama/judul sudah ada di database.',
            '6. Hapus baris contoh sebelum mengisi data asli, atau timpa langsung isinya.',
        ];

        foreach ($lines as $i => $line) {
            $sheet->setCellValue('A' . ($i + 1), $line);
            $sheet
                ->getStyle('A' . ($i + 1))
                ->getAlignment()
                ->setWrapText(true);
        }
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
    }

    protected function styleHeaderRow($sheet, int $colCount): void
    {
        $lastCol = Coordinate::stringFromColumnIndex($colCount);
        $range = "A1:{$lastCol}1";
        $sheet->getStyle($range)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($range)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('6366F1');
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    /**
     * Proses file Excel yang diupload: parse lalu insert Features + CodeDocumentations.
     * Selalu insert baru (tidak ada pengecekan duplikat nama/judul).
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls',
        ]);

        try {
            $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        } catch (\Exception $e) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'File tidak valid atau rusak: ' . $e->getMessage(),
                ],
                422,
            );
        }

        $featuresRaw = $this->readSheet($spreadsheet, 'Features');
        $codeDocsRaw = $this->readSheet($spreadsheet, 'CodeDocs');
        $codeBlocksRaw = $this->readSheet($spreadsheet, 'CodeBlocks');
        $changeLogsRaw = $this->readSheet($spreadsheet, 'ChangeLogs');

        if (empty($featuresRaw)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Sheet "Features" kosong/tidak ditemukan. Gunakan template yang benar.',
                ],
                422,
            );
        }

        $errors = $this->validateRows($featuresRaw, $codeDocsRaw, $codeBlocksRaw, $changeLogsRaw);
        if (!empty($errors)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Ditemukan kesalahan pada file.',
                    'errors' => $errors,
                ],
                422,
            );
        }

        $userId = Auth::id();
        $summary = ['features' => 0, 'code_docs' => 0, 'code_blocks' => 0, 'change_logs' => 0];

        DB::beginTransaction();
        try {
            // Pass 1: insert semua feature dulu tanpa parent_id, simpan map no -> id asli
            $featureIdMap = [];
            foreach ($featuresRaw as $row) {
                $feature = FeatureDocumentation::create([
                    'name' => $row['name'],
                    'category' => $row['category'],
                    'status' => $row['status'] ?: 'draft',
                    'short_description' => $row['short_description'],
                    'purpose' => $row['purpose'],
                    'problem_solved' => $row['problem_solved'] ?: null,
                    'how_it_works' => $row['how_it_works'] ?: null,
                    'user_access' => $row['user_access'] ?: null,
                    'update_by' => $userId,
                    'log_update' => [$userId],
                    'log_time_update' => [now()->toDateTimeString()],
                ]);
                $featureIdMap[$row['no']] = $feature->id;
                $summary['features']++;
            }

            // Pass 2: set parent_id sekarang id asli sudah lengkap (aman untuk urutan baris apapun)
            foreach ($featuresRaw as $row) {
                if (!empty($row['parent_no']) && isset($featureIdMap[$row['parent_no']])) {
                    FeatureDocumentation::where('id', $featureIdMap[$row['no']])->update(['parent_id' => $featureIdMap[$row['parent_no']]]);
                }
            }

            $codeBlocksByDoc = collect($codeBlocksRaw)->groupBy('codedoc_no');
            $changeLogsByDoc = collect($changeLogsRaw)->groupBy('codedoc_no');

            foreach ($codeDocsRaw as $row) {
                if (!isset($featureIdMap[$row['feature_no']])) {
                    continue;
                }

                $codeBlocks = $codeBlocksByDoc
                    ->get($row['no'], collect())
                    ->map(
                        fn($b) => [
                            'description' => $b['description'] ?: null,
                            'language' => $b['language'] ?: 'php',
                            'code' => $b['code'],
                        ],
                    )
                    ->values()
                    ->all();

                $changeLogs = $changeLogsByDoc
                    ->get($row['no'], collect())
                    ->map(
                        fn($c) => [
                            'version' => $c['version'],
                            'date' => $c['date'] ?: null,
                            'summary' => $c['summary'] ?: null,
                            'details' => $c['details'] ?: null,
                        ],
                    )
                    ->values()
                    ->all();

                $relations = $row['relations'] ? array_values(array_filter(array_map('trim', explode(',', $row['relations'])))) : [];

                $futureDevelopment = $row['future_development'] ? array_values(array_filter(array_map('trim', explode('|', $row['future_development'])))) : [];

                $data = [
                    'feature_documentation_id' => $featureIdMap[$row['feature_no']],
                    'title' => $row['title'],
                    'description' => $row['description'] ?: null,
                    'code_blocks' => $codeBlocks,
                    'relations' => $relations,
                    'change_logs' => $changeLogs,
                    'future_development' => $futureDevelopment,
                    'update_by' => $userId,
                    'log_update' => [$userId],
                    'log_time_update' => [now()->toDateTimeString()],
                    'log_changes' => [
                        [
                            'action' => 'created',
                            'fields' => ['title', 'description', 'code_blocks', 'relations', 'change_logs', 'future_development'],
                        ],
                    ],
                ];

                if (!empty($row['flow_type']) && !empty($row['flow_content'])) {
                    $data['flow_program'] = [
                        'type' => $row['flow_type'],
                        'content' => $row['flow_content'],
                    ];
                }

                CodeDocumentation::create($data);
                $summary['code_docs']++;
                $summary['code_blocks'] += count($codeBlocks);
                $summary['change_logs'] += count($changeLogs);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Import gagal: ' . $e->getMessage(),
                ],
                500,
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Import berhasil.',
            'summary' => $summary,
        ]);
    }

    /**
     * Baca satu sheet menjadi array asosiatif berdasarkan header baris pertama.
     * Baris yang seluruh kolomnya kosong akan dilewati.
     */
    protected function readSheet(Spreadsheet $spreadsheet, string $sheetName): array
    {
        if (!$spreadsheet->sheetNameExists($sheetName)) {
            return [];
        }

        $sheet = $spreadsheet->getSheetByName($sheetName);
        $rows = $sheet->toArray(null, true, true, false);

        if (empty($rows)) {
            return [];
        }

        $headers = array_map(fn($h) => trim((string) $h), array_shift($rows));

        $result = [];
        foreach ($rows as $row) {
            if (collect($row)->filter(fn($v) => trim((string) $v) !== '')->isEmpty()) {
                continue;
            }
            $assoc = [];
            foreach ($headers as $i => $header) {
                if ($header === '') {
                    continue;
                }
                $assoc[$header] = isset($row[$i]) ? trim((string) $row[$i]) : '';
            }
            $result[] = $assoc;
        }

        return $result;
    }

    /**
     * Validasi field wajib & referensi silang (feature_no / codedoc_no) sebelum masuk ke DB.
     */
    protected function validateRows(array $features, array $codeDocs, array $codeBlocks, array $changeLogs): array
    {
        $errors = [];
        $featureNos = [];

        foreach ($features as $i => $row) {
            $line = $i + 2;
            if (empty($row['no'])) {
                $errors[] = "Features baris {$line}: kolom 'no' wajib diisi.";
                continue;
            }
            if (isset($featureNos[$row['no']])) {
                $errors[] = "Features baris {$line}: nilai 'no' = {$row['no']} duplikat.";
            }
            $featureNos[$row['no']] = true;

            foreach (['name', 'category', 'short_description', 'purpose'] as $field) {
                if (empty($row[$field])) {
                    $errors[] = "Features baris {$line}: kolom '{$field}' wajib diisi.";
                }
            }
            if (!empty($row['status']) && !in_array($row['status'], $this->validStatuses)) {
                $errors[] = "Features baris {$line}: status '{$row['status']}' tidak valid.";
            }
        }

        $codeDocNos = [];
        foreach ($codeDocs as $i => $row) {
            $line = $i + 2;
            if (empty($row['no'])) {
                $errors[] = "CodeDocs baris {$line}: kolom 'no' wajib diisi.";
                continue;
            }
            $codeDocNos[$row['no']] = true;

            if (empty($row['feature_no']) || !isset($featureNos[$row['feature_no']])) {
                $errors[] = "CodeDocs baris {$line}: 'feature_no' = '{$row['feature_no']}' tidak ditemukan di sheet Features.";
            }
            if (empty($row['title'])) {
                $errors[] = "CodeDocs baris {$line}: kolom 'title' wajib diisi.";
            }
            if (!empty($row['flow_type']) && !in_array($row['flow_type'], $this->validFlowTypes)) {
                $errors[] = "CodeDocs baris {$line}: flow_type '{$row['flow_type']}' tidak valid.";
            }
        }

        foreach ($codeBlocks as $i => $row) {
            $line = $i + 2;
            if (empty($row['codedoc_no']) || !isset($codeDocNos[$row['codedoc_no']])) {
                $errors[] = "CodeBlocks baris {$line}: 'codedoc_no' = '{$row['codedoc_no']}' tidak ditemukan di sheet CodeDocs.";
            }
            if (empty($row['code'])) {
                $errors[] = "CodeBlocks baris {$line}: kolom 'code' wajib diisi.";
            }
            if (!empty($row['language']) && !in_array($row['language'], $this->validLanguages)) {
                $errors[] = "CodeBlocks baris {$line}: language '{$row['language']}' tidak valid.";
            }
        }

        foreach ($changeLogs as $i => $row) {
            $line = $i + 2;
            if (empty($row['codedoc_no']) || !isset($codeDocNos[$row['codedoc_no']])) {
                $errors[] = "ChangeLogs baris {$line}: 'codedoc_no' = '{$row['codedoc_no']}' tidak ditemukan di sheet CodeDocs.";
            }
            if (empty($row['version'])) {
                $errors[] = "ChangeLogs baris {$line}: kolom 'version' wajib diisi.";
            }
        }

        return $errors;
    }

    public function exportAll()
    {
        $features = FeatureDocumentation::with('codeDocumentations')->orderBy('parent_id')->orderBy('id')->get();

        return $this->buildExportResponse($features, 'Export_Semua_Dokumentasi.xlsx');
    }

    public function exportFeature($id)
    {
        $feature = FeatureDocumentation::with(['codeDocumentations', 'childrenRecursive.codeDocumentations', 'childrenRecursive.childrenRecursive'])->findOrFail($id);

        $features = collect();
        $this->flattenFeatureTree($feature, $features);

        $filename = 'Export_' . Str::slug($feature->name) . '.xlsx';

        return $this->buildExportResponse($features, $filename);
    }

    protected function flattenFeatureTree(FeatureDocumentation $feature, $collection): void
    {
        $collection->push($feature);
        foreach ($feature->childrenRecursive ?? [] as $child) {
            $this->flattenFeatureTree($child, $collection);
        }
    }

    protected function buildExportResponse($features, string $filename)
    {
        $features = $features->values();

        $featureNoMap = [];
        foreach ($features as $i => $feature) {
            $featureNoMap[$feature->id] = $i + 1;
        }

        $codeDocs = collect();
        foreach ($features as $feature) {
            foreach ($feature->codeDocumentations as $doc) {
                $codeDocs->push($doc);
            }
        }

        $codeDocNoMap = [];
        foreach ($codeDocs as $i => $doc) {
            $codeDocNoMap[$doc->id] = $i + 1;
        }

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $this->writeFeaturesExportSheet($spreadsheet, $features, $featureNoMap);
        $this->writeCodeDocsExportSheet($spreadsheet, $codeDocs, $codeDocNoMap, $featureNoMap);
        $this->writeCodeBlocksExportSheet($spreadsheet, $codeDocs, $codeDocNoMap);
        $this->writeChangeLogsExportSheet($spreadsheet, $codeDocs, $codeDocNoMap);
        $this->buildInstructionsSheet($spreadsheet); 

        $spreadsheet->setActiveSheetIndex(0);

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $tempPath = storage_path('app/' . $filename);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    protected function writeFeaturesExportSheet(Spreadsheet $spreadsheet, $features, array $featureNoMap): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Features');

        $headers = ['no', 'parent_no', 'name', 'category', 'status', 'short_description', 'purpose', 'problem_solved', 'how_it_works', 'user_access'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaderRow($sheet, count($headers));

        $rowNum = 2;
        foreach ($features as $feature) {
            $parentNo = $feature->parent_id && isset($featureNoMap[$feature->parent_id]) ? $featureNoMap[$feature->parent_id] : '';

            $sheet->fromArray([$featureNoMap[$feature->id], $parentNo, $feature->name, $feature->category, $feature->status, $feature->short_description, $feature->purpose, $feature->problem_solved, $feature->how_it_works, $feature->user_access], null, 'A' . $rowNum);
            $rowNum++;
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setWidth(28);
        }
    }

    protected function writeCodeDocsExportSheet(Spreadsheet $spreadsheet, $codeDocs, array $codeDocNoMap, array $featureNoMap): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('CodeDocs');

        $headers = ['no', 'feature_no', 'title', 'description', 'flow_type', 'flow_content', 'relations', 'future_development'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaderRow($sheet, count($headers));

        $rowNum = 2;
        foreach ($codeDocs as $doc) {
            $flowType = $doc->flow_program['type'] ?? '';
            $flowContent = $doc->flow_program['content'] ?? '';
            $relations = $doc->relations ? implode(', ', $doc->relations) : '';
            $futureDev = $doc->future_development ? implode(' | ', $doc->future_development) : '';

            $sheet->fromArray([$codeDocNoMap[$doc->id], $featureNoMap[$doc->feature_documentation_id] ?? '', $doc->title, $doc->description, $flowType, $flowContent, $relations, $futureDev], null, 'A' . $rowNum);
            $rowNum++;
        }

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setWidth(28);
        }
    }

    protected function writeCodeBlocksExportSheet(Spreadsheet $spreadsheet, $codeDocs, array $codeDocNoMap): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('CodeBlocks');

        $headers = ['codedoc_no', 'description', 'language', 'code'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaderRow($sheet, count($headers));

        $rowNum = 2;
        foreach ($codeDocs as $doc) {
            foreach ($doc->code_blocks ?? [] as $block) {
                $sheet->fromArray([$codeDocNoMap[$doc->id], $block['description'] ?? '', $block['language'] ?? 'php', $block['code'] ?? ''], null, 'A' . $rowNum);
                $rowNum++;
            }
        }

        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setWidth(35);
        }
    }

    protected function writeChangeLogsExportSheet(Spreadsheet $spreadsheet, $codeDocs, array $codeDocNoMap): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('ChangeLogs');

        $headers = ['codedoc_no', 'version', 'date', 'summary', 'details'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeaderRow($sheet, count($headers));

        $rowNum = 2;
        foreach ($codeDocs as $doc) {
            foreach ($doc->change_logs ?? [] as $log) {
                $sheet->fromArray([$codeDocNoMap[$doc->id], $log['version'] ?? '', $log['date'] ?? '', $log['summary'] ?? '', $log['details'] ?? ''], null, 'A' . $rowNum);
                $rowNum++;
            }
        }

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setWidth(28);
        }
    }
}
