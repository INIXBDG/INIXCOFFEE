<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('perhitungan_net_sales', function (Blueprint $table) {
            $table->unsignedBigInteger('id_tracking')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('perhitungan_net_sales', function (Blueprint $table) {
            $table->unsignedBigInteger('id_tracking')->nullable(false)->change();
        });
    }
};
