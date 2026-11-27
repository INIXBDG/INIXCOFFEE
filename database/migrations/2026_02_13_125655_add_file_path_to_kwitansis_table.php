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
        if (Schema::hasTable('kwitansis')) {
            Schema::table('kwitansis', function (Blueprint $table) {
                if (!Schema::hasColumn('kwitansis', 'file_path')) {
                    $table->string('file_path')->nullable()->after('dicetak_oleh');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kwitansis', function (Blueprint $table) {
            //
        });
    }
};
