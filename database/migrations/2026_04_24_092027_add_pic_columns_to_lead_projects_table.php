<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('lead_projects')) {
            Schema::table('lead_projects', function (Blueprint $table) {
                if (!Schema::hasColumn('lead_projects', 'nama_pic')) {
                    $table->string('nama_pic')->after('perusahaan_id');
                }
                if (!Schema::hasColumn('lead_projects', 'kontak_pic')) {
                    $table->string('kontak_pic')->after('nama_pic');
                }
            });
        }
    }

    public function down()
    {
        Schema::table('lead_projects', function (Blueprint $table) {
            $table->dropColumn(['nama_pic', 'kontak_pic']);
        });
    }
};