<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Cache;

class MapController extends Controller
{
    public function relay(Request $request, $url) {
	    echo $url;
	    echo "<pre>";
	    echo($request->fullUrl());
    }
}
