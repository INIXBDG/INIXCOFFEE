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
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->string('kategori')->nullable(); // Kendaraan / Fasilitas Kantor
            $table->string('divisi')->nullable();
            $table->string('teknisi')->nullable();
            $table->string('nama_barang')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('no_voucher')->nullable();
            $table->decimal('biaya', 15, 2)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('status')->default('On Progress');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};
