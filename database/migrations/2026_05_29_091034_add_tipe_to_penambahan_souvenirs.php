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
        Schema::table('penambahan_souvenirs', function (Blueprint $table) {
            $table->unsignedBigInteger('id_rkm')->nullable()->change();
            $table->string('tipe')->after('id_souvenir')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penambahan_souvenirs', function (Blueprint $table) {
            $table->unsignedBigInteger('id_rkm')->nullable(false)->change();
            $table->dropColumn('tipe');
        });
    }
};
