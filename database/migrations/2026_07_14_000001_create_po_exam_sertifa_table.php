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
        if (!Schema::hasTable('po_exam_sertifa')) {
            Schema::create('po_exam_sertifa', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_materi')->nullable();
                $table->unsignedBigInteger('id_rkm')->nullable();
                $table->date('tanggal_exam')->nullable();
                $table->unsignedBigInteger('id_perusahaan')->nullable();
                $table->integer('pax')->nullable();
                $table->decimal('harga', 15, 2)->default(0);
                $table->timestamps();

                $table->foreign('id_materi')->references('id')->on('materis')->nullOnDelete();
                $table->foreign('id_rkm')->references('id')->on('r_k_m_s')->nullOnDelete();
                $table->foreign('id_perusahaan')->references('id')->on('perusahaans')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('po_exam_sertifa');
    }
};
