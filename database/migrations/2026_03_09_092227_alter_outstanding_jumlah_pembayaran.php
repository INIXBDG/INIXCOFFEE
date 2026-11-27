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
                if (!Schema::hasColumn('outstandings', 'jumlah_pembayaran')) {
                    $table->decimal('jumlah_pembayaran', 15, 2)->nullable()->after('status_pembayaran');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
