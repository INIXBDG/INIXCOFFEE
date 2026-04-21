<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Mengubah ENUM status pada tabel leads
        DB::statement("ALTER TABLE lead_projects MODIFY COLUMN status ENUM('penawaran_awal', 'permintaan_klien', 'meeting_klien', 'dokumen_penawaran', 'mengirim_proposal_teknis', 'surat_penawaran', 'lost', 'won') DEFAULT 'penawaran_awal'");

        // 2. Menambahkan Soft Deletes
        Schema::table('lead_projects', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::table('lead_projects', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};