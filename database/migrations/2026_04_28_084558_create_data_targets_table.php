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
        Schema::create('data_targets', function (Blueprint $table) {
            $table->id();
            $table->string('asistant_route');
            $table->string('jangka_target');
            $table->string('tipe_target');
            $table->bigInteger('nilai_target');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_targets');
    }
};
