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
        if (Schema::hasTable('kontrol_tugas')) {
            Schema::table('kontrol_tugas', function (Blueprint $table) {
                if (!Schema::hasColumn('kontrol_tugas', 'urutan')) {
                    $table->integer('urutan')->nullable()->default(0);
                }
            });
        }

        if (Schema::hasTable('kategori_daftar_tugas')) {
            Schema::table('kategori_daftar_tugas', function (Blueprint $table) {
                if (!Schema::hasColumn('kategori_daftar_tugas', 'urutan')) {
                    $table->integer('urutan')->nullable()->default(0);
                }
            });
        }
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
