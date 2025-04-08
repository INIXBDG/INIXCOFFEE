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
        Schema::table('registexams', function (Blueprint $table) {
            $table->string('status_pembayaran')->nullable();//0 sudah 1 belum
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registexams', function (Blueprint $table) {
            //
        });
    }
};
