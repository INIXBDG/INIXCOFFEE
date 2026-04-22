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
        Schema::table('project_administrations', function (Blueprint $table) {
            $table->string('proposal_file')->nullable()->after('project_id');
            $table->string('surat_pekerjaan_dimulai_file')->nullable()->after('payment_doc_file');
            $table->string('project_handover_id')->nullable();
        });
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
