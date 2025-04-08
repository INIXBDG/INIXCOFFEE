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
        Schema::table('hasilexams', function (Blueprint $table) {
            $table->string('hasil', 50)->nullable()->change();
            $table->string('pdf', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nullable_hasilexam', function (Blueprint $table) {
            //
        });
    }
};
