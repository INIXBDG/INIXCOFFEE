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
        Schema::table('checklist_keperluans', function (Blueprint $table) {
            $table->date('tanggal_keperluan')->nullable()->after('id_rkm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('checklist_keperluans', function (Blueprint $table) {
            //
        });
    }
};
