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
        if (Schema::hasTable('hasilexams')) {
            Schema::table('hasilexams', function (Blueprint $table) {
                if (!Schema::hasColumn('hasilexams', 'keterangan')) {
                    $table->string('keterangan')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hasilexams', function (Blueprint $table) {
            //
        });
    }
};
