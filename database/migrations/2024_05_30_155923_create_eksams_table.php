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
        Schema::create('eksams', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_pengajuan');
            $table->string('invoice');
            $table->string('id_rkm');
            $table->string('materi');
            $table->string('perusahaan');
            $table->string('mata_uang');
            $table->decimal('harga', 10, 2)->nullable();
            $table->decimal('kurs', 15, 2)->nullable();
            $table->decimal('biaya_admin', 10, 2)->nullable();
            $table->decimal('kurs_dollar', 15, 2)->nullable();
            $table->decimal('harga_rupiah', 15, 2)->nullable();
            $table->integer('pax');
            $table->integer('total_pax');
            $table->decimal('total', 15, 2);
            $table->string('kode_exam');
            $table->string('keterangan')->nullable();
            $table->string('status')->nullable();
            $table->string('kode_karyawan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eksams');
    }
};
