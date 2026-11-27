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
        if (Schema::hasTable('target_k_p_i_s')) {
            $cols = array_values(array_filter(
                ['jabatan', 'divisi', 'jangka_target', 'detail_jangka', 'tipe_target', 'nilai_target'],
                fn (string $col) => Schema::hasColumn('target_k_p_i_s', $col)
            ));

            if (!empty($cols)) {
                Schema::table('target_k_p_i_s', function (Blueprint $table) use ($cols) {
                    $table->dropColumn($cols);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('target_k_p_i_s')) {
            Schema::table('target_k_p_i_s', function (Blueprint $table) {
                if (!Schema::hasColumn('target_k_p_i_s', 'jabatan')) {
                    $table->string('jabatan')->nullable();
                }
                if (!Schema::hasColumn('target_k_p_i_s', 'divisi')) {
                    $table->string('divisi')->nullable();
                }
                if (!Schema::hasColumn('target_k_p_i_s', 'jangka_target')) {
                    $table->text('jangka_target')->nullable();
                }
                if (!Schema::hasColumn('target_k_p_i_s', 'detail_jangka')) {
                    $table->text('detail_jangka')->nullable();
                }
                if (!Schema::hasColumn('target_k_p_i_s', 'tipe_target')) {
                    $table->string('tipe_target')->nullable();
                }
                if (!Schema::hasColumn('target_k_p_i_s', 'nilai_target')) {
                    $table->bigInteger('nilai_target')->nullable();
                }
            });
        }
    }
};
