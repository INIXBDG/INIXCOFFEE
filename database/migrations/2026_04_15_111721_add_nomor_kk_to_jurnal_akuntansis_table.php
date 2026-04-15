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
        Schema::table('jurnal_akuntansis', function (Blueprint $table) {
            $table->string('nomor_kk', 20)->nullable()->after('id');
            $table->string('no_akun')->nullable()->after('keterangan');
        });
    }

    public function down()
    {
        Schema::table('jurnal_akuntansis', function (Blueprint $table) {
            
        });
    }
};
