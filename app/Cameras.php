<?php
	
namespace App;

class Cameras {
	
	// private $base = '/var/www/iutah/public/img';
	private static $base = '/home/public/public/img/cameras';
	
	public static function sites() {
		$base = Cameras::$base;
		
		return array_diff(scandir($base), array('..', '.'));;
	}
	
	public static function update() {
		
		$base = Cameras::$base;
		
		$sites = array_diff(scandir($base), array('..', '.'));
		
		foreach ($sites as $site) {
			
			// Get specifics
			$url = file_get_contents("$base/$site/url");
			$start = intval(file_get_contents("$base/$site/seq"));
			$suffix = 'jpg';
			$tmp = "$base/$site/temp.$suffix";
			
			// Copy all images with sequence numbers >= the starting number
			try {
				while(copy("$url$start.$suffix", $tmp)) {
					$exif = exif_read_data($tmp);
					$when = strtotime($exif['DateTimeOriginal']);
					
					$year = date('Y', $when);
					$month = date('m', $when);
					$name = date('U', $when);
					
					// Create the directory (if needed)
					if (!is_dir("$base/$site/$year/$month")) {
						mkdir("$base/$site/$year/$month", 0777, true);
					}
					
					// Move the file
					rename($tmp, "$base/$site/$year/$month/$name.$suffix");
					
					$start++;
				}
			} catch(\Exception $ex) { 
				// Do nothing
			}
			
			// Update the file to the sequence number to start with next time
			file_put_contents("$base/$site/seq", $start);
		}
		
		return true;
	}
	
	public static function timestamps($sitecode) {

		$base = Cameras::$base;

		$array = [];
		    
	    // Create a list of all timestamps for a given site
	 	$years = array_diff(scandir("$base/$sitecode"), array('..', '.', 'seq', 'url'));
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