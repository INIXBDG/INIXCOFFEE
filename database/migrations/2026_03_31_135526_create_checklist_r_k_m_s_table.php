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
        Schema::create('checklist_r_k_m_s', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_rkm');
            $table->boolean('registrasi_form');
            $table->boolean('surat_kontrak');
            $table->boolean('PA');
            $table->boolean('PO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_r_k_m_s');
    }
};
