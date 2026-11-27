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
            if (!Schema::hasColumn('absensi_no_records', 'waktu_masuk')) {
                $table->time('waktu_masuk')->nullable();
            }
            if (!Schema::hasColumn('absensi_no_records', 'waktu_pulang')) {
                $table->time('waktu_pulang')->nullable();
            }
        });
    }
}

public function down()
{
    Schema::table('absensi_no_records', function (Blueprint $table) {
        $table->dropColumn('waktu_masuk');
        $table->dropColumn('waktu_pulang');
    });
}

};
