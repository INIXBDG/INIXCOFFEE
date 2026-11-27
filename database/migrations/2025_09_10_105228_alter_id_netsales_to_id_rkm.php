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
        if (
            Schema::hasTable('tracking_net_sales')
            && Schema::hasColumn('tracking_net_sales', 'id_netSales')
            && !Schema::hasColumn('tracking_net_sales', 'id_rkm')
        ) {
            Schema::table('tracking_net_sales', function (Blueprint $table) {
                $table->renameColumn('id_netSales', 'id_rkm');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (
            Schema::hasTable('tracking_net_sales')
            && Schema::hasColumn('tracking_net_sales', 'id_rkm')
            && !Schema::hasColumn('tracking_net_sales', 'id_netSales')
        ) {
            Schema::table('tracking_net_sales', function (Blueprint $table) {
                $table->renameColumn('id_rkm', 'id_netSales');
            });
        }
    }
};
