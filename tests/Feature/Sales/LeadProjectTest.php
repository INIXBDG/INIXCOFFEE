<?php

namespace Tests\Feature\Sales;

use App\Http\Controllers\LeadProjectController;
use App\Http\Controllers\ReportSalesProjectController;
use App\Models\LeadProject;
use App\Models\Perusahaan;
use App\Models\Project;
use App\Models\karyawan;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class LeadProjectTest extends TestCase
{
    public function test_project_sales_feature_exposes_controllers_routes_and_view(): void
    {
        $this->assertTrue(class_exists(LeadProjectController::class));
        $this->assertTrue(class_exists(ReportSalesProjectController::class));
        $this->assertTrue(method_exists(LeadProjectController::class, 'index'));
        $this->assertTrue(method_exists(LeadProjectController::class, 'store'));
        $this->assertTrue(method_exists(LeadProjectController::class, 'updateStatus'));
        $this->assertTrue(method_exists(ReportSalesProjectController::class, 'getRecapData'));
        $this->assertTrue(Route::has('leads.index'));
        $this->assertTrue(Route::has('leads.store'));
        $this->assertTrue(Route::has('leads.update_status'));
        $this->assertTrue(Route::has('reports.sales'));
        $this->assertTrue(Route::has('reports.sales.data'));
        $this->assertTrue(View::exists('lead_project.index'));
    }

    public function test_lead_project_accepts_sales_pipeline_attributes(): void
    {
        $lead = new LeadProject([
            'nama_lead' => 'Implementasi CRM',
            'perusahaan_id' => 10,
            'nama_pic' => 'Budi',
            'kontak_pic' => '08123456789',
            'estimasi_nilai' => 75000000,
            'tahun_periode' => 2026,
            'status' => 'surat_penawaran',
            'sales_id' => 'SLS001',
        ]);

        $this->assertSame('Implementasi CRM', $lead->nama_lead);
        $this->assertSame('SLS001', $lead->sales_id);
        $this->assertSame('surat_penawaran', $lead->status);
        $this->assertSame(75000000, $lead->estimasi_nilai);
    }

    public function test_lead_project_defines_client_sales_and_project_relations(): void
    {
        $lead = new LeadProject;

        $this->assertInstanceOf(BelongsTo::class, $lead->client());
        $this->assertSame(Perusahaan::class, $lead->client()->getRelated()::class);
        $this->assertSame('perusahaan_id', $lead->client()->getForeignKeyName());

        $this->assertInstanceOf(BelongsTo::class, $lead->sales());
        $this->assertSame(karyawan::class, $lead->sales()->getRelated()::class);
        $this->assertSame('sales_id', $lead->sales()->getForeignKeyName());
        $this->assertSame('kode_karyawan', $lead->sales()->getOwnerKeyName());

        $this->assertInstanceOf(HasOne::class, $lead->project());
        $this->assertSame(Project::class, $lead->project()->getRelated()::class);
        $this->assertSame('lead_id', $lead->project()->getForeignKeyName());
    }

    public function test_lead_project_controller_exposes_destroy_method(): void
    {
        $this->assertTrue(method_exists(LeadProjectController::class, 'destroy'));
    }

    public function test_project_accepts_dates_and_defines_lead_and_client_relations(): void
    {
        $project = new Project([
            'lead_id' => 1,
            'name' => 'Implementasi CRM',
            'client_id' => 10,
            'phase' => 'won',
            'tanggal_awal' => '2026-09-03',
            'tanggal_akhir' => '2026-10-03',
            'nilai_proyek' => 75000000,
        ]);

        $this->assertSame('2026-09-03', $project->tanggal_awal->format('Y-m-d'));
        $this->assertSame('2026-10-03', $project->tanggal_akhir->format('Y-m-d'));
        $this->assertInstanceOf(BelongsTo::class, $project->lead());
        $this->assertSame(LeadProject::class, $project->lead()->getRelated()::class);
        $this->assertInstanceOf(BelongsTo::class, $project->client());
        $this->assertSame(Perusahaan::class, $project->client()->getRelated()::class);
    }
}