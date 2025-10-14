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
        Schema::table('target_activities', function (Blueprint $table) {
            $table->integer('DB');
            $table->integer('PA');
            $table->integer('PI');
            $table->integer('Telemarketing');
            $table->integer('FormM');
            $table->integer('FormK');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('target_activities', function (Blueprint $table) {
            //
        });
    }
};
