<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('rekomendasi_lanjutans', function (Blueprint $table) {
            $table->text('keterangan')->nullable()->after('id_materi');
        });
    }

    public function down()
    {
        Schema::table('rekomendasi_lanjutans', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};
