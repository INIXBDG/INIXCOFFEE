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
        Schema::table('materis', function (Blueprint $table) {
            $table->string('alias')->nullable()->after('nama_materi');
            $table->string('kode_alias')->nullable()->after('alias');
            $table->string('kategori_exam')->nullable()->after('kategori_materi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materis', function (Blueprint $table) {
            $table->dropColumn('alias');
            $table->dropColumn('kode_alias');
            $table->dropColumn('kategori_exam');
        });
    }
};
