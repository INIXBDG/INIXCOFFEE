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
        Schema::table('tracking_net_sales', function (Blueprint $table) {
            $table->renameColumn('id_netSales', 'id_rkm');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tracking_net_sales', function (Blueprint $table) {
            $table->renameColumn('id_rkm', 'id_netSales');
        });
    }
};
