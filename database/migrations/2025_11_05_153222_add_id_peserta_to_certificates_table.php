<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->foreignId('id_peserta')
                ->after('rkm_id')
                ->constrained('pesertas')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['id_peserta']);
            $table->dropColumn('id_peserta');
        });
    }
};
