<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadProject; // Integrasi Model Lead
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportSalesProjectController extends Controller
{
    public function index()
    {
        return view('report_project.index');
    }

    public function getRecapData(Request $request): JsonResponse
    {
        $year = $request->input('year', Carbon::now()->year);

        // ==========================================
        // 1. Agregasi Data Keuangan (Project)
        // Berdasarkan tahun_periode LeadProject
        // ==========================================

        $projects = Project::with([
            'client',
            'administration.projectManager',
            'lead',
        ])
            ->whereHas('lead', function ($query) use ($year) {
                $query->where('tahun_periode', $year);
            })
            ->whereNotNull('nilai_proyek')
            ->where('phase', '!=', 'gagal')
            ->get();

        $totalSales = $projects->sum('nilai_proyek');

        $completedSales = $projects
            ->where('phase', 'selesai')
            ->sum('nilai_proyek');

        $potentialSales = $projects
            ->whereIn('phase', ['administrasi', 'teknis'])
            ->sum('nilai_proyek');

        // ==========================================
        // 2. Leads Awal
        // ==========================================

        $leadsAwal = LeadProject::where('tahun_periode', $year)
            ->whereIn('status', [
                'penawaran_awal',
                'permintaan_klien',
                'meeting_klien',
            ])
            ->count();

        // ==========================================
        // 3. Prospek Aktif
        // ==========================================

        $prospekAktif = LeadProject::where('tahun_periode', $year)
            ->whereIn('status', [
                'dokumen_penawaran',
                'mengirim_proposal_teknis',
                'surat_penawaran',
            ])
            ->count();

        // ==========================================
        // 4. Closing Won
        // ==========================================

        $closingWon = LeadProject::where('tahun_periode', $year)
            ->where('status', 'won')
            ->count();

        // ==========================================
        // 5. Closing Lost
        // ==========================================

        $closingLost = LeadProject::withTrashed()
            ->where('tahun_periode', $year)
            ->where('status', 'lost')
            ->count();

        return response()->json([
            'success' => true,

            'year' => $year,

            'summary' => [
                'total_revenue' => $totalSales,
                'realized_revenue' => $completedSales,
                'pipeline_revenue' => $potentialSales,
                'leads_awal' => $leadsAwal,
                'prospek_aktif' => $prospekAktif,
                'closing_won' => $closingWon,
                'closing_lost' => $closingLost,
            ],

            'data' => $projects,
        ], 200);
    }
}
