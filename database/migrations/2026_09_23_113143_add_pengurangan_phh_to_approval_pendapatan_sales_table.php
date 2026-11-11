<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('approval_pendapatan_sales', function (Blueprint $table) {
            $table->decimal('pengurangan_phh', 15, 2)->default(0)->after('biaya_lain_lain');
        });
    }

    public function down()
    {
        Schema::table('approval_pendapatan_sales', function (Blueprint $table) {
            $table->dropColumn('pengurangan_phh');
        });
    }
};
