<?php

global $conf;
$conf['torrent_life_time'] 		= 60 * 60 * 24 * 7;		// 1w
$conf['torrent_inact_delay'] 	= 60 * 60 * 24;			// 1 day
$conf['cron_delay'] 			= 60; 					// 1 minute
$conf['seed_life_time']			= 60 * 10;				// 10 minutes

$conf['stop_words'] 			= explode(" ", "порнуха pornuha xxx erotic erotica porno porn порно секс seks sex эротика erotika analnoe analnogo анальное анальные pornolab porn4all porno4");
	
// layout
$conf['items_per_page'] 		= 100;

$conf['torrents_path'] 			= "/usr/domains/tracker.tedirens.com/data/torrents/";
$conf['covers_path']			= "/usr/domains/tracker.tedirens.com/data/covers/";



?>