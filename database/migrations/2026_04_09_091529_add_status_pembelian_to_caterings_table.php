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
        if (Schema::hasTable('caterings')) {
            Schema::table('caterings', function (Blueprint $table) {
                if (!Schema::hasColumn('caterings', 'status_pembelian')) {
                    $table->string('status_pembelian')->nullable();
                }
                if (!Schema::hasColumn('caterings', 'tanggal_pembelian')) {
                    $table->string('tanggal_pembelian')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('caterings', function (Blueprint $table) {
            //
        });
    }
};
