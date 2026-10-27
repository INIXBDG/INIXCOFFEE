<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->string('no_kk', 50)->nullable();
            $table->string('section', 50);                 
            $table->string('jenis_maintenance', 100);
            $table->date('tanggal');
            $table->unsignedTinyInteger('bulan');        
            $table->unsignedSmallInteger('tahun');      
            $table->decimal('biaya', 15, 2)->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->index(['tahun', 'bulan']);
            $table->index('section');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_records');
    }
};