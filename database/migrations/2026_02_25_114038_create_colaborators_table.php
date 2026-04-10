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
        Schema::create('colaborators', function (Blueprint $table) {
            $table->id();
            $table->string('nama_partner');
            $table->string('title');
            $table->string('type');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status');
            $table->text('desc');
            $table->string('document_mou')->nullable();
            $table->string('quartal')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colaborators');
    }
};
