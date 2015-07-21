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
            
            // These are fields that are available from the dataseries endpoint
            // http://data.iutahepscor.org/tsa/api/v1/dataseries/?limit=0
            $table->string('sitecode');
            $table->string('variablecode');
			$table->string('getdataurl');
			
			$table->index('sitecode');
			$table->primary(['sitecode', 'variablecode']); // likely wont be used
            $table->unique(['sitecode', 'variablecode']);
			
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
