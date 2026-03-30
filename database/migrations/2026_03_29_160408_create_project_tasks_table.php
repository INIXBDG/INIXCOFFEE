<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('assignee_id')->nullable();
            $table->foreign('assignee_id')->references('kode_karyawan')->on('karyawans')->onDelete('set null');
            $table->enum('status', [
                'backlog', 'to_do', 'in_progress', 'testing', 'validate', 'deploy', 'evaluasi'
            ])->default('backlog');
            $table->string('task_file')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('project_tasks');
    }
};