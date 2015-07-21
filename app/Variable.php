<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Variable extends Model
{
    protected $fillable = [
		'variablecode',
		'variablename',
		'variableunitsname',
		'variableunitsabbreviation'
    ];
    
    public $timestamps = false;
}
