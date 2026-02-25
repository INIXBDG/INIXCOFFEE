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
        Schema::create('checklist_keperluans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_rkm');

            // Kolom boolean untuk 5 item checklist (0 = belum dicentang, 1 = sudah dicentang)
            $table->boolean('materi')->default(false);
            $table->boolean('kelas')->default(false);
            $table->boolean('cb')->default(false);
            $table->boolean('maksi')->default(false);
            $table->boolean('keperluan_kelas')->default(false);

            $table->timestamps();

            // Mendefinisikan Foreign Key
            $table->foreign('id_rkm')->references('id')->on('r_k_m_s')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_keperluans');
    }
};
