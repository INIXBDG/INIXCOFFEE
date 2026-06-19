<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToRegistryFeaturesTable extends Migration
{
    public function up()
    {
        Schema::table('registry_features', function (Blueprint $table) {
            $table->text('fakta')->nullable();
            $table->text('harapan')->nullable();
            $table->string('waktu_perkiraan')->nullable();
           $table->string('ticket_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('registry_features', function (Blueprint $table) {
            $table->dropColumn(['fakta', 'harapan', 'waktu_perkiraan', 'ticket_id']);
        });
    }
}
