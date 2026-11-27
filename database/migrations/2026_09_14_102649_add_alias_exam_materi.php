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
        if (Schema::hasTable('materis')) {
            Schema::table('materis', function (Blueprint $table) {
                if (!Schema::hasColumn('materis', 'alias_exam')) {
                    $table->string('alias_exam')->nullable()->after('kategori_exam');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materis', function (Blueprint $table) {
            $table->dropColumn('alias_exam');
        });
    }
};
