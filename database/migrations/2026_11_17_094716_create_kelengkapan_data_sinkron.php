<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kelengkapan_data_logs')) {
            Schema::create('kelengkapan_data_logs', function (Blueprint $table) {
                $table->id();
                $table->string('sumber', 20);
                $table->unsignedBigInteger('id_rkm')->index();
                $table->text('perubahan');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('kelengkapan_data_syncs')) {
            Schema::create('kelengkapan_data_syncs', function (Blueprint $table) {
                $table->id();
                $table->date('periode_mulai');
                $table->date('periode_selesai');
                $table->unsignedBigInteger('sales_by')->nullable();
                $table->timestamp('sales_at')->nullable();
                $table->string('sales_hash', 40)->nullable();
                $table->unsignedBigInteger('finance_by')->nullable();
                $table->timestamp('finance_at')->nullable();
                $table->string('finance_hash', 40)->nullable();
                $table->timestamps();
                $table->unique(['periode_mulai', 'periode_selesai']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kelengkapan_data_syncs');
        Schema::dropIfExists('kelengkapan_data_logs');
    }
};