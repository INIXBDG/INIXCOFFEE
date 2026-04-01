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
        Schema::table('laporan_harian_sales', function (Blueprint $table) {
            $table->date('tanggal_pelaksanaan')->nullable()->change();
            $table->time('waktu_pelaksanaan')->nullable()->change();
            $table->integer('jumlah_peserta_hadir')->nullable()->change();
            $table->string('jenis_meeting')->nullable()->change();
            $table->boolean('is_draft')->default(false)->after('catatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_harian_sales', function (Blueprint $table) {
            $table->date('tanggal_pelaksanaan')->change();
            $table->time('waktu_pelaksanaan')->change();
            $table->integer('jumlah_peserta_hadir')->change();
            $table->string('jenis_meeting')->change();
        });
    }
};
