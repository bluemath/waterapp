<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = [
		'network',
	    'sitecode',
	    'sitename',
	    'latitude',
	    'longitude',
    ];
}
