<?php

namespace App\Http\Controllers;

use App\Models\LeadProject;
use App\Models\Project;
use App\Models\ProjectAdministration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class LeadProjectController extends Controller
{
    public function index()
    {
        return view('lead_project.index');
    }

    public function getLeads(): JsonResponse
    {
        $data = LeadProject::with(['client', 'sales'])->get();
        return response()->json(['data' => $data], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nama_lead' => 'required|string|max:255',
            'perusahaan_id' => 'required|exists:perusahaans,id',
            'nama_pic' => 'required|string|max:255',     // Validasi PIC
            'kontak_pic' => 'required|string|max:255',
            'estimasi_nilai' => 'required|numeric',
        ]);

        $currentUserKaryawanId = auth()->user()->karyawan->kode_karyawan ?? null;

        LeadProject::create([
            'nama_lead' => $request->nama_lead,
            'perusahaan_id' => $request->perusahaan_id,
            'nama_pic' => $request->nama_pic,            // Penyimpanan Data
            'kontak_pic' => $request->kontak_pic,
            'estimasi_nilai' => $request->estimasi_nilai,
            'status' => 'penawaran_awal',
            'sales_id' => $currentUserKaryawanId,
        ]);

        return response()->json(['success' => true, 'message' => 'Lead berhasil dibuat.'], 201);
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:penawaran_awal,permintaan_klien,meeting_klien,dokumen_penawaran,mengirim_proposal_teknis,surat_penawaran,lost,won'
        ]);

        DB::beginTransaction();
        try {
            $lead = LeadProject::findOrFail($id);
            $newStatus = $request->status;

            $lead->update(['status' => $newStatus]);

            // Logika Penanganan Status 'Lost' (Soft Delete)
            if ($newStatus === 'lost') {
                if ($lead->project) {
                    $lead->project->delete(); // Soft delete proyek yang sudah terbentuk
                }
                $lead->delete(); // Soft delete lead itu sendiri
            } 
            // Logika Transisi ke Administrasi
            else if (in_array($newStatus, ['dokumen_penawaran', 'mengirim_proposal_teknis', 'surat_penawaran', 'won'])) {
                if (!$lead->project) {
                    // Membuat Proyek dan masuk ke fase Administrasi
                    $project = Project::create([
                        'lead_id' => $lead->id,
                        'name' => $lead->nama_lead,
                        'client_id' => $lead->perusahaan_id,
                        'nilai_proyek' => $lead->estimasi_nilai,
                        'phase' => 'administrasi',
                    ]);

                    ProjectAdministration::create([
                        'project_id' => $project->id,
                        'current_stage' => 'kak',
                        'pm_id' => 'AD', // Default PM
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Tahapan Lead berhasil diperbarui.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui tahapan: ' . $e->getMessage()], 500);
        }
    }

    public function updateLead(Request $request, $id): JsonResponse
    {
        $request->validate([
            'nama_lead' => 'required|string|max:255',
            'nama_pic' => 'required|string|max:255',
            'kontak_pic' => 'required|string|max:255',
            'estimasi_nilai' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            $lead = LeadProject::findOrFail($id);

            // 1. Memperbarui Data Lead
            $lead->update([
                'nama_lead' => $request->nama_lead,
                'nama_pic' => $request->nama_pic,
                'kontak_pic' => $request->kontak_pic,
                'estimasi_nilai' => $request->estimasi_nilai,
            ]);

            // 2. Mensinkronkan Data dengan Project (Jika sudah terkonversi/masuk fase administrasi)
            if ($lead->project) {
                $lead->project->update([
                    'name' => $request->nama_lead,
                    'nilai_proyek' => $request->estimasi_nilai,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data Lead berhasil diperbarui.'], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui data Lead: ' . $e->getMessage()], 500);
        }
    }
}