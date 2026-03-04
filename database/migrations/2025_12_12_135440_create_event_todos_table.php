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
        Schema::create('event_todos', function (Blueprint $table) {
            $table->id();
            // Relasi Manual
            $table->unsignedBigInteger('year_mapping_id')->index();
            $table->unsignedBigInteger('todo_id')->index();

            $table->boolean('is_checked')->default(false); // Status Selesai
            $table->string('pic')->nullable();             // PJ
            $table->text('notes')->nullable();             // Catatan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_todos');
    }
};
