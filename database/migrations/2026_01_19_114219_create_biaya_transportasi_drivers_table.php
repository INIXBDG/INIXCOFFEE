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
        Schema::create('biaya_transportasi_drivers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('id_pengajuan_barang')->nullable();
            $table->integer('id_karyawan');
            $table->integer('id_pickup_driver');
            $table->string('tipe');
            $table->integer('harga');
            $table->text('bukti');
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biaya_transportasi_drivers');
    }
};
