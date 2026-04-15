<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('bast_file')->nullable(); // Dokumen Berita Acara Serah Terima
            $table->string('final_report_file')->nullable(); // Dokumen Laporan Akhir Proyek
            $table->date('handover_date')->nullable();
            $table->enum('status', ['pending', 'disetujui_klien', 'selesai'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_handovers');
    }
};