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
	
	// Client side of the exhibit to display 'Coming Soon'
	// The route is /
	public function comingsoon() {
		return view('comingsoon');
	}
	
	// Client side of the exhibit
	// The route is /app, but could be swapped with comingsoon
	public function app() {
		return view('app');
	}
	
	
	// Used for development, and an easy way 
	// to try out new code / test in a web browser
	// The route /test runs this.
	public function test() {
/*
		// This is a test of manipulating timestamps in UTC v TZ mode
		$ts = 1439581500;
		echo Carbon::createFromTimeStamp($ts)->toDateTimeString(), "<br>";
		echo Carbon::createFromTimeStamp($ts)->timestamp, "<br>";
		echo Carbon::createFromTimeStamp($ts, 'America/Denver')->timestamp, "<br>";
		echo Carbon::createFromTimeStampUTC($ts)->toDateTimeString(), "<br>";
		echo Carbon::createFromTimeStampUTC($ts)->format('Y-m-d\TH:i:s'), "<br>";
*/

		// This is a test of parsing a remote file listing...
		// Get the remote directory listing
		$url = "http://data.iutahepscor.org/gamutphotos/UU_RB_KF_BA/";
		$html = file_get_contents($url);
		preg_match_all('/\/([^\/"]+\.jpg)/', $html, $uu);
		//preg_match_all('/\/(([0-9_]+)([A-Z_]+)([0-9]+)_([0-9]+)_([0-9]+)_([0-9]+)_([0-9]+)_([0-9]+).jpg)/', $html, $uu);
		$files = $uu[1];
		print_r($files);
		
		if(!($li = array_search("1902_RB_KF_BA_2015_10_16_04_00_12.jpg", $files))) $li = -1;
		$li++;
		
		for(;$li < count($files); $li++) {
					echo "<br>" . $files[$li];
				}
	}
	
	// This is the JSON data source the server provides for the 
	// backbone.js client side code.
	// The route /pages gets this.
    public function pages() {
        // Enumerate all the pages
		// $pages = [$this->gsl(), $this->gamut(), $this->rbc(), $this->lr(), $this->pr(), $this->bio()];
		$pages = [$this->gsl(), $this->gamut(), $this->rbc(), $this->bio()]; //
		// Send back as JSON
		return response()->json($pages);
    }
    
    public function gsl() {
	    $page = [];
	    $page['type'] = "Photos";
	    $page['id'] = "gsl";
	    $page['img'] = "/img/bubbles/gsl.jpg";
	    $page['bubblescale'] = .29;
	    $page['bubblename'] = "Great Salt Lake Watershed";
	    $page['name'] = "Explore the Great Salt Lake Watershed";
	    $page['text'] = [];
	    $page['text'][] = "The Great Salt Lake watershed is enormous&mdash;it covers nearly 35,000 square miles. Most of its water comes from three watersheds east of the Lake: Bear River, Weber River, and Jordan River watersheds. Each of these watersheds is fed by smaller watersheds. It’s a converging system of drainages all flowing to the Great Salt Lake.";
	    $page['topics'] = [
		    [
			    'name' => 'What is a watershed?',
			    'text' => ['A watershed is an area of land that drains into a particular stream, river, lake, or even an ocean. Wherever you are, you are in a watershed. Some watersheds are hilly; some are flat. Some are wild, while others are developed. Some watersheds are quite small, and some are huge. Large bodies of water are typically fed by many tributaries, and each tributary has its own watershed.'],
			    'default' => 0,
			    'photos' => [
				    [
					    'img' => '/img/gsl/1Watershed_0_GSL_v11_Roads.png',
					    'label' => "",
					    'caption' => '',
					    'type' => 'hidden'
				    ]
				]
		    ],
		    [
			    'name' => 'Watersheds are dynamic',
			    'text' => ['Every watershed is unique and change is ever present. Watershed boundaries and characteristics depend on interactions among the geology and topography of the region, climate, vegetation cover, habitats available for animals and other organisms, human impacts, and of course, the water cycle.'],
			    'default' => 0,
			    'photos' => [
				    [
					    'img' => '/img/gsl/wasatchback.jpg',
					    'label' => "",
					    'caption' => '',
					    'type' => 'hidden'
				    ],
				]
		    ],
		    [
			    'name' => 'Jordan River watershed',
			    'text' => ['Most of Salt Lake County falls within the boundaries of the Jordan River watershed, a 3,805 square mile basin. From its outlet at Utah Lake, the Jordan River flows north for 51 miles to the Great Salt Lake. Bounded by the Wasatch and Oquirrh Mountains, it meanders along the Salt Lake valley floor and is fed by seven tributary streams originating in the Wasatch Mountains.'],
			    'default' => 0,
			    'photos' => [
				    [
					    'img' => '/img/gsl/1Watershed_1c_Jordan_v12_Roads.png',
					    'label' => "",
					    'caption' => '',
					    'type' => 'hidden'
				    ]
				]
		    ],
		    [
			    'name' => 'Jordan River Tributaries',
			    'text' => ['Seven major tributaries feed the Jordan River in Salt Lake County: City Creek, Red Butte Creek, Emigration Creek, Parley’s Creek, Mill Creek, Big Cottonwood Creek and Little Cottonwood Creek. The high elevation watersheds of these seven tributaries are primarily uninhabited forest. In the Salt Lake Valley, the water passes through private land where residential, commercial, and industrial development have replaced vegetation and agriculture. Each of the tributaries is impacted by a variety of both natural and human impacts.'],
			    'default' => 0,
			    'photos' => [
				    [
					    'img' => '/img/gsl/1Watershed_2_SLValley_v12_LUCC.png',
					    'label' => "",
					    'caption' => '',
					    'type' => 'hidden'
				    ],
				    [
					    'img' => '/img/gsl/1Watershed_2_SLValley_v12_MajorRoads.png',
					    'label' => "",
					    'caption' => '',
					    'type' => 'hidden'
				    ],
				    [
					    'img' => '/img/gsl/1Watershed_2_SLValley_v12_Streams.png',
					    'label' => "",
					    'caption' => '',
					    'type' => 'hidden'
				    ],
				    [
					    'img' => '/img/gsl/1Watershed_2_SLValley_v13_Hwys.png',
					    'label' => "",
					    'caption' => '',
					    'type' => 'hidden'
				    ]
				]
		    ],
		    [
			    'name' => 'Red Butte Creek',
			    'text' => ['Look out the window and you’ll see the Bonneville Shoreline trail just in front of the Museum. Take a stroll heading north, and you’ll arrive at Red Butte Creek as it leaves Red Butte Garden and enters the built environment of Salt Lake City. Like the other Jordan River Tributaries, Red Butte Creek is a very different creek once it flows into the city.'],
			    'default' => 0,
			    'photos' => [
				    [
					    'img' => '/img/gsl/1Watershed_3_RBC_Oblique_v1.png',
					    'label' => "",
					    'caption' => '',
					    'type' => 'hidden'
				    ],
				    [
					    'img' => '/img/gsl/1Watershed_3_RBC_Oblique_v2.png',
					    'label' => "",
					    'caption' => '',
					    'type' => 'hidden'
				    ],
				    [
					    'img' => '/img/gsl/1Watershed_3_RBC_Oblique_v3.png',
					    'label' => "",
					    'caption' => '',
					    'type' => 'hidden'
				    ],
				    [
					    'img' => '/img/gsl/1950s.png',
					    'label' => "",
					    'caption' => '',
					    'type' => 'hidden'
				    ],
				    [
					    'img' => '/img/gsl/1950sleveled.png',
					    'label' => "",
					    'caption' => '',
					    'type' => 'hidden'
				    ]
				]
		    ]
	    ];
	    return $page;
    }
    
    public function gamut() {
	    $page = [];
	    $page['id'] = "gamut";
	    $page['img'] = "/img/bubbles/gamut.jpg";
	    $page['bubblescale'] = .21;
	    $page['bubblename'] = "The Whole GAMUT";
	    $page['name'] = "The Whole GAMUT";
	    $page['text'] = ["iUTAH Scientists and technicians have designed and installed a network of aquatic and climate monitoring stations along the Wasatch Front. Built to study water in “<strong>G</strong>radients <strong>A</strong>long <strong>M</strong>ountain-to-<strong>U</strong>rban <strong>T</strong>ransitions” (<strong>GAMUT</strong>) the network measures climate, hydrology, and water quality in three watersheds: Red Butte Creek, Logan River, and Provo River watersheds. Although alike in their primary source of water—winter snow—these three watersheds are very different in terms of human use of the land. GAMUT is providing baseline data to inform research about a wide range of issues related to water quality and quantity along the Wasatch Front."];
	    $page['type'] = "Photos";
	    $page['topics'] = [
		    [
			    'name' => 'Instruments',
			    'text' => ['Six aquatic stations along Red Butte Creek use state-of-the-art sensors to carry out real-time monitoring and reporting day in and day out.  Each station is solar powered and self-contained.'],
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
			    'name' => 'Solar Panel',
			    'text' => ['The sun generates power to run each station. A photovoltaic panel collects energy while the sun shines and stores it in a battery. This way, the system can run no matter the weather.'],
			    'default' => 0,
			    'photos' => [
				    [
					    'img' => '/img/gamut/solar.jpg',
					    'label' => '',
					    'caption' => '',
					    'type' => 'hidden'
				    ]
				]
		    ],
		    [
			    'name' => 'Datalogger',
			    'text' => ['A Campbell Scientific datalogger at the monitoring station collects data from each of the sensors, stores it, and transmits it to the computing center at Utah State University.'],
			    'default' => 0,
			    'photos' => [
				    [
					    'img' => '/img/gamut/datalogger.jpg',
					    'label' => '',
					    'caption' => '',
					    'type' => 'hidden'
				    ]
				]
		    ],
		    [
			    'name' => 'Multiparameter Water Quality Sonde',
			    'text' => ['This multi-port probe provides a state-of-the-art platform for the sensors used at each of the GAMUT water monitoring stations.  It is rugged, corrosion-resistant, and has a built in wiper to clear the sensors.'],
			    'default' => 0,
			    'background' => '/img/gamut/blur.png',
			    'photos' => [
				    [
					    'img' => '/img/gamut/sensors/sonde.png',
					    'label' => 'Sonde',
					    'caption' => '',
					    'type' => 'cutout'
				    ],
				    [
					    'img' => '/img/gamut/sensors/ph.png',
					    'label' => 'pH Sensor',
					    'caption' => 'Special glass in the pH sensor reacts with hydrogen ions in the water. This creates a small difference in voltage between the inside and outside of the bulb, which creates a weak electrical current—the more hydrogen ions in the water, the stronger the current.',
					    'type' => 'cutout'
				    ],
				    [
					    'img' => '/img/gamut/sensors/do.png',
					    'label' => 'Dissolved Oxygen Sensor',
					    'caption' => 'This sensor contains a specially dyed disk that reacts to a beam of blue light by emitting florescence&mdash;the more oxygen in the water, the faster the florescence fades away.',
					    'type' => 'cutout'
				    ],
				    [
					    'img' => '/img/gamut/sensors/alg.png',
					    'label' => 'Total Algae Sensor',
					    'caption' => 'Measuring the amount of algae and the number of algae species present in streams helps to assess water quality. Algae can become over-abundant when pollution from fertilizers, sediments and organic matter introduce too many nutrients into the water. When algae is too abundant, the biological balance of the stream becomes unbalanced causing oxygen levels to drop and fish and other organisms to die.',
					    'type' => 'cutout'
				    ],
				    [
					    'img' => '/img/gamut/sensors/sc.png',
					    'label' => 'Conductivity and Temperature Sensor',
					    'caption' => 'This sensor measures temperature as well as specific conductance. A weak electrical current moves through a hole in this probe. The saltier the water, the more readily the water conducts the current.',
					    'type' => 'cutout'
				    ],
				    [
					    'img' => '/img/gamut/sensors/p.png',
					    'label' => 'Pressure Transducer',
					    'caption' => 'The pressure transducer calculates how deep the water is based on how much pressure the water places on the sensor. Every 15 minutes the pressure transducer outputs an average of 25 measurements made in rapid succession.',
					    'type' => 'cutout'
				    ],
				    [
					    'img' => '/img/gamut/sensors/turb.png',
					    'label' => 'Turbidity Sensor',
					    'caption' => 'This sensor calculates turbidity by emitting light into the water and measuring how much is reflected back. Suspended soil, algae, and other particles make water murky and decrease the passage of light through it. ',
					    'type' => 'cutout'
				    ],
				    [
					    'img' => '/img/gamut/sensors/cdom.png',
					    'label' => 'CDOM Sensor',
					    'caption' => 'The effects of colored dissolved organic matter can be seen in both the color and clarity of water. Known as yellow substances, CDOM is the result of deteriorating organic materials and the tannins they release. Too much CDOM can impact biological activity by limiting light penetration into the water, limiting photosynthesis and negatively impacting plants and other organisms.',
					    'type' => 'cutout'
				    ]
				]
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
	    $page['bubblescale'] = .26;
	    $page['bubblename'] = "Red Butte Creek";
	    $page['name'] = "Red Butte Creek";
	    $page['text'] = ["Red Butte Creek watershed, located in narrow Red Butte Canyon, covers just over 11 square miles. It ranges in elevation from approximately 4900 feet to nearly 7900 feet. Red Butte Canyon is a Research Natural Area (RNA), managed by the U.S. Forest Service to preserve its significant natural ecosystems for scientific education and research. It’s a place where natural processes can be observed and compared to other areas where people regularly impact natural systems. And it’s one of three streams where iUTAH is monitoring aquatic data around the clock."];
	    $page['type'] = "Data";

	    $page['sites'] = $this->sites("RB_");
	    $page['variables'] = $this->variables();
	    $page['topics'] = $this->topics();
	    $page['poi'] = [
		    [
			    "name" => "Natural History Museum of Utah",
			    "icon" => "img/logos/nhmu.svg",
			    "latitude" => 40.764131,
			    "longitude" => -111.82279
		    ]
	    ];
	    
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
				'text' => ["Explore what’s happening in Red Butte Creek’s aquatic system by sliding your finger across the data stream to the right.  Choose a monitoring station from the map to see all the data feeds from that location, or compare data from different locations. You can also look at individual variables to see how they change over time and across stations."],
				'variables' => ['WaterTemp_EXO', 'ODO', 'pH', 'SpCond', 'TurbMed', 'Stage', 'Level'],
				'mode' => 'ONE'
			],
			
			// Curated pairs
/*
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
*/
			
			// Single Variables
			// These were selected because they are common among all sites
			[
				'name' => 'Water Temperature',
				'text' => ['Temperature impacts the kinds of organisms that can live in streams and rivers. Different species of fish, insects and other aquatic organisms have a preferred temperature range. When temperatures get too far above or below the acceptable range for a given species, their populations will decrease or be eliminated from the ecosystem.'],
			    'variables' => ['WaterTemp_EXO'],
			    'mode' => 'MANY'
		    ],
		    [
			    'name' => 'Dissolved Oxygen',
			    'text' => ['The concentration of oxygen gas incorporated in water is called dissolved Oxygen (DO). Oxygen is absorbed into water from the atmosphere; turbulence in the water increases this aeration.<br><br>
Water also absorbs oxygen released by aquatic plants as they photosynthesize. Dissolved oxygen is necessary for aquatic life, but too much can be a stressor for many organisms.'],
			    'variables' => ['ODO'],
			    'mode' => 'MANY'
		    ],
		    [
			    'name' => 'pH',
			    'text' => ['pH is a measure of how acidic or basic water is. Measured on a scale from 0 (acidic) to 14 (basic), a change of one unit corresponds to a tenfold change in acidity. Neutral water has a pH of 7. Stream water in the Red Butted Creek Watershed ranges between 7 and 9pH units.
<br><br>Most aquatic animals and plants have adapted a specific pH range, so a small change can cause big problems. Very acidic water will kill most fish and insects.
'],
			    'variables' => ['pH'],
			    'mode' => 'MANY'
		    ],
		    [
			    'name' => 'Specific Conductance',
			    'text' => ['The amount of dissolved solids, such as salt, determines the water’s specific conductance—a measure of the ability of water to conduct an electrical current. As water drains through soil, it dissolves salts and minerals increasing its specific conductance. Municipal and industrial uses may also introduce salts to water.<br><br>
The level of saltiness in water impacts cellular functions in aquatic plants and animals. This is an important water quality measure because high levels of salts can negatively impact the suitability of water for consumption by humans and animals, for agricultural use, and for industry. 
'],
			    'variables' => ['SpCond'],
			    'mode' => 'MANY'
		    ],
		    [
			    'name' => 'Turbitdity',
			    'text' => ['Turbidity is a measure of water clarity. Clear water has low turbidity and muddy water has high turbidity. Clay, silt, plant material, microorganisms, and industrial waste can all contribute to turbidity.<br><br>
Turbidity levels vary in streams as conditions change, and turbidity in turn can cause stream conditions to change. High stream flow can cause erosion, which brings more particulate matter into the water.'],
			    'variables' => ['TurbMed'],
			    'mode' => 'MANY'
		    ],
		    [
			    'name' => 'Gauge Height',
			    'text' => ['Water depth is measured with a pressure transducer. It measures the weight of the water above it, which increases with water depth. Together, water depth and stream velocity help us to determine stream flow.<br><br>
When stream flow is high, water can overflow into the stream’s floodplain. This can help to maintain a healthy riparian plant community while also filtering the water as it makes its way through the soil and back to the stream.'],
			    'variables' => ['Stage','Level'],
			    'mode' => 'MANY'
			]			
	    ];
    }
    
    public function bio() {
		$page = [];
	    $page['id'] = "bio";
	    $page['img'] = "/img/bubbles/bio.jpg";
	    $page['bubblescale'] = .19;
	    $page['bubblename'] = "Biodiversity";
	    $page['name'] = "Biodiversity";
	    $page['text'] = ["Red Butte Canyon is one of the last undisturbed watersheds in the Great Basin thanks to an unusual history of protected status from 1862 to the present. With low human impacts, a perennial stream, and multiple distinct plant communities across its elevational gradient, the canyon provides pristine habitat for a rich diversity of life."];
	    $page['type'] = "Hover";
	    $page['topics'] = [
			[
				'background' => '/img/bio/illustration.jpg',
				'hotspots' => [
					[
						'name' => ['Stellar\'s jay'],
						'subname' => ['Cyanocitta stelleri'],
						'text' => ['These beautiful blue jays are a common sight in coniferous forests and mountain shrub areas. They sometimes move to lower valleys in winter.'],
						'coords' => '2664,601,2668,633,2737,639,2745,665,2825,683,2817,631,2869,518,2815,500,2664,601',
						'img' => '/img/bio/StellersJay.jpg',
						'attribution' => 'None'
					],
					[
						'name' => ['Coyote'],
						'subname' => ['Canis latrans'],
						'text' => ['Coyotes are found in all parts of the United States across a variety of habitats. This coyote was photographed right behind the Museum. Coyotes are highly vocal animals engaging in yipping, barking, whining, and howling. Each type of vocalization delivers a particular message to others in the pack.'],
						'coords' => '549,749,701,846,854,928,954,914,936,766,1081,715,1049,627,1071,551,1033,541,942,601,760,565,665,516,555,557',

							 	'img' => '/img/bio/Coyote.jpg',
							 	'attribution' => 'None'
					],
					[
						'name' => ['1'],
						'subname' => ['()'],
						'text' => [''],
						'coords' => '1616,1868,1716,1927',

							 	'img' => '',
							 	'attribution' => ''
					],
					[
						'name' => ['Porcupine'],
						'subname' => ['Erethizon dorsatum'],
						'text' => ['Porcupines are slow moving herbivores that spend most of their time in trees eating leaves, twigs, and bark. They are equipped with sharp quills that detach when touched, to protect them against predators.'],
						'coords' => '3179,1384,3179,1306,3108,1268,3102,1121,3213,968,3386,954,3476,1005,3569,1085,3607,1200,3621,1272,3749,1368,3743,1441,3695,1453,3540,1455,3426,1463,3317,1435,3259,1467,3203,1471,3179,1384',

							 	'img' => '/img/bio/Porcupine.jpg',
							 	'attribution' => ''
					],
					[
						'name' => ['Mallard duck'],
						'subname' => ['Anas platyrhynchos'],
						'text' => ['Mallards are the most abundant duck in Utah. They are less common in winter than in summer, but were there is open water they appear year around.'],
						'coords' => '1296,962,1366,973,1402,1075,1501,1073,1543,1039,1589,1059,1626,1119,1575,1192,1439,1185,1360,1127,1324,1087,1250,1027,1256,981,1296,962',

							 	'img' => '/img/bio/Mallard.jpg',
							 	'attribution' => ''
					],
					[
						'name' => ['Mayfly larvae'],
						'subname' => ['Baetis sp'],
						'text' => ['Mayfly larvae may live in streams for two to three years breathing through gills on the sides of their bodies.'],
						'coords' => '884,1630,1089,1734,1185,1624,1165,1585,1087,1581,1039,1551,878,1561,884,1630',

							 	'img' => '/img/bio/MayflyLarva.png',
							 	'attribution' => ''
					],
					[
						'name' => ['Black-chinned hummingbird'],
						'subname' => ['Archilochus alexandri'],
						'text' => ['A common summer resident, the black-chinned hummingbird appears in April and may stay through early October&mdash;as long as there are flowers in bloom. '],
						'coords' => '2773,1139,2809,1204,2881,1188,2922,1137,2948,1085,2869,1081,2805,1103,2773,1139',

							 	'img' => '/img/bio/Hummingbird.jpg',
							 	'attribution' => ''
					],
					[
						'name' => ['Black fly'],
						'subname' => ['Simulium sp'],
						'text' => ['Sometimes called buffalo gnats. They’re very small and can get in your eyes, ears, mouth, and nose. While they don’t usually bit humans, they do bite horses, cattle, deer, and other wildlife.'],
						'coords' => '2574,1226,2552,1278,2628,1314,2678,1228,2638,1177,2590,1177,2574,1226',

							 	'img' => '/img/bio/BlackFly.jpg',
							 	'attribution' => ''
					],
					[
						'name' => ['Montane vole'],
						'subname' => ['Microtus montanus'],
						'text' => ['Living in varied habitats from woods to meadows, montane voles occur across western North America. Montane voles sometimes experience extreme population densities; a boon to owls and other raptors that rely on them as prey.'],
						'coords' => '3058,1853,3120,1913,3249,1879,3253,1828,3203,1796,3120,1802,3058,1853',

							 	'img' => '/img/bio/Vole.jpg',
							 	'attribution' => ''
					],
					[
						'name' => ['Caddisfly larvae'],
						'subname' => ['Hydropsychidae'],
						'text' => ['Caddisfly larvae often build cases to protect themselves from predators. Covered with twigs, caddis fly larvae can look like little log cabins. Some caddisfly larvae use grains of sand or tiny stones for their cases, while others use plants.'],
						'coords' => '1404,1887,1487,1923,1547,1857,1449,1802,1413,1836,1404,1887',

							 	'img' => '/img/bio/CaddisflyLarva.jpg',
							 	'attribution' => ''
					],
					[
						'name' => ['Black fly larvae'],
						'subname' => ['Simulium sp'],
						'text' => ['When black fly eggs hatch, the larvae float downstream with long silky threads trailing behind until they catch on rocks or logs. Tiny black fly larvae attach themselves by the thousands and catch bits of food it floats by.'],
						'coords' => '2240,1619,2305,1682,2427,1561,2401,1519,2289,1525,2240,1619',

							 	'img' => '/img/bio/BlackFlyLarva.png',
							 	'attribution' => ''
					],
					[
						'name' => ['Bonneville cutthroat trout'],
						'subname' => ['Oncorhynchus clarki utah'],
						'text' => ['This species has occupied Red Butte Creek for thousands of years, but their numbers have been dwindling in recent years. They are now considered a “sensitive species.” In 2011, the Utah Division of Fish and Wildlife reintroduced 3,000 Bonneville Cutthroat Trout to lower Red Butte Creek after clean up of two oil spills in the creek.'],
						'coords' => '1565,1772,1688,1800,1722,1845,1824,1873,1871,1869,1929,1895,1969,1881,1961,1863,2025,1832,2108,1832,2140,1760,2212,1778,2216,1622,2086,1642,2058,1557,1927,1539,1832,1551,1738,1597,1620,1656,1549,1714,1565,1772',

							 	'img' => '/img/bio/Trout.jpg',
							 	'attribution' => ''
					],
					[
						'name' => ['Water shrew'],
						'subname' => ['Sorex palustris'],
						'text' => ['Water shrews live near mountain streams in areas surrounded by heavy vegetation. They are strong swimmers that dive and swim to forage for food, even in the coldest weather. Aquatic insects and their larvae are their primary food, although they sometimes eat small fish, too.'],
						'coords' => '1216,1561,1234,1658,1356,1774,1409,1756,1376,1563,1226,1535,1216,1561',

							 	'img' => '/img/bio/Shrew.jpg',
							 	'attribution' => ''
					],
					[
						'name' => ['Mule deer'],
						'subname' => ['Odocoileus heminous'],
						'text' => ['Large mule-like ears, brownish-grey coloring, and a white rump make mule deer easy to identify. Mule deer diets are highly variable depending on season, region, and elevation.'],
						'coords' => '1706,993,1989,778,1919,683,1963,563,1909,534,1830,559,1782,603,1778,532,1672,536,1605,567,1527,561,1515,611,1561,663,1617,808,1630,878,1706,993',

							 	'img' => '/img/bio/MuleDeer.jpg',
							 	'attribution' => ''
					],
					[
						'name' => ['Spotted sandpiper'],
						'subname' => ['Actitis macularia'],
						'text' => ['These sandpipers are summer residents, appearing from late April to late September. They breed along waterways across a wide elevational range.'],
						'coords' => '2037,1045,2084,1123,2184,1127,2230,1017,2126,989,2058,997,2037,1045',

							 	'img' => '/img/bio/Sandpiper.jpg',
							 	'attribution' => ''
					],
					[
						'name' => ['Dipper'],
						'subname' => ['Cinclus mexicanus'],
						'text' => ['This species is a common resident all year along mountain streams where the water is swift. They are sometimes spotted along valley streams as well.'],
						'coords' => '1362,1334,1499,1222,1571,1258,1563,1290,1519,1348,1559,1390,1497,1413,1362,1334',

							 	'img' => '/img/bio/Dipper.jpg',
							 	'attribution' => ''
					],
					[
						'name' => ['Rattlesnake'],
						'subname' => ['various species'],
						'text' => ['Rattlesnakes are common in the foothills of Salt Lake City. They use venom to quickly kill their prey, and while highly poisonous, they rarely bite unless provoked or threatened. Steer clear when you see one on a trail.'],
						'coords' => '848,1298,1015,1318,1103,1316,1133,1425,1073,1479,912,1491,782,1535,647,1493,673,1413,848,1298',

							 	'img' => '/img/bio/Rattlesnake.jpg',
							 	'attribution' => ''	
					],
					[
						'name' => ['Mayfly'],
						'subname' => ['Baetis sp'],
						'text' => ['Adult mayflies may only live for twenty-four hours. They find a partner, mate, and lay eggs all in one day. Adult Mayflies often emerge in huge swarms near waterways. Listen closely you may hear the humming sound of thousands of these little insects.'],
						'coords' => '866,1202,981,1214,1019,1145,940,1073,864,1121,866,1202',

							 	'img' => '/img/bio/Mayfly.jpg',
							 	'attribution' => ''
					],
					[
						'name' => ['Caddisfly'],
						'subname' => ['Hydropsychidae'],
						'text' => [''],
						'coords' => '633,1212,709,1256,774,1236,715,1161,633,1133,633,1212',

							 	'img' => '/img/bio/Caddisfly.jpg',
							 	'attribution' => ''
					],
					[
						'name' => ['Yellow-bellied marmot'],
						'subname' => ['Marmota flaviventer'],
						'text' => ['You might hear a marmot before you see one; when a marmot sees a predator, it whistles to warn other marmots of the danger. Marmots spend about 80% of their time inside their burrows, which includes a long hibernation period.'],
						'coords' => '1252,536,1286,522,1334,541,1372,538,1368,486,1276,408,1222,394,1188,434,1208,500,1252,536',

							 	'img' => '/img/bio/Marmot.jpg',
							 	'attribution' => ''
					],
					[
						'name' => ['Black-capped chickadee'],
						'subname' => ['Parus atricapillus'],
						'text' => ['Chickadees, with their familiar call, are permanent residents throughout Utah. In winter they occupy woodlands and riparian areas. In summer they move to higher ground where they breed.'],
						'coords' => '999,892,964,970,993,1033,1015,1073,1075,1077,1121,1045,1198,989,1187,950,1135,981,999,892',

							 	'img' => '/img/bio/Chickadee.jpg',
							 	'attribution' => ''
					],
					[
						'name' => ['Belted kingfisher'],
						'subname' => ['Megaceryle alcyon'],
						'text' => ['As their name implies, these birds rely on fishing for sustenance. They are most common in the summer, but some remain through the winter. Their numbers have diminished in some areas because of nesting site disturbance.'],
						'coords' => '1607,1334,1792,1382,1899,1346,2041,1479,2134,1441,2066,1236,2126,1185,2045,1153,1836,1198,1698,1258,1607,1334',

							 	'img' => '/img/bio/Kingfisher.jpg',
							 	'attribution' => ''
					]
					
				]
			]  
		];
	    return $page;
    }

}
