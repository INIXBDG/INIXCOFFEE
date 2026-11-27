<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('analysis_year_descriptions')) {
            Schema::table('analysis_year_descriptions', function (Blueprint $table) {
                if (!Schema::hasColumn('analysis_year_descriptions', 'note')) {
                    $table->text('note')->nullable()->after('description');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('analysis_year_descriptions', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
};
