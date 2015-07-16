<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Data extends Model
{
    protected $fillable = [
		'sitecode',
		'variablecode',
		'datetime',
		'value'
    ];
    
    public $timestamps = false;
    
    //protected $dateFormat = 'Y-m-d\TH:i:s';
    
    protected $dates = array('datetime');

}
