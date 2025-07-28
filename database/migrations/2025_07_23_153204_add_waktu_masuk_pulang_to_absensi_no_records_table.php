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
    Schema::table('absensi_no_records', function (Blueprint $table) {
        $table->time('waktu_masuk')->nullable();
        $table->time('waktu_pulang')->nullable();
    });
}

public function down()
{
    Schema::table('absensi_no_records', function (Blueprint $table) {
        $table->dropColumn('waktu_masuk');
        $table->dropColumn('waktu_pulang');
    });
}

};
