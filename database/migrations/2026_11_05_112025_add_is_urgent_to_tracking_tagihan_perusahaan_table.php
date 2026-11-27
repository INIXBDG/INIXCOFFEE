<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('tracking_tagihan_perusahaans')) {
            Schema::table('tracking_tagihan_perusahaans', function (Blueprint $table) {
                if (!Schema::hasColumn('tracking_tagihan_perusahaans', 'is_urgent')) {
                    $table->boolean('is_urgent')->default(false)->after('status');
                }
                if (!Schema::hasColumn('tracking_tagihan_perusahaans', 'urgent_order')) {
                    $table->string('urgent_order')->nullable()->after('is_urgent');
                }

                if (Schema::hasColumn('tracking_tagihan_perusahaans', 'nominal')) {
                    $table->string('nominal')->nullable()->change();
                }
            });
        }

        if (Schema::hasTable('tagihan_perusahaans')) {
            Schema::table('tagihan_perusahaans', function (Blueprint $table) {
                if (Schema::hasColumn('tagihan_perusahaans', 'nominal')) {
                    $table->string('nominal')->nullable()->change();
                }
            });
        }
    }

    public function down()
    {
        Schema::table('tracking_tagihan_perusahaans', function (Blueprint $table) {
            $table->dropColumn(['is_urgent', 'urgent_order']);
        });
    }
};