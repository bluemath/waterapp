<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sites', function (Blueprint $table) {
            
            // These are fields that are available from the sites endpoint
            // http://data.iutahepscor.org/tsa/api/v1/sites/?limit=0
            $table->string('network')->index();
            $table->string('sitecode')->index();
            
            $table->primary('sitecode');
            $table->unique(['network', 'sitecode']);
            
            $table->string('sitename');
            $table->string('latitude');
            $table->string('longitude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sites');
    }
}
