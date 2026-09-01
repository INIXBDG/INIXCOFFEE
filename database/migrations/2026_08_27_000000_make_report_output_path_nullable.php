<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('report_generations', function (Blueprint $table) {
            $table->string('output_file_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('report_generations', function (Blueprint $table) {
            $table->string('output_file_path')->nullable(false)->change();
        });
    }
};