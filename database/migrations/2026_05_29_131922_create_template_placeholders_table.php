<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('report_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('report_templates');
            $table->string('report_title');
            $table->string('source_type');
            $table->unsignedBigInteger('source_id');
            $table->json('manual_inputs')->nullable();
            $table->json('generated_data')->nullable();
            $table->string('output_file_path');
            $table->string('status')->default('pending');
            $table->foreignId('generated_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('report_generations');
    }
};