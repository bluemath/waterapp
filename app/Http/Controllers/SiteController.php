<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Site;
use App\Series;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class SiteController extends Controller
{
	
	// Coming Soon
	public function comingsoon() {
		
		return view('comingsoon');
		
	}
	
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

	// App
    public function app() {
		// Get RB sites that are not USGS
	    // (USGS now removed in DataController sitesUpdate)
	    $sites = Site::where('sitecode', 'LIKE', '%RB_%')->get();	    
	    
	    foreach ($sites as $site) {
		    $site->series = Series::where('sitecode', '=', $site->sitecode)->get();
	    }
	    
	    return view('pages.rbc', compact('sites'));
    }
    
}
