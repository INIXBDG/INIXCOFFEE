<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReportSalesProjectController extends Controller
{
    public function index()
    {
        return view('report_project.index');
    }

    public function getRecapData(Request $request): JsonResponse
    {
        // Mengambil proyek yang tidak gagal dan memiliki nilai proyek
        $projects = Project::with(['client', 'administration.projectManager'])
            ->whereNotNull('nilai_proyek')
            ->where('phase', '!=', 'gagal')
            ->get();

        $totalSales = $projects->sum('nilai_proyek');
        $completedSales = $projects->where('phase', 'selesai')->sum('nilai_proyek');
        $potentialSales = $projects->whereIn('phase', ['administrasi', 'teknis'])->sum('nilai_proyek');

        return response()->json([
            'success' => true,
            'summary' => [
                'total_revenue' => $totalSales,
                'realized_revenue' => $completedSales,
                'pipeline_revenue' => $potentialSales,
            ],
            'data' => $projects
        ], 200);
    }
}