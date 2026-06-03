<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('template_placeholders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('report_templates')->onDelete('cascade');
            $table->string('placeholder_key');
            $table->string('placeholder_label');
            $table->string('field_type');
            $table->string('source_column')->nullable();
            $table->boolean('is_manual')->default(false);
            $table->json('options')->nullable();
            $table->string('default_value')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('template_placeholders');
    }
};