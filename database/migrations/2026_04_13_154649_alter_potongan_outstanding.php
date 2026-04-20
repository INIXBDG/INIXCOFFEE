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
        Schema::table('outstandings', function (Blueprint $table) {
            $table->string('jumlah_potongan')->nullable();
            $table->string('jenis_potongan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outstandings', function (Blueprint $table) {
            //
        });
    }
};
