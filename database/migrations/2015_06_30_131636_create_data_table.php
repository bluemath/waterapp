<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data', function (Blueprint $table) {
            
            // These are fields available in the WOF, XML endpoint. From a query like:
            // http://data.iutahepscor.org/RedButteCreekWOF/REST/waterml_1_1.svc/datavalues?location=iutah%3ARB_RBG_BA&variable=iutah%3AODO&startDate=2015-06-29&endDate=2015-06-30
            $table->string('sitecode');
            $table->string('variablecode');
			$table->timestamp('datetime')->index(); //UTC
            $table->string('value');
            
            $table->primary(['sitecode', 'variablecode', 'datetime']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('data');
    }
}
