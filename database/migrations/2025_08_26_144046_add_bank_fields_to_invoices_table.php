<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
<<<<<<< HEAD
     * Run the migrations.
=======
     * Jalankan migration untuk menambah kolom baru di tabel invoices.
>>>>>>> 1b16b0915417b914bb03ff291c20d9b95894c711
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
<<<<<<< HEAD
            $table->string('bank_name')->nullable()->after('amount');
            $table->string('account_number')->nullable()->after('bank_name');
=======
            // Tambah kolom bank_name setelah 'amount'
            if (!Schema::hasColumn('invoices', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('amount');
            }

            // Tambah kolom account_number setelah 'bank_name'
            if (!Schema::hasColumn('invoices', 'account_number')) {
                $table->string('account_number')->nullable()->after('bank_name');
            }
>>>>>>> 1b16b0915417b914bb03ff291c20d9b95894c711
        });
    }

    /**
<<<<<<< HEAD
     * Reverse the migrations.
=======
     * Rollback migration: hapus kolom baru dari tabel invoices.
>>>>>>> 1b16b0915417b914bb03ff291c20d9b95894c711
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
<<<<<<< HEAD
            $table->dropColumn(['bank_name', 'account_number']);
=======
            if (Schema::hasColumn('invoices', 'bank_name')) {
                $table->dropColumn('bank_name');
            }
            if (Schema::hasColumn('invoices', 'account_number')) {
                $table->dropColumn('account_number');
            }
>>>>>>> 1b16b0915417b914bb03ff291c20d9b95894c711
        });
    }
};
