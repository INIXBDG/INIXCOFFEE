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
        Schema::table('kontrol_tugas', function (Blueprint $table) {
            $table->integer('urutan')->nullable()->default(0);
        });

        Schema::table('kategori_daftar_tugas', function (Blueprint $table) {
            $table->integer('urutan')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kontrol_tugas', function (Blueprint $table) {
            $table->dropColumn('urutan');
        });

        Schema::table('kategori_daftar_tugas', function (Blueprint $table) {
            $table->dropColumn('urutan');
        });
    }
};
