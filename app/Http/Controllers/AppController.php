<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Site;
use App\Variable;
use DB;
use Carbon\Carbon;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Cameras;

class AppController extends Controller
{	
	
	// Coming Soon
	public function comingsoon() {
		
		return view('comingsoon');
		
	}
	
	public function test() {
		
		// This was used for development, and is an easy way 
		// to try out new code at /test in a web browser
		
		$ts = 1438659900;
		echo Carbon::createFromTimeStamp($ts)->toDateTimeString(), "<br>";
		echo Carbon::createFromTimeStamp($ts, 'America/Denver')->timestamp, "<br>";
		echo Carbon::createFromTimeStampUTC($ts)->toDateTimeString();
		
	}
	
	public function app() {
		
		return view('app');
		
	}
	
    public function pages() {
        // Enumerate all the pages
		$pages = [$this->gsl(), $this->gamut(), $this->rbc(), $this->lr(), $this->bio()];
		// Send back as JSON
		return response()->json($pages);
    }
    
    public function gsl() {
	    $page = [];
	    $page['id'] = "gsl";
	    $page['img'] = "/img/bubbles/gsl.png";
	    $page['bubblescale'] = .25;
	    $page['name'] = "Explore the Great Salt Lake Watershed";
	    $page['text'] = [];
	    $page['text'][] = "The Great Salt Lake watershed is enormous—it covers nearly 35,000 square miles. Most of its water comes from three watersheds east of the Lake: Bear River, Weber River, and Jordan River watersheds.";
	    $page['text'][] = "The Jordan River watershed includes most of Salt Lake County within its borders. Seven major tributaries feed the Jordan River as it makes its way from Utah Lake through the Salt Lake Valley. Each tributary has its own watershed—it’s a converging system of drainages all flowing to the Great Salt Lake.";
	    $page['type'] = "Photos";
	    $page['topics'] = [
		    [
			    'name' => 'Jordan River Watershed',
			    'text' => ['']
		    ],
		    [
			    'name' => 'Rivers',
			    'text' => ['A river runs through us.']
		    ],
		    [
			    'name' => 'Storm Drains',
			    'text' => ['We all live downstream.']
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
	    $page['text'] = ["Learn about the GAMUT project."];
	    $page['type'] = "Photos";
	    $page['topics'] = [
		    [
			    'name' => 'Aquatic Monitoring Stations',
			    'text' => ['Check out these sites along the river.'],
			    'default' => 2,
			    'photos' => [
				    [
					    'img' => '/img/gamut/RB_KF_BA.jpg',
					    'caption' => 'This is the highest monitoring station on the creek: Knowlton Fork.'
				    ],
				    [
					    'img' => '/img/gamut/RB_ARBR_AA.jpg',
					    'caption' => 'Above Red Butte Reservoir'
				    ],
				    [
					    'img' => '/img/gamut/RB_RBG_BA.jpg',
					    'caption' => 'This is the site near the gate that prevents access to the Red Butte Creek protected area.'
				    ],
				    [
					    'img' => '/img/gamut/RB_CG_BA.jpg',
					    'caption' => 'Cottams Grove.'
				    ],
				    [
					    'img' => '/img/gamut/RB_FD_AA.jpg',
					    'caption' => 'Foothill Drive.'
				    ]
			    ]
		    ],
		    [
			    'name' => 'Sensors',
			    'text' => ['Always on, always sensing.']
		    ],
		    [
			    'name' => 'Database',
			    'text' => ['Millions of data samples.']
		    ]
	    ];
	    return $page;
    }

    public function lr() {
	    
	    $page = [];
	    $page['id'] = "lr";
	    $page['img'] = "/img/bubbles/lr.png";
	    $page['bubblescale'] = .24;
	    $page['name'] = "Logan River";
	    $page['text'] = ["Text explaining Logan River"];
	    $page['type'] = "Data";

	    $page['sites'] = $this->sites("LR_");
	    $page['variables'] = $this->variables();
	    $page['topics'] = $this->topics();
	    
	    $page['zoom'] = 11;
	    
	    return $page;
    }
    
    public function rbc() {
	    
	    $page = [];
	    $page['id'] = "rbc";
	    $page['img'] = "/img/bubbles/rbc.png";
	    $page['bubblescale'] = .24;
	    $page['name'] = "Red Butte Creek";
	    $page['text'] = ["Text explaining Red Butte Creek"];
	    $page['type'] = "Data";

	    $page['sites'] = $this->sites("RB_");
	    $page['variables'] = $this->variables();
	    $page['topics'] = $this->topics();
	    
	    $page['zoom'] = 13;
	    
	    return $page;
    }
    
    private function sites($like) {
	    $sites = Site::where('sitecode', 'LIKE', "%$like%")->get();	    
	   
		$cameras = Cameras::sites();
	   
	    foreach ($sites as $site) {
		    
		    // Cleanup site name
		    $remove = [' Basic Aquatic', ' Advanced Aquatic', 'Provo River at ', 'Provo River near ', 'Provo River Below ', 'Logan River at ', 'Logan River near '];
			foreach ($remove as $r) {
				$site['sitename'] = str_replace($r, '', $site['sitename']);
			}
		    
		    // Convert Lat/Lon to float
		    $site['latitude'] = floatval($site['latitude']);
		    $site['longitude'] = floatval($site['longitude']);
		    
		    // Check to see if site has a camera
		    $site['camera'] = false;
		    if(in_array($site['sitecode'], $cameras)) {
			    $site['camera'] = true;
		    }
		    
		    // Append array of series at site
		    $series = [];
		    foreach (DB::table('series')->select('variablecode')->where('sitecode', '=', $site->sitecode)->get() as $var) {
			    $series[] = $var->variablecode;
		    }
		    $site->series = $series;
		    
	    }
	    
	    return $sites;
    }
    
    private function variables() {
	    return Variable::get();
    }
    
    private function topics() {
	    return [
	    	// All Varaibles, one site
			[ 	'name' => 'Explore the Data',
				'text' => ['Examine all the data collected at a station.'],
				'variables' => ['WaterTemp_EXO', 'ODO', 'pH', 'SpCond', 'TurbMed', 'Stage', 'Level'],
				'mode' => 'ONE'
			],
			
			// Curated pairs
			[
				'name' => 'Dissolved Oxygen and Temperature',
				'text' => [''],
				'variables' => ['ODO', 'WaterTemp_EXO'],
				'mode' => 'ONE'
			],
			[
				'name' => 'Turbidity and Water Level',
				'text' => [''],
				'variables' => ['TurbMed', 'Stage', 'Level'],
				'mode' => 'ONE'
			],
			
			// Single Variables
			// These were selected because they are common among all sites
			[
				'name' => 'Temperature',
				'text' => [''],
			    'variables' => ['WaterTemp_EXO'],
			    'mode' => 'MANY'
		    ],
		    [
			    'name' => 'Dissolved Oxygen',
			    'text' => [''],
			    'variables' => ['ODO'],
			    'mode' => 'MANY'
		    ],
		    [
			    'name' => 'pH',
			    'text' => [''],
			    'variables' => ['pH'],
			    'mode' => 'MANY'
		    ],
		    [
			    'name' => 'Specific Conductance',
			    'text' => [''],
			    'variables' => ['SpCond'],
			    'mode' => 'MANY'
		    ],
		    [
			    'name' => 'Turbitdity',
			    'text' => [''],
			    'variables' => ['TurbMed'],
			    'mode' => 'MANY'
		    ],
		    [
			    'name' => 'Water Level',
			    'text' => [''],
			    'variables' => ['Stage','Level'],
			    'mode' => 'MANY'
			]			
	    ];
    }
    
    public function bio() {
		$page = [];
	    $page['id'] = "bio";
	    $page['img'] = "/img/bubbles/bio.png";
	    $page['bubblescale'] = .21;
	    $page['name'] = "Biodiveristy";
	    $page['text'] = ["Learn about the life in the creek."];
	    $page['type'] = "Photos";
	    $page['topics'] = [
		    [
			    'name' => 'Fish',
			    'text' => ['Small fish in a big pond.']
		    ],
		    [
			    'name' => 'Birds',
			    'text' => ['Birds of a feather...']
		    ],
		    [
			    'name' => 'Mammals',
			    'text' => ['Mammals Mammals Mammals!']
		    ],
		    [
			    'name' => 'Amphibians',
			    'text' => ["It's not easy being green."]
		    ],
		    [
			    'name' => 'Reptiles',
			    'text' => ["Snakes. Why'd it have to be snakes?"]
		    ],
		    [
			    'name' => 'Crustaceans',
			    'text' => ['Crusty?']
		    ],
		    [
			    'name' => 'Insects',
			    'text' => ['Lots of bugs!']
		    ],
		    [
			    'name' => 'Plants',
			    'text' => ['Lots of plants.']
		    ]
	    ];
	    return $page;
    }

}
