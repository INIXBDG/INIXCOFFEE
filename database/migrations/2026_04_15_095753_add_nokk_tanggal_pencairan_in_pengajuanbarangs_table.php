<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengajuanbarangs', function (Blueprint $table) {
            $table->string('no_kk')->nullable();
            $table->date('tanggal_pencairan')->nullable();
            $table->date('tanggal_terima_finance')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengajuanbarangs', function (Blueprint $table) {
            //
        });
    }
};
