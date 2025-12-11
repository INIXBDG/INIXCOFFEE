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
        Schema::create('activity_instrukturs', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('activity')->nullable();
            $table->text('desc')->nullable();
            $table->string('doc')->nullable();
            $table->date('activity_date')->nullable();
            $table->enum('status', ['On Progres','Gagal','Selesai'])->default('On Progres');
            $table->timestamp('on_progress_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_instrukturs');
    }
};
