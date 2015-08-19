<?php

	error_reporting(E_ERROR);
	ini_set('display_errors', 1);

	$dto = "Aug 8, 2015, 2:00:10 PM";
	
	$when = strtotime($dto);
	$ts = date('U', $when);
	echo "$ts<br>";
	
	$when = strtotime($dto." MST");
	$ts = date('U', $when);
	echo "$ts<br>";

	$when = strtotime($dto." UTC");
	$ts = date('U', $when);
	echo $ts;
	
?>