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
        if (Schema::hasTable('outstandings')) {
            Schema::table('outstandings', function (Blueprint $table) {
                if (!Schema::hasColumn('outstandings', 'jumlah_potongan')) {
                    $table->string('jumlah_potongan')->nullable();
                }
                if (!Schema::hasColumn('outstandings', 'jenis_potongan')) {
                    $table->string('jenis_potongan')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('outstandings', function (Blueprint $table) {
            //
        });
    }
};
