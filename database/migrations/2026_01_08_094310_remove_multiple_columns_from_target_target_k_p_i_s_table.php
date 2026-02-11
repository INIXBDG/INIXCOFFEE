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
        Schema::table('target_k_p_i_s', function (Blueprint $table) {
            $table->dropColumn(['jabatan', 'divisi', 'jangka_target', 'detail_jangka', 'tipe_target', 'nilai_target']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('target_k_p_i_s', function (Blueprint $table) {
            $table->string('jabatan')->nullable();
            $table->string('divisi')->nullable();
            $table->text('jangka_target')->nullable();
            $table->text('detail_jangka')->nullable();
            $table->string('tipe_target')->nullable();
            $table->bigInteger('nilai_target')->nullable();
        });
    }
};
