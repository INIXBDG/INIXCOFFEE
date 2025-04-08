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
        Schema::table('nilaifeedbacks', function (Blueprint $table) {
            $table->text('U1')->change();
            $table->text('U2')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nilaifeedbacks', function (Blueprint $table) {
            //
        });
    }
};
