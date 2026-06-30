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
        Schema::create('org_structures', function (Blueprint $table) {
            $table->id();
            $table->string('jabatan');
            $table->string('divisi')->nullable();
            $table->json('karyawan_ids')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('org_structures')->nullOnDelete();
            $table->integer('sort_order')->default(0);
            $table->json('additional_parents')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('org_structures');
    }
};
