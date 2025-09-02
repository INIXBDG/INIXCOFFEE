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
        Schema::create('target_activities', function (Blueprint $table) {
            $table->id();
            $table->string('id_sales');
            $table->integer('Contact');
            $table->integer('Call');
            $table->integer('Visit');
            $table->integer('Email');
            $table->integer('Meet');
            $table->integer('Incharge');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_activities');
    }
};
