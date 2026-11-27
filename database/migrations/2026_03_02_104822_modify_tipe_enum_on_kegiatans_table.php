<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kegiatans') && Schema::hasColumn('kegiatans', 'tipe')) {
            DB::statement("
                ALTER TABLE kegiatans 
                MODIFY tipe ENUM('kegiatan','pembelian','rekrutmen')
            ");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kegiatans') && Schema::hasColumn('kegiatans', 'tipe')) {
            DB::statement("
                ALTER TABLE kegiatans 
                MODIFY tipe ENUM('kegiatan','pembelian')
            ");
        }
    }
};