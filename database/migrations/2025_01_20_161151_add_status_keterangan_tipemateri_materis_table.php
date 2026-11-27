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
                if (!Schema::hasColumn('materis', 'status')) {
                    $table->enum('status', ['Aktif', 'Retired', 'Nonaktif'])->nullable();
                }
                if (!Schema::hasColumn('materis', 'tipe_materi')) {
                    $table->string('tipe_materi')->nullable();
                }
                if (!Schema::hasColumn('materis', 'keterangan')) {
                    $table->text('keterangan')->nullable();
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
            //
        });
    }
};
