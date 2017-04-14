<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use DB;
use Cache;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use Carbon\Carbon;

// The models
use App\Site;
use App\Series;
use App\Variable;
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

		// Only interested in GAMUT sites
		// ( Issues exist with USGS duplicates that break unique constraints if those sites are included )
		$siteCodeContains = ['RB_', 'PR_', 'LR_'];
		// Revised to focus on suffix
		$siteCodeContains = ['_BA', '_AA'];

		// Only interested in Stream data
		$siteTypes = ['Stream'];
		
		// Only interested in common variables
		$variableLevels = ['Common'];
		
		// Only interested in RAW data
		// Can't rely on QC data
		$qualityControlLevelCodes = [0];
		
		// Get sites
		$siteKeys = ['network' => '', 'sitecode' => '', 'sitename' => '', 'latitude' => '', 'longitude' => ''];
		$sitesJSON = file_get_contents("http://data.iutahepscor.org/tsa/api/v1/sites/?limit=0");
		$sites = json_decode($sitesJSON);
		
		// Process sites into database
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
						try {
							Site::firstOrCreate((array) $site);
							break;
						} catch (Exception $e) {
							// Didn't add to DB because of conflict, ignore...
						}
					}
				}
			}
		}
		
		// Get series
		$seriesKeys = ['sitecode' => '', 'variablecode' => '', 'getdataurl' => ''];
		$variableKeys = ['variablecode' => '', 'variablename' => '', 'variableunitsname' => '', 'variableunitsabbreviation' => ''];
		$seriesJSON = file_get_contents("http://data.iutahepscor.org/tsa/api/v1/dataseries/?limit=0");
		$series = json_decode($seriesJSON);
		
		// Process series into database
		foreach ($series->objects as $s) {
			// Only add series if site was added
			if(!Site::where(['sitecode' => $s->sitecode])->get()->isempty()) {
				// Only add desired variables
				if (in_array($s->variablelevel, $variableLevels) && in_array($s->qualitycontrollevelcode, $qualityControlLevelCodes)) {
					// Strip down arrays for insert
					$s = (array) $s;
					$v = array_intersect_key($s, $variableKeys);
					$s = array_intersect_key($s, $seriesKeys);
					// Add Series
					try {
						Series::firstOrCreate($s);
					} catch (Exception $e) {
						// Series already exists
					}
					// Add Variable
					try {
						Variable::firstOrCreate($v);
					} catch (Exception $e) {
						// Variable already exists, this will catch a lot!
					}
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
	    $series = DB::table('series')->where($where)->join('variables', function($join) {
		    	$join->on('series.variablecode', '=', 'variables.variablecode');
		    })->get();
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

	// Get the data file
	public function data(Request $request, $sitecode, $variablecode) {
		
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
		
		$filename = "$sitecode.$variablecode.json";
		$filepath = storage_path("data/$filename");
		
		// Try to create and update on first access
		if(!file_exists($filepath)) {
			$this->dataUpdate($sitecode, $variablecode, true);
		}
		
		// Check to see if that worked
		if(!file_exists($filepath)) {
			// Error
			$file = "[error: 'not a valid site+variable combination']";
		} else {
			// File JSON
			$file = "[" . file_get_contents($filepath) . "]";
		}
		 
	    // Convert to JSONP if necessary
	    if($request->has('callback')) {
		    $file = $request->input('callback') . "($file)";
	    }
	    
	    return response("$file")->header('Content-Type', 'application/json');
    }
	
	// Update the data file
	public function dataUpdate($sitecode, $variablecode, $silent = false) {
		
		error_reporting(E_ALL);
		ini_set('display_errors', 1);
		
		$filename = "$sitecode.$variablecode.json";
		$filepath = storage_path("data/$filename");
		
		// Get basic URL 
	    $where = ['sitecode' => $sitecode, 'variablecode' => $variablecode];
	    $series = Series::where($where)->first();
	    
	    if(is_null($series)) return;
	    
	    $getdataURL = $series->getdataurl;
	    
	    // Parse the URL
	    $parsedURL = parse_url($getdataURL);
	    
	    // Parse the query portion of the url
	    parse_str($parsedURL['query'], $query);
	    
	    // startDate: the most recent timestamp+1 or empty
	    $lastline = $this->lastJSONArray($filepath);
	    $lastTimestamp = 0;
	    if($lastline != '') {
		    $json = json_decode(trim($lastline, ','));
		    $lastTimestamp = Carbon::createFromTimeStamp($json[0], "MST");
		    // Account for 7 hour offset + 1 second so we don't get this record again
		    $lastTimestamp->addSecond();
		    $query['startDate'] = $lastTimestamp->format('Y-m-d\TH:i:s');
		    $trimcomma = false;
	    } else {
		    // This is an empty file. Plan to remove leading comma.
		    $this->tepln(function() {
			    return "File is empty";
			}, $silent);
		    $trimcomma = true;
	    }

/*
	    // endDate: now
		$query['endDate'] = Carbon::now()->setTimezone('UTC')->format('Y-m-dTh:i:s');
*/
	    
	    // Rebuild the URL
	    $parsedURL['query'] = http_build_query($query);
	    $url = $parsedURL['scheme'] . "://" . $parsedURL['host'] . $parsedURL['path'] . "?" . $parsedURL['query'];
	
		// Get the XML
		
		// Bug Note: 
		// The following calls caused the log explosion detected on 4/13/2017.
		// The logs indicated that "failed to open stream: HTTP request failed!"
		// Specifically, this is a url that was not working:
		    // http://data.iutahepscor.org/RedButteCreekWOF/REST/waterml_1_1.svc/datavalues?location=iutah:RB_RBG_BA&amp;variable=iutah:ODO_Sat/methodCode=60/sourceCode=1/qualityControlLevelCode=0&amp;startDate=2017-03-27T04:15:01&amp;endDate=
		// This was to update RB_RBG_BA's ODO_Sat series
		// The series hadn't updated successfully since 3/27/2017, 17 days.
		// I attemped a manual update by accessing this URL, which worked without issue:
		    // https://iutah.nhmu.utah.edu/sites/RB_RBG_BA/ODO_Sat/update
        // I then updated all other series for this site using the same method, clicking update at:
            // https://iutah.nhmu.utah.edu/sites/RB_RBG_BA
        // All these manual updates worked without issue.
        // When accessing some of the URLs from the logs directly, I would occasionally get this message from the iutahepscor.com server:
            // Error Status Code: 'InternalServerError'
            // Details: The server encountered an error processing the request. Please see the server logs for more details.
        // It seems that the iutahepscor.com server is erroring out on occasion
        // this is likely with specific date combinations, as I could change the date in the URL and it would work.
        // To resolve log generation, I suppressed errors with '@' prepended to simplexml_load_file call, and checked $xml for false state.
        // This will not resolve communication with iutahepscor.com, but that seems like a problem on the iutahepscor.com end.
		$this->tepln(function() use (&$xml, $url, $query) {
    		// The @ operator will prevent HTTP errors from being thrown,
    		// in the event of an HTTP error, the $xml variable will be false.
			$xml = @simplexml_load_file($url);
			if(!$xml) {
    			// Still push an error to the log, but make it a one liner:
    			Log::info("DataController: Failed to get $url");
    			return "failed to download XML.";
            } else {
    			return "downloaded XML with " . count($xml->timeSeries->values->value) . " values from '" . $query['startDate'] . "' until now (checking for HTTP error)";
			}
		}, $silent);
		
		if(!$xml) {
    	    // Didn't get XML, BAIL!
        } else {
    		// Process XML
    		$newdatastring = "";
    		$startDate = $query['startDate'];
    		$this->tepln(function() use (&$newdatastring, $xml, $lastTimestamp) {
    			// Bad data looks like
    			$noValue = $xml->timeSeries->variable->noDataValue;
    			
    			// Iterate through all data, ignoring bad values
    			foreach ($xml->timeSeries->values->value as $value) {
    				// Only add 'valid' values
    				if((string) $value != $noValue) {
    					$time = $value->attributes()->dateTimeUTC;
    					$time = Carbon::parse($time)->timestamp;
    					$value = (string) $value;
    					$newdatastring .= ",[$time,$value]";
    				}
    			}
    			return "processed new values";
    		}, $silent);
    
    		// Save processed data
    		$this->tepln(function() use ($filepath, $newdatastring, $trimcomma) {
    			if($trimcomma) $newdatastring = trim($newdatastring, ',');
    			file_put_contents($filepath, $newdatastring, FILE_APPEND | LOCK_EX);
    			return 'appended to existing data file';
    		}, $silent);
		}
	    
		// Redirect
		// Leaving this commented lets the updates link from the sites endpoint show results.
	    //return redirect()->back();
	    return;
	}
	
	// Time execution and print with new line
	private function tepln($f, $silent = false) {
	    $time_pre = microtime(true);
		$result = $f();
		$time_post = microtime(true);
		$exec_time = number_format($time_post - $time_pre, 1);
		if($silent) return;
		empty($_SERVER['SERVER_PROTOCOL']) ? print "($exec_time s) $result\n" : print "($exec_time s) $result<br>";
	}
	
	// Get the last array from a single level array json file
	// Adapted from http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
	// Alternatives at http://stackoverflow.com/questions/15025875/what-is-the-best-way-in-php-to-read-last-lines-from-a-file
	private function lastJSONArray($filename) {
		
		$arrays = 1;
		$buffer = 4096;
		
	    // Open the file (readable, in binary)
	    $f = fopen($filename, "a+b");
	
	    // Jump to last character
	    fseek($f, -1, SEEK_END);
	
	    // Read it and adjust line number if necessary
	    // (Otherwise the result would be wrong if file doesn't end with a blank line)
	    if(fread($f, 1) != "]") $arrays -= 1;
	
	    // Start reading
	    $output = '';
	    $chunk = '';
	
	    // While we would like more
	    while(ftell($f) > 0 && $arrays >= 0)
	    {
	        // Figure out how far back we should jump
	        $seek = min(ftell($f), $buffer);
	
	        // Do the jump (backwards, relative to where we are)
	        fseek($f, -$seek, SEEK_CUR);
	
	        // Read a chunk and prepend it to our output
	        $output = ($chunk = fread($f, $seek)).$output;
	
	        // Jump back to where we started reading (for the next read)
	        fseek($f, -mb_strlen($chunk, '8bit'), SEEK_CUR);
	
	        // Decrease our array counter
	        $arrays -= substr_count($chunk, "]");
	    }
	
	    // While we have too many lines
	    // (Because of buffer size we might have read too many)
	    while($arrays++ < 0)
	    {
	        // Find first newline and remove all text before that
	        $output = substr($output, strpos($output, "]") + 1);
	    }
	
	    // Close file and return
	    fclose($f); 
	    return $output; 
	}
}
