<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('tracking_tagihan_perusahaans', function (Blueprint $table) {
            $table->boolean('is_urgent')->default(false)->after('status');
            $table->string('urgent_order')->nullable()->after('is_urgent');

            $table->string('nominal')->nullable()->change();
        });

        Schema::table('tagihan_perusahaans', function (Blueprint $table) {
            $table->string('nominal')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('tracking_tagihan_perusahaans', function (Blueprint $table) {
            $table->dropColumn(['is_urgent', 'urgent_order']);
        });
    }
};