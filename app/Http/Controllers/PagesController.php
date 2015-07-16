<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Site;

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
	    // Get RB sites that are not USGS
	    $sites = Site::where('sitecode', 'LIKE', '%RB_%')->where('sitecode', 'NOT LIKE', '%USGS%')->get();
	    
	    foreach ($sites as $site) {
		    $site->series = Series::where('sitecode', '=', $site->sitecode)->all();
	    }
	    
	    return view('pages.rbc', compact('sites'));
    }
    
    // Biodiversity
    public function biodiversity() {
	    
    }
}
