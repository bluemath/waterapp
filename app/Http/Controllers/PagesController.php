<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class PagesController extends Controller
{
	// Splash
    public function splash() {
		
		// Images and text for circles
		
		$pages = [];
		
		$pages[] = ['controller' => 'PagesController@greatsaltlake',
					'name' => 'Great Salt Lake Watershed'];
		$pages[] = ['controller' => 'PagesController@gamut',
					'name' => 'GAMUT'];
		$pages[] = ['controller' => 'PagesController@redbuttecreek',
					'name' => 'Red Butte Creek'];
		$pages[] = ['controller' => 'PagesController@biodiversity',
					'name' => 'Biodiversity'];
					
		return view('pages.splash', compact('pages'));
		
    }

	// Great Salt Lake    
    public function greatsaltlake() {

    }
    
    // GAMUT
    public function gamut() {
	    
    }
    
    // Red Butte Creek
    public function redbuttecreek() {
	    
    }
    
    // Biodiversity
    public function biodiversity() {
	    
    }
}
