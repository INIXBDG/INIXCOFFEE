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
        Schema::create('laporan_harian_sales', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_pelaksanaan');
            $table->time('waktu_pelaksanaan');
            $table->string('tempat_or_media')->nullable();
            $table->integer('jumlah_peserta_hadir');
            $table->integer('jumlah_peserta_tidak_hadir')->nullable();
            $table->string('alasan_peserta_tidak_hadir')->nullable();
            $table->string('jenis_meeting');
            $table->foreignId('pic')->nullable()->constrained('karyawans')->nullOnDelete();
            $table->foreignId('notulis')->nullable()->constrained('karyawans')->nullOnDelete();
            $table->string('topic');
            $table->string('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_harian_sales');
    }
};
