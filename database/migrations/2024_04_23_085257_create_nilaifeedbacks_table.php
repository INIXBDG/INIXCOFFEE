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
        Schema::create('nilaifeedbacks', function (Blueprint $table) {
            $table->id();
            $table->string('id_regist');
            $table->string('id_rkm');
            $table->string('email');
            $table->string('M1');
            $table->string('M2');
            $table->string('M3');
            $table->string('M4');
            $table->string('P1');
            $table->string('P2');
            $table->string('P3');
            $table->string('P4');
            $table->string('P5');
            $table->string('P6');
            $table->string('P7');
            $table->string('F1');
            $table->string('F2');
            $table->string('F3');
            $table->string('F4');
            $table->string('F5');
            $table->string('I1');
            $table->string('I2');
            $table->string('I3');
            $table->string('I4');
            $table->string('I5');
            $table->string('I6');
            $table->string('I7');
            $table->string('I8');
            $table->string('I1b')->nullable();
            $table->string('I2b')->nullable();
            $table->string('I3b')->nullable();
            $table->string('I4b')->nullable();
            $table->string('I5b')->nullable();
            $table->string('I6b')->nullable();
            $table->string('I7b')->nullable();
            $table->string('I8b')->nullable();
            $table->string('I1as')->nullable();
            $table->string('I2as')->nullable();
            $table->string('I3as')->nullable();
            $table->string('I4as')->nullable();
            $table->string('I5as')->nullable();
            $table->string('I6as')->nullable();
            $table->string('I7as')->nullable();
            $table->string('I8as')->nullable();
            $table->string('U1');
            $table->string('U2');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilaifeedbacks');
    }
};
