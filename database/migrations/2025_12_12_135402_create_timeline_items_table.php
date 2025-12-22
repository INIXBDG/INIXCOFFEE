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
        Schema::create('timeline_items', function (Blueprint $table) {
            $table->id();
            // Relasi Opsional ke Mapping (agar tahu item ini milik event bulan apa)
            $table->unsignedBigInteger('year_mapping_id')->nullable()->index();

            $table->date('item_date')->index();         // Tanggal Item
            $table->text('content');                    // Isi Teks (misal: "Posting IG")
            $table->string('color')->nullable();        // Opsional: Warna Label
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_items');
    }
};
