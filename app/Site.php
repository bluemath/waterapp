<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    public $fillable = [
		'network',
	    'sitecode',
	    'sitename',
	    'latitude',
	    'longitude'
    ];
    
    public $timestamps = false;
}
