<?php
	
namespace App;

class Cameras {
	
	public static function sites() {
		$base = public_path() . '/img/cameras';
		
		return array_diff(scandir($base), array('..', '.'));;
	}
	
	// Updates depend on a sequential directory listing.
	// Currently (10/2015), the files are formatted like
	// Prefix_YYYY_MM_DD_HH_MM_SS.jpg and sorted oldest to newest
	// THIS DEPENDS ON THE DIRECTORY LISTIING BEING OLDEST TO NEWEST!!
	public static function update() {
		
		$base = public_path() . '/img/cameras';
		
		// Remove items from directory listing
		$sites = array_diff(scandir($base), array('..', '.', '.htaccess', 'lost+found'));
		
		foreach ($sites as $site) {
			
			// Get specifics
			$url = file_get_contents("$base/$site/url");
			$last = file_get_contents("$base/$site/last");
			$tmp = "$base/$site/temp.jpg";
			
			// HTML Directory Listing -> Array
			// download all elements in the array after
			// the last elelment we know of.
			
			// Get listing
			$html = file_get_contents($url);
			preg_match_all('/\/([^\/"]+\.jpg)/', $html, $matches);
			$imgs = $matches[1];
			if(!($li = array_search($last, $imgs))) $li = -1;
			$li++;
			
			
			
			// Copy all images added after the last known image
			$got = 0;
			for(;$li < count($imgs); $li++) {
				try {
					echo "have $li of " . count($imgs) . "<br>";
					
					// Set a new last image
					$last = $imgs[$li];
					copy("$url/$last", $tmp);
					
					// Read EXIF
					$exif = exif_read_data($tmp);
					$when = strtotime($exif['DateTimeOriginal']);
					
					// Configure path and name
					$year = date('Y', $when);
					$month = date('m', $when);
					$name = date('U', $when);
					
					// Create the directory (if needed)
					if (!is_dir("$base/$site/$year/$month")) {
						mkdir("$base/$site/$year/$month", 0777, true);
					}
					
					// Move the file
					rename($tmp, "$base/$site/$year/$month/$name.jpg");
					
					// Update the last file
					// This used to be after, but somethimes the script takes too long and errors.
					// Moved here to at least get credit for images downloaded...
					file_put_contents("$base/$site/last", $last);
					
					$got++;
				} catch(\Exception $ex) { 
					// Bad image... do nothing
				}
			}
			
		}
		
		echo "Got $got new images.";
		
		return true;
	}
	
	public static function timestamps($sitecode) {

		$base = public_path() . '/img/cameras';

		$array = [];
		    
	    // Create a list of all timestamps for a given site
	 	$years = array_diff(scandir("$base/$sitecode"), array('..', '.', 'last', 'url', 'temp.jpg'));
	 	foreach ($years as $year) {
		 	$months = array_diff(scandir("$base/$sitecode/$year"), array('..', '.'));
		 	foreach ($months as $month) {
			 	$files = array_diff(scandir("$base/$sitecode/$year/$month"), array('..', '.'));
			 	foreach ($files as $file) {
				 	$array[] = intval(rtrim($file, '.jpg'));
			 	}
		 	}
	 	}
	 	
	 	return $array;
	}
	
}