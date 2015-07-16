<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    protected $fillable = [
		'sitecode',
		'variablecode',
		'variablename',
		'variableunitsname',
		'variableunitsabbreviation',
		'datatype',
		'getdataurl',
		'methoddescription',
    ];
    
    // In retrospect, the details specific to a variable should be stored in another table
    // However, I'm running out of time so it stays this way
    
    public $timestamps = false;
}
