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
        Schema::table('certificates', function (Blueprint $table) {
            $table->string('nomor_sertifikat')->nullable()->change();
            $table->string('tanggal_pelatihan')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->string('nomor_sertifikat')->nullable(false)->change();
            $table->string('tanggal_pelatihan')->nullable(false)->change();
        });
    }
};
