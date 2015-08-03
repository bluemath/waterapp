<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

	$site = 'LR_FB_BA';
	$prefix = 'http://data.iutahepscor.org/gamutphotos/USU_LR_FB_BA/LR_FranklinBasin_BA_';
	$start = 269;
	$suffix = '.jpg';
	
	$tmp = '/tmp/photo.jpg';
	
	while(copy("$prefix$start$suffix", $tmp)) {
		$exif = exif_read_data($tmp);
		$when = strtotime($exif['DateTimeOriginal']);
		$name = date('Y-m-d-Hi', $when);
		
		rename($tmp, "/var/www/iutah/public/img/$site/$name$suffix");
		
		$start++;
	}
	
?>