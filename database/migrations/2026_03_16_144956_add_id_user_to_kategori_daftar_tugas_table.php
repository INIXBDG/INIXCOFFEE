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
        if (Schema::hasTable('kategori_daftar_tugas')) {
            Schema::table('kategori_daftar_tugas', function (Blueprint $table) {
                if (!Schema::hasColumn('kategori_daftar_tugas', 'id_user')) {
                    $table->integer('id_user')->nullable()->after('Jabatan_Pembuat');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_daftar_tugas', function (Blueprint $table) {
            $table->dropColumn('id_user');
        });
    }
};
