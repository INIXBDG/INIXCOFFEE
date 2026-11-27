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
        if (Schema::hasTable('eksams')) {
            Schema::table('eksams', function (Blueprint $table) {
                if (Schema::hasColumn('eksams', 'kode_exam')) {
                    $table->string('kode_exam')->nullable()->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('eksams', function (Blueprint $table) {
            $table->string('kode_exam')->nullable(false)->change();
        });
    }
};
