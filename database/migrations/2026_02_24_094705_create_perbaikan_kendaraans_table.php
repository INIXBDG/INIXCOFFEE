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
        Schema::create('perbaikan_kendaraans', function (Blueprint $table) {
            $table->id();
            $table->integer('id_kondisi_kendaraan')->nullable();
            $table->string('kendaraan');
            $table->integer('id_user');
            $table->enum('type_condition', ['Perawatan', 'Kecelakaan']);
            $table->enum('type_vehicle_condition', ['Kerusakan Ringan', 'Kerusakan Sedang', 'Kerusakan Berat', 'Kerusakan Total']);
            $table->enum('type_repair', ['Penggantian', 'Peningkatan', 'Perbaikan', 'Perbaikan Total']);
            $table->text('deskripsi_kondisi')->nullable();
            $table->date('tanggal_kejadian')->nullable();
            $table->time('waktu_kejadian')->nullable();
            $table->string('lokasi')->nullable();
            $table->integer('estimasi')->nullable();
            $table->string('bukti')->nullable();
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perbaikan_kendaraans');
    }
};
