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
        Schema::create('tracking_laporan_insidens', function (Blueprint $table) {
            $table->id();
            $table->string('id_laporanInsiden');
            $table->integer('responder')->nullable();
            $table->text('solusi')->nullable();
            $table->date('tanggal_response');
            $table->time('waktu_response');
            $table->enum('status', ['Baru','Dalam Penanganan','Selesai','Tidak Ditangani'])->default('Baru');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_laporan_insidens');
    }
};
