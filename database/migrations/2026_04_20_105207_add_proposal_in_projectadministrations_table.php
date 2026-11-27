<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('project_administrations')) {
            Schema::table('project_administrations', function (Blueprint $table) {
                if (!Schema::hasColumn('project_administrations', 'proposal_file')) {
                    $table->string('proposal_file')->nullable()->after('project_id');
                }
                if (!Schema::hasColumn('project_administrations', 'surat_pekerjaan_dimulai_file')) {
                    $table->string('surat_pekerjaan_dimulai_file')->nullable()->after('payment_doc_file');
                }
                if (!Schema::hasColumn('project_administrations', 'project_handover_id')) {
                    $table->string('project_handover_id')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_administrations', function (Blueprint $table) {
            $table->dropColumn('legal_file');
        });
    }
};
