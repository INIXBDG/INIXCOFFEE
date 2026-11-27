<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('absensi_no_records')) {
            Schema::table('absensi_no_records', function (Blueprint $table) {
                if (!Schema::hasColumn('absensi_no_records', 'approved_by')) {
                    $table->string('approved_by')->nullable()->after('approval');
                }
            });
        }
    }

    public function down()
    {
        Schema::table('absensi_no_records', function (Blueprint $table) {
            $table->dropColumn('approved_by');
        });
    }
};