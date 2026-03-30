<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('project_administrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('kak_file')->nullable();
            $table->string('budget_file')->nullable();
            $table->string('legal_file')->nullable();
            $table->string('client_doc_file')->nullable();
            $table->string('payment_doc_file')->nullable();
            $table->string('pm_id')->nullable();
            $table->foreign('pm_id')->references('kode_karyawan')->on('karyawans')->onDelete('set null');
            $table->enum('current_stage', [
                'kak', 'penganggaran', 'legal', 'dokumen_klien', 'pembayaran', 'assign_tim'
            ])->default('kak');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('project_administrations');
    }
};