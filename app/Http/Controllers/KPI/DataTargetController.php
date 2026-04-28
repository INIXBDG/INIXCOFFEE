<?php

namespace App\Http\Controllers\KPI;

use App\Http\Controllers\Controller;
use App\Models\DataTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;

class DataTargetController extends Controller
{
    public function index()
    {
        $dataTargets = DataTarget::orderBy('asistant_route', 'asc')->get();
        return view('KPIdata.DataTarget.index', compact('dataTargets'));
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="template_data_target.xlsx"',
        ];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'asistant_route');
        $sheet->setCellValue('B1', 'jangka_target');
        $sheet->setCellValue('C1', 'tipe_target');
        $sheet->setCellValue('D1', 'nilai_target');

        $sheet->setCellValue('A2', 'Contoh: Kepuasan Pelanggan');
        $sheet->setCellValue('B2', 'Tahunan');
        $sheet->setCellValue('C2', 'persen');
        $sheet->setCellValue('D2', '85');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        ob_start();
        $writer->save('php://output');
        return response(ob_get_clean(), 200, $headers);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file_import' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $file = $request->file('file_import');
        $path = $file->getRealPath();

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $success = 0;
        $failed = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                if (empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3])) {
                    $failed++;
                    $errors[] = 'Baris ' . ($i + 1) . ': Data tidak lengkap';
                    continue;
                }

                $asistant_route = trim($row[0]);
                $jangka_target = trim($row[1]);
                $tipe_target = trim($row[2]);
                $nilai_target = preg_replace('/[^\d]/', '', $row[3]);

                if (!in_array($jangka_target, ['Tahunan'])) {
                    $failed++;
                    $errors[] = 'Baris ' . ($i + 1) . ': Jangka target tidak valid';
                    continue;
                }

                if (!in_array($tipe_target, ['angka', 'rupiah', 'persen'])) {
                    $failed++;
                    $errors[] = 'Baris ' . ($i + 1) . ': Tipe target tidak valid';
                    continue;
                }

                DataTarget::updateOrCreate(
                    ['asistant_route' => $asistant_route],
                    [
                        'jangka_target' => $jangka_target,
                        'tipe_target' => $tipe_target,
                        'nilai_target' => (float) $nilai_target,
                    ],
                );
                $success++;
            }

            DB::commit();

            $message = "Import selesai: {$success} data berhasil";
            if ($failed > 0) {
                $message .= ", {$failed} data gagal";
            }
            if (!empty($errors)) {
                $message .= '. Error: ' . implode('; ', array_slice($errors, 0, 3));
            }

            return response()->json(['message' => $message], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Import gagal: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $dataTarget = DataTarget::findOrFail($id);

        $validated = $request->validate([
            'jangka_target' => 'required|in:Tahunan,Bulanan,Kuartalan,Mingguan',
            'tipe_target' => 'required|in:angka,rupiah,persen',
            'nilai_target' => 'required|numeric|min:0',
        ]);

        $dataTarget->update($validated);

        return response()->json([
            'message' => 'Data Target berhasil diperbarui',
            'data' => $dataTarget,
        ]);
    }

    public function destroy($id)
    {
        $dataTarget = DataTarget::findOrFail($id);

        $hasRelation = \App\Models\targetKPI::where('id_data_target', $id)->exists();

        if ($hasRelation) {
            return response()->json(
                [
                    'message' => 'Data Target tidak dapat dihapus karena sudah digunakan dalam target KPI',
                ],
                422,
            );
        }

        $dataTarget->delete();

        return response()->json([
            'message' => 'Data Target berhasil dihapus',
        ]);
    }

    public function getDataTargets()
    {
        $dataTargets = DataTarget::select('id', 'asistant_route', 'jangka_target', 'tipe_target', 'nilai_target')->get();
        return response()->json($dataTargets);
    }
}
