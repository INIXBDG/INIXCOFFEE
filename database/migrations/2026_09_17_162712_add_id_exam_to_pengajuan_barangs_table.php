<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pengajuanbarangs', function (Blueprint $table) {
            $table->unsignedBigInteger('id_exam')->nullable()->after('id_kegiatan');
            $table->foreign('id_exam')->references('id')->on('eksams')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('pengajuanbarangs', function (Blueprint $table) {
            $table->dropForeign(['id_exam']);
            $table->dropColumn('id_exam');
        });
    }
};
