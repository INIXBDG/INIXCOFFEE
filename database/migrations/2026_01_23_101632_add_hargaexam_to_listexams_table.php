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
        Schema::table('listexams', function (Blueprint $table) {
            $table->decimal('harga_exam', 15, 2)->nullable()->after('vendor');
            $table->date('valid_until')->nullable();
            $table->string('estimasi_durasi_booking')->nullable();
            $table->string('note')->nullable();
        });
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
