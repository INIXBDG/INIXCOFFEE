<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('rekomendasi_lanjutans', 'keterangan')) {
            Schema::table('rekomendasi_lanjutans', function (Blueprint $table) {
                $table->text('keterangan')->nullable()->after('id_materi');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('rekomendasi_lanjutans', 'keterangan')) {
            Schema::table('rekomendasi_lanjutans', function (Blueprint $table) {
                $table->dropColumn('keterangan');
            });
        }
    }
};
