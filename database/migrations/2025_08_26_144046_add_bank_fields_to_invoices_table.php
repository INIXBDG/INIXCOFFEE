<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migration untuk menambah kolom baru di tabel invoices.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Tambah kolom bank_name setelah 'amount'
            if (!Schema::hasColumn('invoices', 'bank_name')) {
                $table->string('bank_name')->nullable()->after('amount');
            }

            // Tambah kolom account_number setelah 'bank_name'
            if (!Schema::hasColumn('invoices', 'account_number')) {
                $table->string('account_number')->nullable()->after('bank_name');
            }
        });
    }

    /**
     * Rollback migration: hapus kolom baru dari tabel invoices.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'bank_name')) {
                $table->dropColumn('bank_name');
            }
            if (Schema::hasColumn('invoices', 'account_number')) {
                $table->dropColumn('account_number');
            }
        });
    }
};
