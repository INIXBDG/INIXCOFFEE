<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('detail_target_k_p_i_s', function (Blueprint $table) {
            $table->integer('id_data_target')->after('divisi');
            $table->dropColumn(['jangka_target', 'tipe_target', 'nilai_target']);
        });

        Schema::table('target_k_p_i_s', function (Blueprint $table) {
            $table->integer('id_data_target')->after('id_pembuat');
            $table->dropColumn('asistant_route');
        });
    }

    public function down(): void
    {
        Schema::table('detail_target_k_p_i_s', function (Blueprint $table) {
            $table->dropColumn('id_data_target');
            $table->string('jangka_target');
            $table->string('tipe_target');
            $table->bigInteger('nilai_target');
        });

        Schema::table('target_k_p_i_s', function (Blueprint $table) {
            $table->dropColumn('id_data_target');
            $table->string('asistant_route');
        });
    }
};
