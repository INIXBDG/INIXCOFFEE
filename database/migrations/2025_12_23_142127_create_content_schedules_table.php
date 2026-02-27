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
        Schema::create('content_schedules', function (Blueprint $table) {
            $table->id();
            $table->enum('content_form', ['Reels', 'Youtube', 'Feed', 'Story']);
            $table->date('upload_date')->nullable();
            $table->string('talents');
            $table->text('description')->nullable();
            $table->longText('proof_script')->nullable();
            $table->string('proof_image_path')->nullable();
            $table->boolean('is_tiktok')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_schedules');
    }
};
