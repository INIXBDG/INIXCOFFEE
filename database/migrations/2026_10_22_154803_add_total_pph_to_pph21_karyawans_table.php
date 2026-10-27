<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pph21_karyawans', function (Blueprint $table) {
            $table->decimal('total_pph', 15, 2)->default(0)->after('anak');
        });
    }

    public function down(): void
    {
        Schema::table('pph21_karyawans', function (Blueprint $table) {
            $table->dropColumn('total_pph');
        });
    }
};