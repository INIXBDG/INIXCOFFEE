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
        Schema::table('tunjangan_karyawans', function (Blueprint $table) {
            $table->enum('status_approval', ['pending', 'approved', 'rejected'])->default('pending')->after('total');
            $table->unsignedBigInteger('approved_by')->nullable()->after('status_approval');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_note')->nullable()->after('approved_at');
            
            // Foreign key ke users table untuk GM yang approve
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tunjangan_karyawans', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropColumn(['status_approval', 'approved_by', 'approved_at', 'rejection_note']);
        });
    }
};