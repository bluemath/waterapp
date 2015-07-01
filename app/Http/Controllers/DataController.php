<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
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
		// Get Sites
		$sitesJSON = file_get_contents("http://data.iutahepscor.org/tsa/api/v1/sites/?limit=0");
		$sites = json_decode($sitesJSON);
		foreach ($sites->objects as $site) {
			$s = Site::firstOrCreate((array) $site);
		}
		
		// Get Dataseries
		$dataseriesJSON = file_get_contents("http://data.iutahepscor.org/tsa/api/v1/dataseries/?limit=0");
		$dataseries = json_decode($dataseriesJSON);
		foreach ($dataseries->objects as $ds) {
			$s = Series::firstOrCreate((array) $ds);
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
	    echo "$sitecode is not a valid sitecode. did you mean <a href='" . action('DataController@sitesUpdate') . "'>update?</a>";
    }

	//////////
	// Data //
	//////////
	
	public function toJavascript($data) {
		$ra = [];
		foreach ($data as $d) {
			$t = $d->datetime->timestamp * 1000;
			$v = $d->value;
			$ra[] = "[$t, $v]";
		}
		return "[".join($ra, ',')."]";
	}
	
	public function data($sitecode, $variablecode) {
		$where = ['sitecode' => $sitecode, 'variablecode' => $variablecode];
	    $data = Data::select('datetime', 'value')->where($where)->get();
	    return $this->toJavascript($data);
	    //return view('data.data', compact('data', 'sitecode', 'variablecode'));
    }
    
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
	public function dataRange($sitecode, $variablecode, $start, $end) {
	    // This will return data for the range 
	    $where = ['sitecode' => $sitecode, 'variablecode' => $variablecode];
	    if($end > 0) {
			$data = Data::select('datetime as t', 'value as v')->where($where)->whereBetween('datetime',  [$start, $end])->get();    
	    } else {
		    $data = Data::select('datetime as t', 'value as v')->where($where)->where('datetime', '>', $start)->get();
	    }
	    return $this->toJavascript($data);
	    //return view('data.data', compact('data', 'sitecode', 'variablecode'));
    }
    

	public function dataUpdate($sitecode, $variablecode) {
	    // This will get new data since the last update (XML endpoint)
	    
	    // Set the timezone (might need to change this to depend on site)
	    date_default_timezone_set('America/Denver');
	    
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
		    $lastTimestamp = strtotime($data->datetime);
		    $lastTimestamp++;
		    $query['startDate'] = date('Y-m-d\TH:i:s', $lastTimestamp);
	    }
	    
	    // endDate is now
		$query['endDate'] = date('Y-m-d\TH:i:s');
	    
	    // Rebuild the URL
	    $parsedURL['query'] = http_build_query($query);
	    $url = $parsedURL['scheme'] . "://" . $parsedURL['host'] . $parsedURL['path'] . "?" . $parsedURL['query'];
	    
	    // Because this could take a long time, set the timeout higher
	    set_time_limit(300); // 5 minutes
	    
	    
	    // Get the XML (timed)
	    $time_pre = microtime(true);
		$xml = simplexml_load_file($url);
		$time_post = microtime(true);
		$exec_time = number_format($time_post - $time_pre, 1);
	    echo "XML download: $exec_time seconds<br>";
	    echo "got " . count($xml->timeSeries->values->value) . " values from " . $query['startDate'] . " until now<br>";
	    
/*
		// This section uses Eloquent to add, so it's slow.
		// Add values to the database (timed)
		$time_pre = microtime(true);
		foreach ($xml->timeSeries->values->value as $value) {
			$d = [	'sitecode' => $sitecode,
					'variablecode' => $variablecode,
					'datetime' => $value['dateTime'],
					'value' => $value 					];
			
			// Previously was firstOrCreate, but that was slow. Since the startDate is
			// always after the latest record (+1s), create should work just as well
			$s = Data::create($d);
		}
		$time_post = microtime(true);
		$exec_time = number_format($time_post - $time_pre, 1);
	    echo "Eloquent record insert: $exec_time seconds<br>";
*/

		// This section uses the Database facade to bulk insert
		// Add values to the database (timed)
		$time_pre = microtime(true);
		$data = [];
		foreach ($xml->timeSeries->values->value as $value) {
			$data[] = [	'sitecode' => $sitecode,
						'variablecode' => $variablecode,
						'datetime' => $value['dateTime'],
						'value' => $value ];
		}
		$time_post = microtime(true);
		$exec_time = number_format($time_post - $time_pre, 1);
	    echo "Bulk record (" . count($data) . ") create: $exec_time seconds<br>";
		
		// Add to the DB in chunks
		$time_pre = microtime(true);
		$dataChunks = array_chunk($data, 100);
		foreach ($dataChunks as $d) {
			DB::table('data')->insert($d);
		}
		$time_post = microtime(true);
		$exec_time = number_format($time_post - $time_pre, 1);
	    echo "Insert " . count($data) . ": $exec_time seconds<br>";

		
		
		// Redirect
	    // return redirect()->back();
    }



}
