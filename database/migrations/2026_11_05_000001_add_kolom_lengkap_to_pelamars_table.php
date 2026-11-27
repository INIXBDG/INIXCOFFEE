<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pelamars')) {
            Schema::table('pelamars', function (Blueprint $table) {
                if (!Schema::hasColumn('pelamars', 'nik_ktp')) {
                    $table->string('nik_ktp', 20)->nullable()->after('no_telepon');
                }
                if (!Schema::hasColumn('pelamars', 'tempat_lahir')) {
                    $table->string('tempat_lahir', 100)->nullable()->after('nik_ktp');
                }
                if (!Schema::hasColumn('pelamars', 'status_pernikahan')) {
                    $table->string('status_pernikahan', 30)->nullable()->after('jenis_kelamin');
                }
                if (!Schema::hasColumn('pelamars', 'agama')) {
                    $table->string('agama', 30)->nullable()->after('status_pernikahan');
                }
                if (!Schema::hasColumn('pelamars', 'alamat')) {
                    $table->text('alamat')->nullable()->after('domisili');
                }
                if (!Schema::hasColumn('pelamars', 'linkedin_url')) {
                    $table->string('linkedin_url')->nullable()->after('alamat');
                }
                if (!Schema::hasColumn('pelamars', 'notice_period')) {
                    $table->string('notice_period', 50)->nullable()->after('gaji_diharapkan');
                }
                if (!Schema::hasColumn('pelamars', 'ktp_path')) {
                    $table->string('ktp_path')->nullable()->after('foto_path');
                }
                if (!Schema::hasColumn('pelamars', 'ijazah_path')) {
                    $table->string('ijazah_path')->nullable()->after('ktp_path');
                }
            });
        }
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