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
        $startYear = 2020; // Tahun awal yang ditentukan
        $currentYear = (int) date('Y');
        if ($currentYear < $startYear) {
            $currentYear = $startYear;
        }

        $allYears = range($startYear, $currentYear);

        // Jika opsi tahun melebihi 5 tahun, ambil 5 tahun terakhir secara otomatis
        if (count($allYears) > 5) {
            $dropdownYears = array_slice($allYears, -6);
        } else {
            $dropdownYears = $allYears;
        }
            $dropdownYears = array_reverse($dropdownYears); 
        return view('report_project.index', compact('dropdownYears'));
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
            ->where('phase', '!=', 'gagal');

        // 2. Agregasi Data Kuantitas (Leads & Prospek)
        $leadsAwalQuery = LeadProject::whereIn('status', ['penawaran_awal', 'permintaan_klien', 'meeting_klien']);
        $prospekAktifQuery = LeadProject::whereIn('status', ['dokumen_penawaran', 'mengirim_proposal_teknis', 'surat_penawaran']);
        $closingWonQuery = LeadProject::where('status', 'won');
        $closingLostQuery = LeadProject::withTrashed()->where('status', 'lost');

        // Filter jika memilih tahun spesifik (bukan 'all' / 'Semua')
        if ($year && $year !== 'all' && $year !== 'Semua') {
            $projectsQuery->where(function ($q) use ($year) {
                // 1. Jika proyek terikat ke Lead, wajib berpatokan pada tahun_periode dari lead
                $q->whereHas('lead', function ($lq) use ($year) {
                    $lq->where(function ($lsub) use ($year) {
                        $lsub->where('tahun_periode', $year)
                             ->orWhere(function ($lsub2) use ($year) {
                                 $lsub2->whereNull('tahun_periode')
                                       ->whereYear('created_at', $year);
                             });
                    });
                })
                // 2. Jika proyek TIDAK terikat ke Lead, baru berpatokan pada tanggal_awal atau created_at proyek
                ->orWhere(function ($q2) use ($year) {
                    $q2->whereDoesntHave('lead')
                       ->where(function ($q3) use ($year) {
                           $q3->whereYear('tanggal_awal', $year)
                              ->orWhere(function ($q4) use ($year) {
                                  $q4->whereNull('tanggal_awal')
                                     ->whereYear('created_at', $year);
                              });
                       });
                });
            });

            $filterLeadYear = function ($query) use ($year) {
                $query->where(function ($q) use ($year) {
                    $q->where('tahun_periode', $year)
                      ->orWhere(function ($q2) use ($year) {
                          $q2->whereNull('tahun_periode')->whereYear('created_at', $year);
                      });
                });
            };

            $filterLeadYear($leadsAwalQuery);
            $filterLeadYear($prospekAktifQuery);
            $filterLeadYear($closingWonQuery);
            $filterLeadYear($closingLostQuery);
        }

        $projects = $projectsQuery->get();

        $totalSales = $projects->sum('nilai_proyek');
        $completedSales = $projects->where('phase', 'selesai')->sum('nilai_proyek');
        $potentialSales = $projects->whereIn('phase', ['administrasi', 'teknis'])->sum('nilai_proyek');

        $leadsAwal = $leadsAwalQuery->count();
        $prospekAktif = $prospekAktifQuery->count();
        $closingWon = $closingWonQuery->count();
        $closingLost = $closingLostQuery->count();

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
