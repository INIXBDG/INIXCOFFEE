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
                if (!Schema::hasColumn('jurnal_akuntansis', 'id_perhitungan_net_sales')) {
                    $table->unsignedBigInteger('id_perhitungan_net_sales')->nullable()->after('id_pengajuan_barang');
                }
            });
        }
    }

    public function down()
    {
        Schema::table('jurnal_akuntansis', function (Blueprint $table) {
            $table->dropColumn('id_perhitungan_net_sales');
        });
    }
};
