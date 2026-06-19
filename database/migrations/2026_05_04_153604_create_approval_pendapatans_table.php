<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_pendapatans', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_rkm');

            $table->string('no_faktur')->nullable();
            $table->string('no_invoice')->nullable();

            $table->bigInteger('harga_net')->nullable();
            $table->integer('pax')->nullable();

            $table->bigInteger('diskon')->nullable();
            $table->bigInteger('total_diskon')->nullable();
            $table->bigInteger('total_pa')->nullable();
            $table->bigInteger('total_cashback')->nullable();
            $table->bigInteger('total_uang_saku')->nullable();
            $table->bigInteger('total_akomodasi')->nullable();

            $table->string('jenis_transport')->nullable();
            $table->bigInteger('biaya_transport')->nullable();

            $table->bigInteger('oleh_oleh')->nullable();
            $table->bigInteger('total_penjualan_sales')->nullable();

            $table->bigInteger('PPN')->nullable();
            $table->bigInteger('PPH')->nullable();

            $table->enum('status', ['valid', 'belum tervalidasi'])->nullable();

            $table->unsignedBigInteger('materi')->nullable();
            $table->unsignedBigInteger('perusahaan')->nullable();

            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();

            $table->bigInteger('jumlah_pembayaran')->nullable();
            $table->date('tanggal_pembayaran')->nullable();
            $table->bigInteger('biaya_admin')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_pendapatans');
    }
};