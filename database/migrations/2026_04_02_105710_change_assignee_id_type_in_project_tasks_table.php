<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            // Mengubah tipe kolom menjadi string agar dapat menyimpan kode_karyawan (misal: 'MA')
            $table->string('assignee_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('project_tasks', function (Blueprint $table) {
            // Mengembalikan ke tipe integer (asumsi tipe aslinya integer/bigint)
            $table->bigInteger('assignee_id')->nullable()->change();
        });
    }
};