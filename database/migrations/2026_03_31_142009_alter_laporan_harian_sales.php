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
        if (Schema::hasTable('laporan_harian_sales')) {
            Schema::table('laporan_harian_sales', function (Blueprint $table) {
                if (Schema::hasColumn('laporan_harian_sales', 'tanggal_pelaksanaan')) {
                    $table->date('tanggal_pelaksanaan')->nullable()->change();
                }
                if (Schema::hasColumn('laporan_harian_sales', 'waktu_pelaksanaan')) {
                    $table->time('waktu_pelaksanaan')->nullable()->change();
                }
                if (Schema::hasColumn('laporan_harian_sales', 'jumlah_peserta_hadir')) {
                    $table->integer('jumlah_peserta_hadir')->nullable()->change();
                }
                if (Schema::hasColumn('laporan_harian_sales', 'jenis_meeting')) {
                    $table->string('jenis_meeting')->nullable()->change();
                }
                if (!Schema::hasColumn('laporan_harian_sales', 'is_draft')) {
                    $table->boolean('is_draft')->default(false)->after('catatan');
                }
            });
        }
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
