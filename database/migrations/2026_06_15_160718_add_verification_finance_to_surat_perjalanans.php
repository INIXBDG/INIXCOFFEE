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
        Schema::table('surat_perjalanans', function (Blueprint $table) {
            $table->enum('approval_finance', ['0', '1', '2'])->default('0')->after('approval_direksi');
            $table->string('bukti_transfer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surat_perjalanans', function (Blueprint $table) {
            $table->dropColumn('approval_finance');
            $table->dropColumn('bukti_transfer');
        });
    }
};
