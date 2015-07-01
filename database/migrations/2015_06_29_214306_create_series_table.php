<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSeriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('series', function (Blueprint $table) {
            
            // Standard laravel
            $table->increments('id');
            $table->timestamps();
            
            // These are fields that are available from the dataseries endpoint
            // http://data.iutahepscor.org/tsa/api/v1/dataseries/?limit=0
            $table->string('sitecode')->index();
            $table->string('variablecode')->index();
            
			$table->string('variablename');
			$table->string('variableunitsname')->nullable();
			$table->string('datatype')->nullable();
			$table->string('getdataurl');
			$table->string('methoddescription');
			
			// In order to support faster updates, the last update is recorded
			$table->timestamp('lastupdate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('series');
    }
}
