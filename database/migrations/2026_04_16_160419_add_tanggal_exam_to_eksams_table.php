<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('eksams')) {
            Schema::table('eksams', function (Blueprint $table) {
                if (!Schema::hasColumn('eksams', 'tanggal_mulai')) {
                    $table->date('tanggal_mulai')->nullable()->after('tanggal_pengajuan');
                }
                if (!Schema::hasColumn('eksams', 'tanggal_selesai')) {
                    $table->date('tanggal_selesai')->nullable()->after('tanggal_mulai');
                }
            });
        }
    }

    public function down()
    {
        Schema::table('eksams', function (Blueprint $table) {
            $table->dropColumn(['tanggal_mulai', 'tanggal_selesai']);
        });
    }
};
