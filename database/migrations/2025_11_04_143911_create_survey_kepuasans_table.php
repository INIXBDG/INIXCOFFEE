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
        Schema::create('survey_kepuasans', function (Blueprint $table) {
            $table->id();
            $table->string('id_user');
            $table->integer('q1');
            $table->string('q2');
            $table->text('q3')->nullable();
            $table->integer('q4');
            $table->text('q5')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_kepuasans');
    }
};
