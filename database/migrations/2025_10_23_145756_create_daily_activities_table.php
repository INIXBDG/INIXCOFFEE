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
        Schema::create('daily_activities', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->bigInteger('id_task');
            $table->string('activity');
            $table->enum('status', ['On Progres', 'On Progres Dilanjutkan Besok','Gagal','Selesai'])->default('On Progres');
            $table->text('description')->nullable();
            $table->string('doc')->nullable();
            $table->date('start_date')->default(now());
            $table->timestamp('on_progress_at')->nullable();
            $table->timestamp('on_progress_next_day_at')->nullable();
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
        Schema::dropIfExists('daily_activities');
    }
};
