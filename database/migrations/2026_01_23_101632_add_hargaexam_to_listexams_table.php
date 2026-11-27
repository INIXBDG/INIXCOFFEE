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
        if (Schema::hasTable('listexams')) {
            Schema::table('listexams', function (Blueprint $table) {
                if (!Schema::hasColumn('listexams', 'harga_exam')) {
                    $table->decimal('harga_exam', 15, 2)->nullable()->after('vendor');
                }
                if (!Schema::hasColumn('listexams', 'valid_until')) {
                    $table->date('valid_until')->nullable();
                }
                if (!Schema::hasColumn('listexams', 'estimasi_durasi_booking')) {
                    $table->string('estimasi_durasi_booking')->nullable();
                }
                if (!Schema::hasColumn('listexams', 'note')) {
                    $table->string('note')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('listexams', function (Blueprint $table) {
            //
        });
    }
};
