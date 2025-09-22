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
        Schema::table('r_k_m_s', function (Blueprint $table) {
            $table->enum('makanan', ['0', '1', '2',])
                  ->default('0')
                  ->after('exam');
        });
    }

    public function down(): void
    {
        Schema::table('r_k_m_s', function (Blueprint $table) {
            $table->dropColumn('makanan');
        });
    }
};
