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
        Schema::create('absensi_no_records', function (Blueprint $table) {
            $table->id();
            $table->integer('id_karyawan');
            $table->string('jenis_PK');
            $table->string('kendala')->nullable();
            $table->integer('id_absen');
            $table->string('bukti_gambar')->nullable();
            $table->text('kronologi')->nullable();
            $table->string('alasan_approval')->nullable();
            $table->integer('approval');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_no_records');
    }
};
