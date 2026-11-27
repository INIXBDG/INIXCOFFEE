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
        if (Schema::hasTable('administrasi_karyawans') && !Schema::hasColumn('administrasi_karyawans', 'id_karyawan')) {
            Schema::table('administrasi_karyawans', function (Blueprint $table) {
                $table->foreignId('id_karyawan')->nullable()->after('nama_administrasi')->constrained('karyawans')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('administrasi_karyawans', function (Blueprint $table) {
            //
        });
    }
};
