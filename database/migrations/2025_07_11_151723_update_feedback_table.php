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
            $table->string('M')->nullable();
            $table->text('TextM')->nullable();
            $table->string('P')->nullable();
            $table->text('TextP')->nullable();
            $table->string('F')->nullable();
            $table->text('TextF')->nullable();
            $table->string('I')->nullable();
            $table->text('TextI')->nullable();
            $table->string('IB')->nullable();
            $table->text('TextIB')->nullable();
            $table->string('IAS')->nullable();
            $table->text('TextIAS')->nullable();
            $table->string('S')->nullable();
            $table->text('TextS')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
