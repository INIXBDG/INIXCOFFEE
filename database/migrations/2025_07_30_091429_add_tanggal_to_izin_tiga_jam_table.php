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
        Schema::table('izin_tiga_jams', function (Blueprint $table) {
            $table->date('tanggal')->after('id_karyawan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('izin_tiga_jams', function (Blueprint $table) {
            $table->dropColumn('tanggal');
        });
    }
};
