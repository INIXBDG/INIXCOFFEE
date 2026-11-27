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
        if (Schema::hasTable('rincian_kegiatans')) {
            Schema::table('rincian_kegiatans', function (Blueprint $table) {
                if (!Schema::hasColumn('rincian_kegiatans', 'id_karyawan')) {
                    $table->string('id_karyawan');
                }
                if (!Schema::hasColumn('rincian_kegiatans', 'tipe')) {
                    $table->string('tipe');
                }
                if (!Schema::hasColumn('rincian_kegiatans', 'status')) {
                    $table->string('status');
                }
                if (!Schema::hasColumn('rincian_kegiatans', 'tanggal')) {
                    $table->date('tanggal');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rincian_kegiatans', function (Blueprint $table) {
            //
        });
    }
};
