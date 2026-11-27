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
        if (Schema::hasTable('perhitungan_net_sales')) {
            Schema::table('perhitungan_net_sales', function (Blueprint $table) {
                if (!Schema::hasColumn('perhitungan_net_sales', 'deleted_at')) {
                    $table->softDeletes();
                }
                if (!Schema::hasColumn('perhitungan_net_sales', 'deleted_by')) {
                    $table->string('deleted_by')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
