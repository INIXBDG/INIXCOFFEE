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
        if (Schema::hasTable('aktivitas')) {
            Schema::table('aktivitas', function (Blueprint $table) {
                if (!Schema::hasColumn('aktivitas', 'foto_lokasi')) {
                    $table->string('foto_lokasi')->nullable();
                }
                if (!Schema::hasColumn('aktivitas', 'latitude')) {
                    $table->text('latitude')->nullable();
                }
                if (!Schema::hasColumn('aktivitas', 'longitude')) {
                    $table->text('longitude')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
