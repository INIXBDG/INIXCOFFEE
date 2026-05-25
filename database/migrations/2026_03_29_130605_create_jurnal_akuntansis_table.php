<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration.
     */
    public function up(): void
    {
        Schema::create('jurnal_akuntansis', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_pengajuan_barang')->nullable();
            $table->date('tanggal_transaksi');
            $table->string('keterangan');
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('id_pengajuan_barang')->references('id')->on('pengajuanbarangs')->onDelete('set null');
        });
    }

    /**
     * Kembalikan migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_akuntansis');
    }
};