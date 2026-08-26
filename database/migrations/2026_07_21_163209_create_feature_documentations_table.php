<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_documentations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->enum('status', ['draft', 'development', 'production', 'deprecated'])->default('draft');
            $table->text('short_description');
            $table->text('purpose');
            $table->text('background')->nullable();
            $table->text('problem_solved')->nullable();
            $table->text('how_it_works')->nullable();
            $table->text('user_access')->nullable();
            $table->string('manual_file_path')->nullable();
            $table->string('manual_file_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_documentations');
    }
};