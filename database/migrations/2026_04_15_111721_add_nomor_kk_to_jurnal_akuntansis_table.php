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
        if (Schema::hasTable('jurnal_akuntansis')) {
            Schema::table('jurnal_akuntansis', function (Blueprint $table) {
                if (!Schema::hasColumn('jurnal_akuntansis', 'nomor_kk')) {
                    $table->string('nomor_kk', 20)->nullable()->after('id');
                }
                if (!Schema::hasColumn('jurnal_akuntansis', 'no_akun')) {
                    $table->string('no_akun')->nullable()->after('keterangan');
                }
            });
        }
    }

    public function down()
    {
        Schema::table('jurnal_akuntansis', function (Blueprint $table) {
            
        });
    }
};
