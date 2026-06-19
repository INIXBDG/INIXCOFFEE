<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('job_profiles');

        Schema::create('job_profiles', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('karyawan_id')
                  ->constrained('karyawans')
                  ->onDelete('cascade');
            
            $table->json('qualifications')->nullable()->comment('Array of qualification strings');
            $table->json('descriptions')->nullable()->comment('Array of job description strings');
            $table->json('compensation_benefit')->nullable()->comment('Array of compensation & benefit strings');

            $table->timestamps();
            
            $table->unique('karyawan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_profiles');
    }
};