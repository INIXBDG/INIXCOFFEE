<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pelamars', function (Blueprint $table) {
            $table->string('nik_ktp', 20)->nullable()->after('no_telepon');
            $table->string('tempat_lahir', 100)->nullable()->after('nik_ktp');
            $table->string('status_pernikahan', 30)->nullable()->after('jenis_kelamin');
            $table->string('agama', 30)->nullable()->after('status_pernikahan');
            $table->text('alamat')->nullable()->after('domisili');
            $table->string('linkedin_url')->nullable()->after('alamat');
            $table->string('notice_period', 50)->nullable()->after('gaji_diharapkan');
            $table->string('ktp_path')->nullable()->after('foto_path');
            $table->string('ijazah_path')->nullable()->after('ktp_path');
        });
    }

    public function down(): void
    {
        Schema::table('pelamars', function (Blueprint $table) {
            $table->dropColumn([
                'nik_ktp',
                'tempat_lahir',
                'status_pernikahan',
                'agama',
                'alamat',
                'linkedin_url',
                'notice_period',
                'ktp_path',
                'ijazah_path',
            ]);
        });
    }
};