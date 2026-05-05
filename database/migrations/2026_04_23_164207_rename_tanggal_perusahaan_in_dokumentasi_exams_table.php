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
        Schema::table('dokumentasi_exams', function (Blueprint $table) {
            $table->renameColumn('tanggal_perusahaan', 'tanggal_pelaksanaan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dokumentasi_exams', function (Blueprint $table) {
            $table->renameColumn('tanggal_pelaksanaan', 'tanggal_perusahaan');
        });
    }
};
