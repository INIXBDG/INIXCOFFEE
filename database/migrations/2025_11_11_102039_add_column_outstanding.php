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
                if (!Schema::hasColumn('outstandings', 'path_faktur_pajak')) {
                    $table->string('path_faktur_pajak')->nullable();
                }
                if (!Schema::hasColumn('outstandings', 'path_dokumen_tambahan')) {
                    $table->string('path_dokumen_tambahan')->nullable();
                }
            });
        }
    }

    /**
     * 
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
