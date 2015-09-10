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
		
		$ts = 1439581500;
		echo Carbon::createFromTimeStamp($ts)->toDateTimeString(), "<br>";
		echo Carbon::createFromTimeStamp($ts)->timestamp, "<br>";
		echo Carbon::createFromTimeStamp($ts, 'America/Denver')->timestamp, "<br>";
		echo Carbon::createFromTimeStampUTC($ts)->toDateTimeString(), "<br>";
		echo Carbon::createFromTimeStampUTC($ts)->format('Y-m-d\TH:i:s'), "<br>";
		
	}
	
	public function app() {
		
		return view('app');
		
	}
	
    public function pages() {
        // Enumerate all the pages
		// $pages = [$this->gsl(), $this->gamut(), $this->rbc(), $this->lr(), $this->pr(), $this->bio()];
		$pages = [$this->gsl(), $this->gamut(), $this->rbc(), $this->bio()];
		// Send back as JSON
		return response()->json($pages);
    }
    
    public function gsl() {
	    $page = [];
	    $page['id'] = "gsl";
	    $page['img'] = "/img/bubbles/gsl.jpg";
	    $page['bubblescale'] = .25;
	    $page['px'] = 270;
	    $page['name'] = "Explore the Great Salt Lake Watershed";
	    $page['text'] = [];
	    $page['text'][] = "The Great Salt Lake watershed is enormous&mdash;it covers nearly 35,000 square miles. Most of its water comes from three watersheds east of the Lake: Bear River, Weber River, and Jordan River watersheds. Smaller watersheds feed each of these watersheds. It’s a converging system of drainages all flowing to the Great Salt Lake.";
	    $page['type'] = "Photos";
	    $page['topics'] = [
		    [
			    'name' => 'What is a watershed?',
			    'text' => ['A watershed is an area of land that drains into a particular stream, river, lake, or even an ocean. Wherever you are, you are in a watershed. Some watersheds are hilly; some are flat. Some are wild, while others are developed. Some watersheds are quite small, and some are huge. Large bodies of water are typically fed by many tributaries, and each tributary has its own watershed.']
		    ],
		    [
			    'name' => 'Watersheds are dynamic',
			    'text' => ['Every watershed is unique and change is ever present. Watershed boundaries and characteristics depend on interactions among the geology and topography of the region, climate, vegetation cover, habitats available for animals and other organisms, human impacts, and of course, the water cycle.']
		    ],
		    [
			    'name' => 'Jordan River watershed',
			    'text' => ['Most of Salt Lake County falls within the boundaries of the Jordan River watershed, a 3,805 square mile basin. From its outlet at Utah Lake, the Jordan River flows north for 51 miles to the Great Salt Lake. Bounded by the Wasatch and Oquirrh Mountains, it meanders along the Salt Lake valley floor and is fed by seven tributary streams originating in the Wasatch Mountains.']
		    ],
		    [
			    'name' => 'Jordan River Tributaries',
			    'text' => ['Seven major tributaries feed the Jordan River in Salt Lake County: Little Cottonwood Creek, Big Cottonwood Creek, Mill Creek, Parley’s Creek, Emigration Creek, Red Butte Creek, and City Creek. The high elevation watersheds of these seven tributaries are primarily uninhabited forest lands. In the valley bottoms, the watersheds are primarily private lands that include industrial and agricultural areas. This means that each of the tributaries is impacted by a variety of both natural and human impacts.']
		    ],
		    [
			    'name' => 'Red Butte Creek',
			    'text' => ['Look out the window and you’ll see the Bonneville Shoreline trail just in front of the Museum. Take a stroll heading north, and you’ll arrive at Red Butte Creek as it leaves Red Butte Garden and enters the built environment of Salt Lake City. Like the other Jordan River Tributaries, Red Butte Creek is a very different creek once it flows into the city.']
		    ]
	    ];
	    return $page;
    }
    
    public function gamut() {
	    $page = [];
	    $page['id'] = "gamut";
	    $page['img'] = "/img/bubbles/gamut.jpg";
	    $page['bubblescale'] = .22;
	    $page['name'] = "GAMUT Project";
	    $page['text'] = ["iUTAH researchers have developed and deployed an ecohydrologic observatory to study water in ‘Gradients Along Mountain to Urban Transitions’ (GAMUT). The GAMUT Network measures aspects of climate, hydrology, and water quality along a mountain-to-urban gradient in three watersheds that share common water sources (winter-derived precipitation) but differ in the human and biophysical nature of land-use transitions. Designing GAMUT was a 12-month process involving faculty and technicians from across Utah’s research-intensive institutions: Brigham Young University, the University of Utah, and Utah State University."];
	    $page['type'] = "Photos";
	    $page['topics'] = [
		    [
			    'name' => 'Aquatic Monitoring Stations',
			    'text' => ['GAMUT includes state-of-the-art sensors at aquatic and terrestrial sites for real-time monitoring of common meteorological variables, snow accumulation and melt, soil moisture, surface water flow, and surface water quality.'],
			    'default' => 0,
			    'photos' => [
				    [
					    'img' => '/img/gamut/RB_KF_BA.jpg',
					    'label' => "Knowlton Fork",
					    'caption' => 'This is the highest monitoring station on the creek: Knowlton Fork.',
					    'type' => 'polaroid'
				    ],
				    [
					    'img' => '/img/gamut/RB_ARBR_AA.jpg',
					    'label' => "Above Red Butte Reservoir",
					    'caption' => 'Above Red Butte Reservoir',
					    'type' => 'polaroid'
				    ],
				    [
					    'img' => '/img/gamut/RB_RBG_BA.jpg',
					    'label' => "Red Butte Gate",
					    'caption' => 'This is the site near the gate that prevents access to the Red Butte Creek protected area.',
					    'type' => 'polaroid'
				    ],
				    [
					    'img' => '/img/gamut/RB_CG_BA.jpg',
					    'label' => "Cottams Grove",
					    'caption' => 'Cottams Grove.',
					    'type' => 'polaroid'
				    ],
				    [
					    'img' => '/img/gamut/RB_FD_AA.jpg',
					    'label' => "Foothill Drive",
					    'caption' => 'Foothill Drive.',
					    'type' => 'polaroid'
				    ]
			    ]
		    ],
		    [
			    'name' => 'Sensors',
			    'text' => ['Always on, always sensing.']
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

    public function pr() {
	    
	    $page = [];
	    $page['id'] = "pr";
	    $page['img'] = "/img/bubbles/lr.png";
	    $page['bubblescale'] = .24;
	    $page['name'] = "Provo River";
	    $page['text'] = ["Text explaining Provo River"];
	    $page['type'] = "Data";

	    $page['sites'] = $this->sites("PR_");
	    $page['variables'] = $this->variables();
	    $page['topics'] = $this->topics();
	    
	    $page['zoom'] = 11;
	    
	    return $page;
    }
    
    public function rbc() {
	    
	    $page = [];
	    $page['id'] = "rbc";
	    $page['img'] = "/img/bubbles/rbc.jpg";
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
	    $page['img'] = "/img/bubbles/bio.jpg";
	    $page['bubblescale'] = .21;
	    $page['name'] = "Biodiveristy";
	    $page['text'] = ["Learn about the life in the creek. Learn about the life in the creek. Learn about the life in the creek. Learn about the life in the creek. Learn about the life in the creek. Learn about the life in the creek. Learn about the life in the creek. Learn about the life in the creek. Learn about the life in the creek. Learn about the life in the creek. Learn about the life in the creek. Learn about the life in the creek.  Learn about the life in the creek.  Learn about the life in the creek.  Learn about the life in the creek. "];
	    $page['type'] = "Photos";
	    $page['topics'] = [
			[
				'name' => 'River',
				'text' => ['Explore the life along Red Butte Creek. Explore the life along Red Butte Creek. Explore the life along Red Butte Creek. Explore the life along Red Butte Creek. Explore the life along Red Butte Creek. Explore the life along Red Butte Creek.'],
				'default' => '0',
			    'photos' => [
				    [
					 	'type' => 'photo',
					 	'img' => '/img/bio/bio.jpg',
					 	'label' => 'The Creek',
				    ],
				    [
					    'img' => '/img/bio/icons/fish.png',
					    'label' => 'Certain Trout',
					    'type' => 'icon'
				    ],
				    [
					    'img' => '/img/bio/icons/grass.png',
					    'label' => 'Particular Grass',
					    'type' => 'icon'
				    ],
				    [
					    'img' => '/img/bio/icons/flies.png',
					    'label' => 'Type of Fly',
					    'type' => 'icon'
				    ],
				    [
					    'img' => '/img/bio/icons/rodent.png',
					    'label' => 'Breed of Mouse',
					    'type' => 'icon'
				    ],
				    [
					    'img' => '/img/bio/icons/snakes.png',
					    'label' => 'Specific Snake',
					    'type' => 'icon'
				    ]
			    ]	
			],
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
