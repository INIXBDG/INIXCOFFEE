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
        Schema::create('tracking_tagihan_perusahaans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tagihan_perusahaan')->nullable()->constrained('tagihan_perusahaans')->nullOnDelete();
            $table->decimal( 'nominal', 15, 2)->nullable();
            $table->string('tracking')->nullable();
            $table->enum('status', ['proses', 'pending', 'selesai', 'telat'])->default('proses');
            $table->date('tanggal_selesai')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_tagihan_perusahaans');
    }
};
