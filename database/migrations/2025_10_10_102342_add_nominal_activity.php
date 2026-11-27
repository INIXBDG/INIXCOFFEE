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
                if (!Schema::hasColumn('aktivitas', 'pax')) {
                    $table->integer('pax')->nullable();
                }
                if (!Schema::hasColumn('aktivitas', 'harga')) {
                    $table->decimal('harga', 15, 2)->nullable();
                }
                if (!Schema::hasColumn('aktivitas', 'total')) {
                    $table->decimal('total', 15, 2)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aktivitas', function (Blueprint $table) {
            //
        });
    }
};
