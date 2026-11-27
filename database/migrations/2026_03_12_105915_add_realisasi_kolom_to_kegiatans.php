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
        if (Schema::hasTable('kegiatans')) {
            Schema::table('kegiatans', function (Blueprint $table) {
                if (!Schema::hasColumn('kegiatans', 'realisasi')) {
                    $table->string('realisasi')->nullable();
                }
            });
        }
    }
};
