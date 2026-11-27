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
    if (Schema::hasTable('absensi_no_records')) {
        Schema::table('absensi_no_records', function (Blueprint $table) {
            if (Schema::hasColumn('absensi_no_records', 'id_absen')) {
                $table->unsignedBigInteger('id_absen')->nullable()->change();
            }
        });
    }
}

public function down()
{
    Schema::table('absensi_no_records', function (Blueprint $table) {
        $table->unsignedBigInteger('id_absen')->nullable(false)->change();
    });
}

};
