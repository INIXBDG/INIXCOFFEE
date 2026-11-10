
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instruktur_ol_pembayarans', function (Blueprint $table) {
            $table->dropIndex(['rkm_id']);
        });

        Schema::table('instruktur_ol_pembayarans', function (Blueprint $table) {
            $table->json('rkm_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('instruktur_ol_pembayarans', function (Blueprint $table) {
            $table->unsignedBigInteger('rkm_id')->nullable()->change();
        });

        Schema::table('instruktur_ol_pembayarans', function (Blueprint $table) {
            $table->index('rkm_id');
        });
    }
};
