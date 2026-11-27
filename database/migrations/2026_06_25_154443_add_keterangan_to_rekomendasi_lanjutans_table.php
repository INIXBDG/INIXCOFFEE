<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('rekomendasi_lanjutans')) {
            Schema::table('rekomendasi_lanjutans', function (Blueprint $table) {
                if (!Schema::hasColumn('rekomendasi_lanjutans', 'keterangan')) {
                    $table->text('keterangan')->nullable()->after('id_materi');
                }
            });
        }
    }

    public function down()
    {
        Schema::table('rekomendasi_lanjutans', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};
