<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Cameras;

class CamerasController extends Controller
{	
    public function siteslinked() {
		foreach (Cameras::sites() as $site) {
			$url = "/cameras/$site";
			echo("<a href='$url'>$site</a><br>");
		}
    }
    
    public function update() {		
		Cameras::update();
    }
    
    public function timestamps($sitecode) {
		$stamps = Cameras::timestamps($sitecode);
		return response()->json($stamps);
	}
}
