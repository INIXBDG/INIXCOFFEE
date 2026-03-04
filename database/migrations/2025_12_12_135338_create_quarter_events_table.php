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
        Schema::create('quarter_events', function (Blueprint $table) {
            $table->id();
            // Relasi ke Mapping (Tanpa Constraint Database)
            $table->unsignedBigInteger('year_mapping_id')->index();

            $table->string('title')->nullable();        // Judul Webinar
            $table->string('speaker_name')->nullable(); // Narasumber
            $table->text('description')->nullable();    // Keterangan
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quarter_events');
    }
};
