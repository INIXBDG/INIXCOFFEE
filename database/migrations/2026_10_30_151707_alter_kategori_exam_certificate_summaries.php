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
        if (Schema::hasTable('certificate_summaries')) {
            Schema::table('certificate_summaries', function (Blueprint $table) {
                if (!Schema::hasColumn('certificate_summaries', 'kategori')) {
                    $table->string('kategori')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('certificate_summaries', function (Blueprint $table) {
            $table->dropColumn('kategori');
        });
    }
};
