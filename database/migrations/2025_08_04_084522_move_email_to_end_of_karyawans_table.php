<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up()
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('karyawans', function (Blueprint $table) {
            $table->string('email')->nullable(); // Tanpa `after`, otomatis di akhir
        });
    }

    public function down()
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn('email');
        });

        Schema::table('karyawans', function (Blueprint $table) {
            $table->string('email')->nullable()->after('nama_lengkap');
        });
    }
};
