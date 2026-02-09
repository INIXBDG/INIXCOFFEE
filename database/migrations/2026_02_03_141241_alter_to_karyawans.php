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
        Schema::table('karyawans', function (Blueprint $table) {
            $table->text('alamat_lengkap')->nullable()->after('nama_lengkap');
            $table->string('gender', 20)->nullable()->after('alamat_lengkap');
            $table->string('tempat_lahir')->nullable()->after('gender');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->string('religion', 50)->nullable()->after('tanggal_lahir');
            $table->string('provinsi')->nullable()->after('religion');
            $table->string('kota')->nullable()->after('provinsi');
        });
    }
};
