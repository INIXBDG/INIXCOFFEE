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
        if (Schema::hasTable('surat_perjalanans')) {
            Schema::table('surat_perjalanans', function (Blueprint $table) {
                if (!Schema::hasColumn('surat_perjalanans', 'jenis_dinas')) {
                    $table->string('jenis_dinas')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_perjalanan', function (Blueprint $table) {
            //
        });
    }
};
