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
        Schema::create('pembelian_hrs', function (Blueprint $table) {
            $table->id();
            $table->string('no_kk')->nullable();
            $table->integer('id_karyawan');
            $table->string('status_pembelian');
            $table->string('kategori');
            $table->string('periode');
            $table->string('invoice')->nullable();
            $table->text('alasan_dibatalkan')->nullable();
            $table->foreignId('id_pengajuan')->nullable()->constrained('pengajuanbarangs')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian_hrs');
    }
};
