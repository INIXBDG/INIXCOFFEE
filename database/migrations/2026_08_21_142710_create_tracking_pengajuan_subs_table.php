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
        if (!Schema::hasTable('tracking_pengajuan_subs')) {
            Schema::create('tracking_pengajuan_subs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('id_pengajuan_subs');
                $table->string('tracking');
                $table->dateTime('tanggal');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_pengajuan_subs');
    }
};
