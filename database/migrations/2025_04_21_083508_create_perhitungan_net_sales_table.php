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
        Schema::create('perhitungan_net_sales', function (Blueprint $table) {
            $table->id();
            $table->integer('id_rkm');
            $table->integer('id_tracking')->nullable();
            $table->decimal('transportasi', 15)->nullable();
            $table->text('jenis_transportasi')->nullable();
            $table->decimal('akomodasi_peserta', 15)->nullable();
            $table->decimal('akomodasi_tim', 15)->nullable();
            $table->text('keterangan_akomodasi_tim')->nullable();
            $table->decimal('fresh_money', 15)->nullable();
            $table->decimal('entertaint', 15)->nullable();
            $table->text('keterangan_entertaint')->nullable();
            $table->decimal('souvenir', 15)->nullable();
            $table->decimal('cashback', 15)->nullable();
            $table->decimal('sewa_laptop', 15)->nullable();
            $table->date('tgl_pa')->nullable();
            $table->string('tipe_pembayaran')->nullable();
            $table->text('deskripsi_tambahan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perhitungan_net_sales');
    }
};
