<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->text('content')->change(); // Ubah dari string ke text
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->string('content', 255)->change(); // Balik ke string
        });
    }
};
