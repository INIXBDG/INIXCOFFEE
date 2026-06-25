<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('template_placeholders', function (Blueprint $table) {
            if (!Schema::hasColumn('template_placeholders', 'config')) {
                $table->json('config')->nullable()->after('options');
            }
        });

        Schema::table('report_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('report_templates', 'settings')) {
                $table->json('settings')->nullable()->after('edited_text');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('template_placeholders', function (Blueprint $table) {
            $table->dropColumn('config');
        });
    }
};
