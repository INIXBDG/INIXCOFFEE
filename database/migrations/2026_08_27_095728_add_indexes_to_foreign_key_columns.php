<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom yang perlu diindex, dikelompokkan per tabel.
     * peserta_moduls.perusahaan_id sengaja tidak diikutkan karena bertipe TEXT
     * (butuh key length kalau mau diindex, dan memang sudah ditetapkan TEXT).
     */
    private function columns(): array
    {
        return [
            'activity_instrukturs' => ['user_id'],
            'activity_logs' => ['user_id'],
            'daily_activities' => ['user_id'],
            'detail_pickup_drivers' => ['pickup_driver_id'],
            'kondisi_kendaraans' => ['user_id'],
            'lead_projects' => ['perusahaan_id', 'sales_id'],
            'monitorings' => ['project_id'],
            'pelamar_riwayats' => ['user_id'],
            'pic_penagihan_invoices' => ['perusahaan_id'],
            'project_activities' => ['project_task_id', 'user_id'],
            'project_administrations' => ['pm_id', 'assignee_id', 'project_handover_id'],
            'project_tasks' => ['assignee_id'],
            'projects' => ['lead_id', 'client_id'],
            'registry_features' => ['pengerja_id', 'ticket_id'],
            'report_generations' => ['source_id'],
            'survey_kepuasans' => ['ticket_id'],
            'tasks' => ['user_id'],
            'tracking_koordinasi_office_boys' => ['koordinasi_id'],
            'tracking_pickup_drivers' => ['pickup_driver_id'],
            'users' => ['karyawan_id'],
        ];
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::selectOne(
            'SELECT COUNT(1) as total FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $indexName]
        );

        return $result->total > 0;
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->columns() as $table => $cols) {
            foreach ($cols as $column) {
                $indexName = "{$table}_{$column}_index";

                if ($this->indexExists($table, $indexName)) {
                    continue;
                }

                Schema::table($table, function (Blueprint $blueprint) use ($column) {
                    $blueprint->index($column);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->columns() as $table => $cols) {
            foreach ($cols as $column) {
                $indexName = "{$table}_{$column}_index";

                if (! $this->indexExists($table, $indexName)) {
                    continue;
                }

                Schema::table($table, function (Blueprint $blueprint) use ($column) {
                    $blueprint->dropIndex([$column]);
                });
            }
        }
    }
};