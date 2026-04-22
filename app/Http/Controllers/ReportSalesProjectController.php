<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Lead; // Integrasi Model Lead
use App\Models\LeadProject;
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
        // 1. Agregasi Data Keuangan (Project)
        $projects = Project::with(['client', 'administration.projectManager'])
            ->whereNotNull('nilai_proyek')
            ->where('phase', '!=', 'gagal')
            ->get();

        $totalSales = $projects->sum('nilai_proyek');
        $completedSales = $projects->where('phase', 'selesai')->sum('nilai_proyek');
        $potentialSales = $projects->whereIn('phase', ['administrasi', 'teknis'])->sum('nilai_proyek');

        // 2. Agregasi Data Kuantitas (Leads & Prospek)
        // Leads Awal: Tahapan sebelum dokumen penawaran
        $leadsAwal = LeadProject::whereIn('status', ['penawaran_awal', 'permintaan_klien', 'meeting_klien'])->count();
        
        // Prospek Aktif: Tahapan dokumen berjalan namun belum final
        $prospekAktif = LeadProject::whereIn('status', ['dokumen_penawaran', 'mengirim_proposal_teknis', 'surat_penawaran'])->count();
        
        // Closing: Berhasil (Won) dan Gagal (Lost - membaca data Soft Delete)
        $closingWon = LeadProject::where('status', 'won')->count();
        $closingLost = LeadProject::withTrashed()->where('status', 'lost')->count();

        return response()->json([
            'success' => true,
            'summary' => [
                'total_revenue' => $totalSales,
                'realized_revenue' => $completedSales,
                'pipeline_revenue' => $potentialSales,
                'leads_awal' => $leadsAwal,
                'prospek_aktif' => $prospekAktif,
                'closing_won' => $closingWon,
                'closing_lost' => $closingLost,
            ],
            'data' => $projects
        ], 200);
    }
}