<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHistoryStatusToPerusahaansTable extends Migration
{
    public function up()
    {
        Schema::table('perusahaans', function (Blueprint $table) {
            $table->json('history_status')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('perusahaans', function (Blueprint $table) {
            $table->dropColumn('history_status');
        });
    }
}
