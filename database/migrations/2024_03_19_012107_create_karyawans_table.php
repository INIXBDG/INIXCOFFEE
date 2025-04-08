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
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();
            $table->string('foto')->nullable();
            $table->string('nip')->nullable();
            $table->string('nama_lengkap');
            // $table->string('email')->nullable();
            $table->string('divisi')->nullable();
            $table->string('jabatan')->nullable();
            $table->string('rekening_maybank')->nullable();
            $table->string('rekening_bca')->nullable();
            // $table->string('notelp')->nullable();
            $table->enum('status_aktif', ['0', '1']);
            $table->date('awal_probation')->nullable();
            $table->date('akhir_probation')->nullable();
            $table->date('awal_kontrak')->nullable();
            $table->date('akhir_kontrak')->nullable();
            $table->date('awal_tetap')->nullable();
            $table->date('akhir_tetap')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('kode_karyawan')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
