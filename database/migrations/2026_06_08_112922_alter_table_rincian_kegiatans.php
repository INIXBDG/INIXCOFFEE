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
        Schema::table('rincian_kegiatans', function (Blueprint $table) {
            $table->string('id_karyawan');
            $table->string('tipe');
            $table->string('status');
            $table->date('tanggal');
        });
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
