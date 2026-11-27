<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('detail_target_k_p_i_s')) {
            if (!Schema::hasColumn('detail_target_k_p_i_s', 'id_data_target')) {
                Schema::table('detail_target_k_p_i_s', function (Blueprint $table) {
                    $col = $table->integer('id_data_target');
                    if (Schema::hasColumn('detail_target_k_p_i_s', 'divisi')) {
                        $col->after('divisi');
                    }
                });
            }

            $dropDetail = array_values(array_filter(
                ['jangka_target', 'tipe_target', 'nilai_target'],
                fn (string $col) => Schema::hasColumn('detail_target_k_p_i_s', $col)
            ));
            if (!empty($dropDetail)) {
                Schema::table('detail_target_k_p_i_s', function (Blueprint $table) use ($dropDetail) {
                    $table->dropColumn($dropDetail);
                });
            }
        }

        if (Schema::hasTable('target_k_p_i_s')) {
            if (!Schema::hasColumn('target_k_p_i_s', 'id_data_target')) {
                Schema::table('target_k_p_i_s', function (Blueprint $table) {
                    $col = $table->integer('id_data_target');
                    if (Schema::hasColumn('target_k_p_i_s', 'id_pembuat')) {
                        $col->after('id_pembuat');
                    }
                });
            }

            if (Schema::hasColumn('target_k_p_i_s', 'asistant_route')) {
                Schema::table('target_k_p_i_s', function (Blueprint $table) {
                    $table->dropColumn('asistant_route');
                });
            }
        }
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
