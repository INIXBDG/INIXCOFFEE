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
            $table->decimal('transportasi', 15)->nullable();
            $table->decimal('penginapan', 15)->nullable();
            $table->decimal('fresh_money', 15)->nullable();
            $table->decimal('entertaint', 15)->nullable();
            $table->decimal('souvenir', 15)->nullable();
            $table->decimal('harga_penawaran', 15)->nullable();
            $table->date('tgl_pa')->nullable();
            $table->string('tipe_pembayaran')->nullable();
            $table->integer('pajak')->nullable();
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
