<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('job_desks');

        Schema::create('job_desks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_org');
            
            // === JOB PROFILE ===
            $table->text('fungsi_utama')->nullable();
            $table->text('tujuan_jabatan')->nullable();
            $table->text('kualifikasi_pendidikan')->nullable();
            $table->text('pengalaman_kerja')->nullable();
            $table->json('kompetensi')->nullable(); 
            $table->text('karakteristik_pribadi')->nullable();

            // === JOB DESK (Parent-Child) ===
            $table->json('tugas_tanggung_jawab')->nullable();  
            $table->json('wewenang')->nullable();               

            // === SOP (Parent-Child) ===
            $table->json('sop')->nullable();              

            $table->timestamps();

            $table->foreign('id_org')
                  ->references('id')
                  ->on('org_structures')
                  ->onDelete('cascade');

            $table->unique('id_org');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_desks');
    }
};