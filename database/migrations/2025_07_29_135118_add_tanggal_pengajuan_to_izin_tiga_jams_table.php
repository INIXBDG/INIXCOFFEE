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
    Schema::table('izin_tiga_jams', function (Blueprint $table) {
        $table->date('tanggal_pengajuan')->after('alasan')->nullable();
    });
}

public function down()
{
    Schema::table('izin_tiga_jams', function (Blueprint $table) {
        $table->dropColumn('tanggal_pengajuan');
    });
}

};
