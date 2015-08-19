<?php

		error_reporting(E_ALL);
		ini_set('display_errors', 1);

function tepln($f) {
    $time_pre = microtime(true);
	$result = $f();
	$time_post = microtime(true);
	$exec_time = number_format($time_post - $time_pre, 1);
	empty($_SERVER['SERVER_PROTOCOL']) ? print "($exec_time s) $result\n" : print "($exec_time s) $result<br>";
}

	// Get the last array from a single level array json file
	// Adapted from http://www.geekality.net/2011/05/28/php-tail-tackling-large-files/
	// Alternatives at http://stackoverflow.com/questions/15025875/what-is-the-best-way-in-php-to-read-last-lines-from-a-file
	 function lastJSONArray($filename) {
		
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

$last = lastJSONArray("/home/public/lar.dockthis.com/test.txt", 1);

echo "last array '$last'";

if ($last == '') echo " empty string";
else {
	echo "<br>json<br>";
	$json = json_decode(trim(trim($last, ',')));
	print_r($json[0]);
}

?>