<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Site;
use App\Variable;
use DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class AppController extends Controller
{	
	
	// Coming Soon
	public function comingsoon() {
		
		return view('comingsoon');
		
	}
	
	public function test() {
		
		// This was used for development, and is an easy way 
		// to try out new code at /test in a web browser
		
	}
	
	public function app() {
		
		return view('pages.grid');
		
	}
	
    public function pages() {
        // Name all the pages
		// This could be moved to DB
		
		$pages = [$this->gsl(), $this->gamut(), $this->rbc(), $this->bio()];
		
		return response()->json($pages);
    }
    
    public function gsl() {
	    $page = [];
	    $page['id'] = "gsl";
	    $page['img'] = "/img/bubbles/gsl.png";
	    $page['bubblescale'] = .25;
	    $page['name'] = "Great Salt Lake Watershed";
	    $page['text'] = "Where all the water goes.";
	    $page['type'] = "Photos";
	    $page['topics'] = [
		    [
			    'name' => 'Watershed',
			    'text' => 'Water, water, everywhere.'
		    ],
		    [
			    'name' => 'Rivers',
			    'text' => 'A river runs through us.'
		    ],
		    [
			    'name' => 'Storm Drains',
			    'text' => 'We all live downstream.'
		    ]
	    ];
	    return $page;
    }
    
    public function gamut() {
	    $page = [];
	    $page['id'] = "gamut";
	    $page['img'] = "/img/bubbles/gamut.png";
	    $page['bubblescale'] = .22;
	    $page['name'] = "GAMUT Project";
	    $page['text'] = "Learn about the GAMUT project.";
	    $page['type'] = "Photos";
	    $page['topics'] = [
		    [
			    'name' => 'Aquatic Monitoring Stations',
			    'text' => 'Check out these sites along the river.'
		    ],
		    [
			    'name' => 'Sensors',
			    'text' => 'Always on, always sensing.'
		    ],
		    [
			    'name' => 'Database',
			    'text' => 'Millions of data samples.'
		    ]
	    ];
	    return $page;
    }
    
    public function rbc() {
	    
	    $page = [];
	    $page['id'] = "rbc";
	    $page['img'] = "/img/bubbles/rbc.png";
	    $page['bubblescale'] = .24;
	    $page['name'] = "Red Butte Creek";
	    $page['text'] = "Text explaining Red Butte Creek";
	    $page['type'] = "DataExplorer";
	    
	    //////////////
	    // Sites
	    $sites = Site::where('sitecode', 'LIKE', '%RB_%')->get();	    
	    foreach ($sites as $site) {
		    
		    // Cleanup site name
		    $remove = [' Basic Aquatic', ' Advanced Aquatic', 'Provo River at ', 'Provo River near ', 'Provo River Below ', 'Logan River at ', 'Logan River near '];
			foreach ($remove as $r) {
				$site['sitename'] = str_replace($r, '', $site['sitename']);
			}
		    
		    // Convert Lat/Lon to float
		    $site['latitude'] = floatval($site['latitude']);
		    $site['longitude'] = floatval($site['longitude']);
		    
		    // Append array of series at site
		    $series = [];
		    foreach (DB::table('series')->select('variablecode')->where('sitecode', '=', $site->sitecode)->get() as $var) {
			    $series[] = $var->variablecode;
		    }
		    $site->series = $series;
		    
	    }
	    $page['sites'] = $sites;
	    
	    //////////////
	    // Variables
	    $page['variables'] = Variable::get();
	    
	    //////////////
	    // Topics
	    $page['topics'] = [
	    	// All Varaibles, one site
			[ 	'name' => 'Explore the Data',
				'text' => 'Examine all the data collected at a station.',
				'variables' => ['WaterTemp_EXO', 'ODO', 'pH', 'SpCond', 'TurbMed', 'Stage', 'Level'],
				'sites' => 'ONE'
			],
			
			// Curated pairs
			[
				'name' => 'Dissolved Oxygen and Temperature',
				'text' => '',
				'variables' => ['ODO', 'WaterTemp_EXO'],
				'sites' => 'ONE'
			],
			[
				'name' => 'Turbidity and Water Level',
				'text' => '',
				'variables' => ['TurbMed', 'Stage', 'Level'],
				'sites' => 'ONE'
			],
			
			// Single Variables
			// These were selected because they are common among all sites
			[
				'name' => 'Temperature',
				'text' => '',
			    'variables' => ['WaterTemp_EXO'],
			    'sites' => 'MANY'
		    ],
		    [
			    'name' => 'Dissolved Oxygen',
			    'text' => '',
			    'variables' => ['ODO'],
			    'sites' => 'MANY'
		    ],
		    [
			    'name' => 'pH',
			    'text' => '',
			    'variables' => ['pH'],
			    'sites' => 'MANY'
		    ],
		    [
			    'name' => 'Specific Conductance',
			    'text' => '',
			    'variables' => ['SpCond'],
			    'sites' => 'MANY'
		    ],
		    [
			    'name' => 'Turbitdity',
			    'text' => '',
			    'variables' => ['TurbMed'],
			    'sites' => 'MANY'
		    ],
		    [
			    'name' => 'Water Level',
			    'text' => '',
			    'variables' => ['Stage','Level'],
			    'sites' => 'MANY'
			]			
	    ];
	    
	    return $page;
    }
    
    public function bio() {
		$page = [];
	    $page['id'] = "bio";
	    $page['img'] = "/img/bubbles/bio.png";
	    $page['bubblescale'] = .21;
	    $page['name'] = "Biodiveristy";
	    $page['text'] = "Learn about the life in the creek.";
	    $page['type'] = "Photos";
	    $page['topics'] = [
		    [
			    'name' => 'Fish',
			    'text' => 'Small fish in a big pond.'
		    ],
		    [
			    'name' => 'Birds',
			    'text' => 'Birds of a feather...'
		    ],
		    [
			    'name' => 'Mammals',
			    'text' => 'Mammals Mammals Mammals!'
		    ],
		    [
			    'name' => 'Amphibians',
			    'text' => "It's not easy being green."
		    ],
		    [
			    'name' => 'Reptiles',
			    'text' => "Snakes. Why'd it have to be snakes?"
		    ],
		    [
			    'name' => 'Crustaceans',
			    'text' => 'Crusty?'
		    ],
		    [
			    'name' => 'Insects',
			    'text' => 'Lots of bugs!'
		    ],
		    [
			    'name' => 'Plants',
			    'text' => 'Lots of plants.'
		    ]
	    ];
	    return $page;
    }

}
