<?php

namespace App\Http\Controllers\office;

use App\Http\Controllers\Controller;
use App\Models\AnalysisReport;
use App\Models\AnalysisYearDescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnalysisReportController extends Controller
{
    public function index(Request $request)
    {
        $allowedRoles = ['Finance & Accounting', 'GM', 'HRD'];
        $userRole = auth()->user()->karyawan->jabatan ?? null;

        if (!in_array($userRole, $allowedRoles)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otorisasi untuk melihat halaman ini.');
        }
        $availableYears = AnalysisReport::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
        $query = AnalysisReport::with('user.karyawan');
        $selectedYear = $request->has('year_filter') ? $request->input('year_filter') : date('Y');

        if (!empty($selectedYear)) {
            $query->where('year', $selectedYear);
            $years = collect([$selectedYear]);
        } else {
            $years = clone $availableYears;
        }

        $reports = $query->get();
        $yearDescriptions = AnalysisYearDescription::whereIn('year', $years)->pluck('description', 'year');

        return view('office.analysis.index', compact('years', 'availableYears', 'reports', 'selectedYear', 'yearDescriptions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'description' => 'nullable|string',
            'files'       => 'nullable|array',
            'files.*'     => 'file|max:10240',
            'year'        => 'required|digits:4|integer',
            'month'       => 'required|integer|between:1,12',
        ]);

        $filePaths = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $path = $file->store('analysis_reports/files', 'public');
                $filePaths[] = $path;
            }
        }

        AnalysisReport::create([
            'user_id'     => Auth::id(),
            'description' => $request->input('description'),
            'file_paths'  => $filePaths,
            'year'        => $request->input('year'),
            'month'       => $request->input('month'),
        ]);

        return redirect()->back()->with('success', 'Laporan analisis berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $report = AnalysisReport::findOrFail($id);

        $request->validate([
            'description' => 'nullable|string',
            'files'       => 'nullable|array',
            'files.*'     => 'file|max:10240',
            'year'        => 'required|digits:4|integer',
            'month'       => 'required|integer|between:1,12',
        ]);

        $filePaths = $report->file_paths ?? [];

        if ($request->hasFile('files')) {
            if (!empty($filePaths)) {
                foreach ($filePaths as $oldFile) {
                    Storage::disk('public')->delete($oldFile);
                }
            }

            $filePaths = [];
            foreach ($request->file('files') as $file) {
                $path = $file->store('analysis_reports/files', 'public');
                $filePaths[] = $path;
            }
        }

        $report->update([
            'description' => $request->input('description'),
            'file_paths'  => $filePaths,
            'year'        => $request->input('year'),
            'month'       => $request->input('month'),
        ]);

        return redirect()->back()->with('success', 'Laporan analisis berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $report = AnalysisReport::findOrFail($id);

        if (!empty($report->file_paths)) {
            foreach ($report->file_paths as $file) {
                Storage::disk('public')->delete($file);
            }
        }

        $report->delete();

        return redirect()->back()->with('success', 'Laporan analisis berhasil dihapus.');
    }

    public function download($id, $index)
    {
        $report = AnalysisReport::findOrFail($id);

        if (!isset($report->file_paths[$index])) {
            abort(404, 'Data file tidak ditemukan.');
        }

        $path = $report->file_paths[$index];
        $absolutePath = storage_path('app/public/' . $path);

        if (!file_exists($absolutePath)) {
            abort(404, 'File fisik tidak ditemukan pada server.');
        }

        $extension = pathinfo($absolutePath, PATHINFO_EXTENSION);

        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $monthName = $months[$report->month] ?? $report->month;

        $fileName = sprintf('LaporanAnalis_%s_%s_%d.%s', $report->year, $monthName, $index + 1, $extension);

        $headers = [
            'Content-Type'        => mime_content_type($absolutePath),
            'Content-Disposition' => 'inline; filename="' . $fileName . '"'
        ];

        return response()->file($absolutePath, $headers);
    }

    public function updateYearDescription(Request $request)
    {
        $request->validate([
            'year'        => 'required|digits:4|integer',
            'description' => 'nullable|string',
        ]);

        AnalysisYearDescription::updateOrCreate(
            ['year' => $request->input('year')],
            ['description' => $request->input('description')]
        );

        return redirect()->back()->with('success', 'Deskripsi tahun berhasil disimpan.');
    }
}
