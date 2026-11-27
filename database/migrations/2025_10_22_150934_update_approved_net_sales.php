<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (
            Schema::hasTable('approved_net_sales')
            && Schema::hasColumn('approved_net_sales', 'id_netSales')
            && !Schema::hasColumn('approved_net_sales', 'id_rkm')
        ) {
            Schema::table('approved_net_sales', function (Blueprint $table) {
                $table->renameColumn('id_netSales', 'id_rkm');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (
            Schema::hasTable('approved_net_sales')
            && Schema::hasColumn('approved_net_sales', 'id_rkm')
            && !Schema::hasColumn('approved_net_sales', 'id_netSales')
        ) {
            Schema::table('approved_net_sales', function (Blueprint $table) {
                $table->renameColumn('id_rkm', 'id_netSales');
            });
        }
    }
};
