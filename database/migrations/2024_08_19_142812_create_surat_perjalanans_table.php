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
        Schema::create('surat_perjalanans', function (Blueprint $table) {
            $table->id();
            $table->string('id_karyawan');
            $table->string('tipe');
            $table->string('tujuan');
            $table->dateTime('tanggal_berangkat');
            $table->dateTime('tanggal_pulang');
            $table->string('durasi');
            $table->string('alasan')->nullable();
            $table->decimal('ratemakan', 10, 2)->nullable();
            $table->decimal('ratespj', 10, 2)->nullable();
            $table->decimal('ratetaksi', 10, 2)->nullable();
            $table->decimal('total')->nullable();
            $table->enum('approval_manager', ['0', '1', '2']);//0 = proses, 1 = disetujui, 2 = ditolak 
            $table->enum('approval_hrd', ['0', '1', '2']);//0 = proses, 1 = disetujui, 2 = ditolak 
            $table->enum('approval_direksi', ['0', '1', '2']);//0 = proses, 1 = disetujui, 2 = ditolak 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surat_perjalanans');
    }
};
