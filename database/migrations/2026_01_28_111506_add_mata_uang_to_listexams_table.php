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
        Schema::table('listexams', function (Blueprint $table) {
            $table->string('mata_uang')->nullable();
            $table->decimal('harga', 10, 2)->nullable();
            $table->decimal('kurs', 15, 2)->nullable();
            $table->decimal('biaya_admin', 10, 2)->nullable();
            $table->decimal('kurs_dollar', 15, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listexams', function (Blueprint $table) {
            //
        });
    }
};
