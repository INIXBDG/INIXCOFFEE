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
        if (Schema::hasTable('listexams')) {
            Schema::table('listexams', function (Blueprint $table) {
                if (!Schema::hasColumn('listexams', 'mata_uang')) {
                    $table->string('mata_uang')->nullable();
                }
                if (!Schema::hasColumn('listexams', 'harga')) {
                    $table->decimal('harga', 10, 2)->nullable();
                }
                if (!Schema::hasColumn('listexams', 'kurs')) {
                    $table->decimal('kurs', 15, 2)->nullable();
                }
                if (!Schema::hasColumn('listexams', 'biaya_admin')) {
                    $table->decimal('biaya_admin', 10, 2)->nullable();
                }
                if (!Schema::hasColumn('listexams', 'kurs_dollar')) {
                    $table->decimal('kurs_dollar', 15, 2)->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listexams', function (Blueprint $table) {
            //
        });
    }
};
