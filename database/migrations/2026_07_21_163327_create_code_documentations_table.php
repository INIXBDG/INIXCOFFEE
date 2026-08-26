<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('code_documentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feature_documentation_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('flow_program')->nullable(); // {type: 'text'|'diagram'|'mermaid', content: '...'}
            $table->json('code_blocks')->nullable(); // [{description: '', code: '', language: ''}]
            $table->json('relations')->nullable(); // ['User', 'Role', 'Permission']
            $table->json('change_logs')->nullable(); // [{version: '1.0', date: '', programmer: '', summary: '', details: ''}]
            $table->json('future_development')->nullable(); // ['Add export excel', 'Add API']
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('code_documentations');
    }
};