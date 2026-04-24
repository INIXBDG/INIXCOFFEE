<?php

namespace App\Http\Controllers\office;

use App\Http\Controllers\Controller;
use App\Models\AnalysisReport;
use App\Models\AnalysisYearDescription;
use App\Models\AnalysisQuarterDescription;
use App\Models\AnalysisAnnualReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AnalysisReportController extends Controller
{
    public function index(Request $request)
    {
        $allowedRoles = ['Finance & Accounting', 'GM'];
        $userRole = auth()->user()->karyawan->jabatan ?? null;

        if (!in_array($userRole, $allowedRoles)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki otorisasi untuk melihat halaman ini.');
        }

        $availableYears = AnalysisReport::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $query = AnalysisReport::with('user.karyawan');

        $selectedYear = $request->input('year_filter', date('Y'));
        $selectedQuarter = $request->input('quarter_filter');
        $selectedMonth = $request->input('month_filter');

        if (!empty($selectedYear)) {
            $query->where('year', $selectedYear);
            $years = collect([$selectedYear]);
        } else {
            $years = clone $availableYears;
        }

        if (!empty($selectedMonth)) {
            $query->where('month', $selectedMonth);
        } elseif (!empty($selectedQuarter)) {
            $monthsInQuarter = [
                1 => [1, 2, 3],
                2 => [4, 5, 6],
                3 => [7, 8, 9],
                4 => [10, 11, 12]
            ];
            $query->whereIn('month', $monthsInQuarter[$selectedQuarter]);
        }

        $reports = $query->get();
        $yearDescriptions = AnalysisYearDescription::whereIn('year', $years)->get()->keyBy('year');

        // Memuat Data Triwulan (Termasuk File)
        $quarterDescriptionsData = AnalysisQuarterDescription::whereIn('year', $years)->get();
        $quarterData = [];
        foreach ($quarterDescriptionsData as $qd) {
            $quarterData[$qd->year][$qd->quarter] = $qd;
        }

        $annualData = AnalysisAnnualReport::whereIn('year', $years)->get()->keyBy('year');

        return view('office.analysis.index', compact(
            'years', 'availableYears', 'reports', 'selectedYear',
            'selectedQuarter', 'selectedMonth', 'yearDescriptions', 'quarterData', 'annualData'
        ));
    }
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'nullable|string',
            'files'       => 'nullable|array',
            'files.*'     => 'file|max:10240',
            'year'        => 'required|digits:4|integer',
            'month'       => 'required|integer|between:1,12',
            'nilai'       => 'required',
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
            'nilai'       => $request->input('nilai'),
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
            'nilai'       => 'required'
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
            'nilai'       => $request->input('nilai'),
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
            'note'        => 'nullable|string',
        ]);

        AnalysisYearDescription::updateOrCreate(
            ['year' => $request->input('year')],
            [
                'description' => $request->input('description'),
                'note'        => $request->input('note')
            ]
        );

        return redirect()->back()->with('success', 'Deskripsi dan catatan tahun berhasil disimpan.');
    }

    public function updateQuarterDescription(Request $request)
    {
        $request->validate([
            'year'         => 'required|digits:4|integer',
            'quarter'      => 'required|integer|between:1,4',
            'description'  => 'nullable|string',
            'format_nilai' => 'nullable|string|max:255',
            'nilai'        => 'nullable|numeric',
            'files'        => 'nullable|array',
            'files.*'      => 'file|max:10240',
        ]);

        $qd = AnalysisQuarterDescription::firstOrNew([
            'year' => $request->input('year'),
            'quarter' => $request->input('quarter')
        ]);

        $filePaths = $qd->file_paths ?? [];

        if ($request->hasFile('files')) {
            if (!empty($filePaths)) {
                foreach ($filePaths as $oldFile) {
                    Storage::disk('public')->delete($oldFile);
                }
            }

            $filePaths = [];
            foreach ($request->file('files') as $file) {
                $path = $file->store('analysis_reports/quarter_files', 'public');
                $filePaths[] = $path;
            }
        }

        $qd->description  = $request->input('description');
        $qd->format_nilai = $request->input('format_nilai'); // Menyimpan format nilai
        $qd->nilai        = $request->input('nilai');        // Menyimpan nilai
        $qd->file_paths   = $filePaths;
        $qd->save();

        return redirect()->back()->with('success', 'Data Triwulan berhasil disimpan.');
    }

    // Fungsi Baru untuk Download File Triwulan
    public function downloadQuarter($year, $quarter, $index)
    {
        $qd = AnalysisQuarterDescription::where('year', $year)->where('quarter', $quarter)->firstOrFail();

        if (!isset($qd->file_paths[$index])) {
            abort(404, 'Data file tidak ditemukan.');
        }

        $path = $qd->file_paths[$index];
        $absolutePath = storage_path('app/public/' . $path);

        if (!file_exists($absolutePath)) {
            abort(404, 'File fisik tidak ditemukan pada server.');
        }

        $extension = pathinfo($absolutePath, PATHINFO_EXTENSION);
        $fileName = sprintf('LaporanTriwulan_%s_Q%s_%d.%s', $year, $quarter, $index + 1, $extension);

        $headers = [
            'Content-Type'        => mime_content_type($absolutePath),
            'Content-Disposition' => 'inline; filename="' . $fileName . '"'
        ];

        return response()->file($absolutePath, $headers);
    }

    public function updateAnnualReport(Request $request)
    {
        $request->validate([
            'year'        => 'required|digits:4|integer',
            'description' => 'nullable|string',
            'files'       => 'nullable|array',
            'files.*'     => 'file|max:10240',
        ]);

        $ar = AnalysisAnnualReport::firstOrNew(['year' => $request->input('year')]);

        $filePaths = $ar->file_paths ?? [];

        if ($request->hasFile('files')) {
            // Hapus file lama jika ada unggahan baru
            if (!empty($filePaths)) {
                foreach ($filePaths as $oldFile) {
                    Storage::disk('public')->delete($oldFile);
                }
            }

            $filePaths = [];
            foreach ($request->file('files') as $file) {
                $path = $file->store('analysis_reports/annual_files', 'public');
                $filePaths[] = $path;
            }
        }

        $ar->description = $request->input('description');
        $ar->file_paths = $filePaths;
        $ar->save();

        return redirect()->back()->with('success', 'Laporan Tahunan berhasil disimpan.');
    }

    public function downloadAnnual($year, $index)
    {
        $ar = AnalysisAnnualReport::where('year', $year)->firstOrFail();

        if (!isset($ar->file_paths[$index])) {
            abort(404);
        }

        $path = $ar->file_paths[$index];
        $absolutePath = storage_path('app/public/' . $path);
        $extension = pathinfo($absolutePath, PATHINFO_EXTENSION);
        $fileName = sprintf('LaporanTahunan_Final_%s_%d.%s', $year, $index + 1, $extension);

        return response()->file($absolutePath, [
            'Content-Type' => mime_content_type($absolutePath),
            'Content-Disposition' => 'inline; filename="' . $fileName . '"'
        ]);
    }
}
