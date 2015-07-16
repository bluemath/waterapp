<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use DB;
use Cache;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Carbon\Carbon;

// The models
use App\Site;
use App\Series;
use App\Data;

class DataController extends Controller
{
	///////////
	// Sites //
	///////////
	
	// Get a list of all known sites
	public function sites() {
		$sites = Site::all();
	    return view('data.sites', compact('sites'));
	}
	
	// Update the list of sites and variables
	public function sitesUpdate() {
		
		echo "<pre>";
		// Limit the types of sites and variables loaded
		// change these to load more or less

		// Only interested in Red Butte sites
		// ( Issues exist with USGS duplicates that break unique constraints if those sites are included )
		$siteCodeContains = ['RB_']; 

		// Only interested in Stream data
		$siteTypes = ['Stream']; 
		
		// Only interested in common variables
		$variableLevels = ['Common'];
		
		// Only interested in RAW data
		$qualityControlLevelCodes = [0];
		
		// Get Sites
		$siteKeys = ['network' => '', 'sitecode' => '', 'sitename' => '', 'latitude' => '', 'longitude' => ''];
		$sitesJSON = file_get_contents("http://data.iutahepscor.org/tsa/api/v1/sites/?limit=0");
		$sites = json_decode($sitesJSON);
		foreach ($sites->objects as $site) {
			// Only add desiered site types
			if(in_array($site->sitetype, $siteTypes)) {
				// Only add desired sitecodes
				foreach($siteCodeContains as $piece) {
					if(strpos($site->sitecode, $piece) !== false) {
						// Clean up array
						$site = (array) $site;
						$site = array_intersect_key($site, $siteKeys);
						// Add
						Site::updateOrCreate((array) $site);
						break;
					}
				}
			}
		}
		
		// Get Dataseries
		$seriesKeys = ['sitecode' => '', 'variablecode' => '', 'variablename' => '', 'variableunitsname' => '', 'variableunitsabbreviation' => '', 'datatype' => '', 'getdataurl' => '', 'methoddescription' => ''];
		$seriesJSON = file_get_contents("http://data.iutahepscor.org/tsa/api/v1/dataseries/?limit=0");
		$series = json_decode($seriesJSON);
		foreach ($series->objects as $s) {
			// Only add if site was added
			if(!Site::where(['sitecode' => $s->sitecode])->get()->isempty()) {
				// Only add desired variables
				if (in_array($s->variablelevel, $variableLevels) && in_array($s->qualitycontrollevelcode, $qualityControlLevelCodes)) {
					// Clean up array
					$s = (array) $s;
					$s = array_intersect_key($s, $seriesKeys);
					// Add
					Series::updateOrCreate((array) $s);
				}
			}
		}
		
		// Redirect to sites list
		return redirect()->action('DataController@sites');
	}
	
	
	////////////
	// Series //
	////////////
	
	/**
	 * Get the dataseries variables for a given site
	 * 
	 * @access public
	 * @param mixed $sitecode
	 * @return a view for the series
	 */
	public function series($sitecode) {
		$where = ['sitecode' => $sitecode];
	    $series = Series::where($where)->get();
	    if(count($series) > 0) {
			return view('data.series', compact('series'));    
	    }
	    echo "$sitecode is not a valid sitecode. You could <a href='" . action('DataController@sitesUpdate') . "'>update</a> the list of sitecodes.";
    }
    
    public function seriesUpdate($sitecode) {
	    
	    // Get the series for the sitecode
		$where = ['sitecode' => $sitecode];
	    $series = Series::where($where)->get();
	    
	    // Update each series
	    foreach ($series as $s) {
		    $this->dataUpdate($sitecode, $s->variablecode);
	    }
	    
		// Redirect
	    return redirect()->back();
    }

	//////////
	// Data //
	//////////
    
    /**
	 * dataRange function.
	 * 
	 * @access public
	 * @param mixed $sitecode
	 * @param mixed $variablecode
	 * @param mixed $start in YYYY-MM-DDTHH:MM:SSZ format
	 * @param mixed $end in YYYY-MM-DDTHH:MM:SSZ format
	 * @return void
	 */
	public function data(Request $request, $sitecode, $variablecode, $start = null, $end = null) {
	    
	    if ($start == null) {
		    // Cache the full dataset
			$array = Cache::rememberForever("$sitecode/$variablecode", function () use ($sitecode, $variablecode, $start, $end) {
				return $this->dataArray($sitecode, $variablecode, $start, $end);
			});    
	    } else {
		    // Generate subsets dynamically
		    $array = $this->dataArray($sitecode, $variablecode, $start, $end);
	    }
	    	    
	    // Return JSON or JSONP
	    return response()->json($array)->setCallback($request->input('callback'));
    }
    
    private function dataArray($sitecode, $variablecode, $start = null, $end = null) {
	    
	    $where = ['sitecode' => $sitecode, 'variablecode' => $variablecode];
	    
	    // Limit the results based on $start and $end times
	    if($start == null) { 
		    $data = Data::select('datetime', 'value')->where($where)->get();
	    } else if($end == null) {
		    $data = Data::select('datetime', 'value')->where($where)->where('datetime', '>', $start)->get();
	    } else {
		    // whereBetween not working...
		    // $data = Data::select('datetime', 'value')->where($where)>whereBetween('datetime',  [$start, $end])->get();
		    // so just do the same as above:
		    $data = Data::select('datetime', 'value')->where($where)->where('datetime', '>', $start)->get();
	    }
	    
	    return $this->toArray($data);
    }
    
    private function toArray($data) {
		$ra = [];
		foreach ($data as $d) {
			$t = $d->datetime->timestamp * 1000;
			$v = (float) $d->value;
			$ra[] = [$t, $v];
		}
		return $ra;
	}

	public function dataUpdate($sitecode, $variablecode) {
	    // This will get new data since the last update (via the ML endpoint)
	    
	    // Get URL
	    $where = ['sitecode' => $sitecode, 'variablecode' => $variablecode];
	    $series = Series::where($where)->first();
	    $getdataURL = $series->getdataurl;
	    
	    // Parse the URL
	    $parsedURL = parse_url($getdataURL);
	    
	    // Parse the query
	    parse_str($parsedURL['query'], $query);
	    
	    // Add appropriate start and end times to the query
	    // Get the most recent timestamp
	    $where = ['sitecode' => $sitecode, 'variablecode' => $variablecode];
	    $data = Data::where($where)->orderBy('datetime', 'desc')->first();
	    
	    // startDate is the date of the most recent (+1s) or empty if never updated
	    if($data != null) {
		    $lastTimestamp = $data->datetime;
		    $lastTimestamp->addSecond();
		    $query['startDate'] = $lastTimestamp->setTimezone('UTC')->toW3cString();
	    }

	    // endDate is now
		$query['endDate'] = Carbon::now()->setTimezone('UTC')->toW3cString();
	    
	    // Rebuild the URL
	    $parsedURL['query'] = http_build_query($query);
	    $url = $parsedURL['scheme'] . "://" . $parsedURL['host'] . $parsedURL['path'] . "?" . $parsedURL['query'];
	    
	    echo $url;
	    
	    // TESTING
	    // $url = 'http://lar.dockthis.com/xml/dataseries.xml';
	    
	    // Get the XML (timed)
	    $time_pre = microtime(true);
		$xml = simplexml_load_file($url);
		$time_post = microtime(true);
		$exec_time = number_format($time_post - $time_pre, 1);
	    echo "<br>XML downloaded in $exec_time seconds<br>";
	    echo "got " . count($xml->timeSeries->values->value) . " values from '" . $query['startDate'] . "' until now<br>";

		// Filter out bad data
		$noValue = $xml->timeSeries->variable->noDataValue;

		// This section uses the Database facade to bulk insert
		// Add values to the database (timed)
		$time_pre = microtime(true);
		$data = [];
		echo "Not saving data with values of $noValue<br>";
		foreach ($xml->timeSeries->values->value as $value) {
			if((string) $value != $noValue) {
				$data[] = [	'sitecode' => $sitecode,
						'variablecode' => $variablecode,
						'datetime' => $value->attributes()->dateTimeUTC,
						'value' => (string) $value ];
			}
		}
		$time_post = microtime(true);
		$exec_time = number_format($time_post - $time_pre, 1);
	    echo "Filtered down to " . count($data) . " values in $exec_time seconds<br>";
		
		// Add to the DB
		$time_pre = microtime(true);
		DB::transaction(function () use ($data) {
			foreach ($data as $d) {
				
				// Old way, could cause key problems
				// http://code.openark.org/blog/mysql/replace-into-think-twice
				//DB::statement("REPLACE INTO data (sitecode, variablecode, datetime, value) VALUES (:sitecode, :variablecode, :datetime, :value)", $d);
				
				// Better way
				DB::statement("INSERT INTO data (sitecode, variablecode, datetime, value) VALUES(:sitecode, :variablecode, :datetime, :value) ON DUPLICATE KEY UPDATE value = VALUES(value)", $d);
			}
		});
		$time_post = microtime(true);
		$exec_time = number_format($time_post - $time_pre, 1);
	    echo "Inserted " . count($data) . " into database in $exec_time seconds<br>";

		// Remove the cache
		Cache::forget("$sitecode/$variablecode");
		
		// Rebuild the cache
		$time_pre = microtime(true);
		$this->data(new Request(), $sitecode, $variablecode);
		$time_post = microtime(true);
		$exec_time = number_format($time_post - $time_pre, 1);
	    echo "Rebuilt the cache in $exec_time seconds<br>";
	    
		// Redirect
	    return redirect()->back();
    }

}
