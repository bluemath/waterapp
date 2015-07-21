<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    protected $fillable = [
		'sitecode',
		'variablecode',
		'getdataurl'
    ];
    
    public $timestamps = false;
}
