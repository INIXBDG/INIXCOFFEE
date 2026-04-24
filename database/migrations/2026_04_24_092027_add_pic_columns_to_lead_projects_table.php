<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('lead_projects', function (Blueprint $table) {
            $table->string('nama_pic')->after('perusahaan_id');
            $table->string('kontak_pic')->after('nama_pic');
        });
    }

    public function down()
    {
        Schema::table('lead_projects', function (Blueprint $table) {
            $table->dropColumn(['nama_pic', 'kontak_pic']);
        });
    }
};