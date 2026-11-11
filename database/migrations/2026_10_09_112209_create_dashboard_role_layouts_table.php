<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dijaga hasTable karena controller juga bisa membuat tabel ini otomatis.
        if (!Schema::hasTable('dashboard_role_layouts')) {
            Schema::create('dashboard_role_layouts', function (Blueprint $table) {
                $table->id();
                $table->string('role_name')->unique();
                $table->longText('section_order')->nullable(); // JSON
                $table->longText('card_order')->nullable();    // JSON
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_role_layouts');
    }
};