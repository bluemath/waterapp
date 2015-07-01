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
		'datatype',
		'getdataurl',
		'methoddescription'
    ];
}
