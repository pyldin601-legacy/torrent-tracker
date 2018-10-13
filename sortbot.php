<?php

include 'config.php';
include 'bricks/functions.php';

$time = time();

header("Content-type: text/plain; charset=utf-8");

print "Tracker cron processing script\n------------------------------\n\n";

$file_types = array(
	'audio' 	=> array('mp3', 'ogg', 'm4a', 'tta', 'ape', 'wma', 'flac', 'wav'),
	'video' 	=> array('avi', 'mkv', 'flv', 'mp4', 'vob', 'wmv', 'mpeg', 'mpg', '3gp', 'vob'),
	'image' 	=> array('jpg', 'jpeg', 'png', 'bmp', 'gif', 'tif'),
	'iso'     	=> array('iso', 'cso', 'isz', 'mds', 'mdf', 'nrg', 'bin', 'cue')
);

$cat_priority = array('iso', 'video', 'audio', 'image');

$stop_words = $conf['stop_words'];

printf("Loading stopword list... %d items\n\n", count($stop_words));


$news = 0;

if(isset($_GET['force']))
	$result = mysql_query("SELECT * FROM description WHERE 1");
else
	$result = mysql_query("SELECT * FROM description WHERE category = ''");

while($row = mysql_fetch_assoc($result)) {
	$extensions = array('audio' => 0, 'video' => 0, 'image' => 0, 'iso' => 0, 'soft' => 0);
	$files = bdecode($row['filelist']);
	foreach($files as $file) {
		$extensions[get_category(strtolower(substr($file['name'], strrpos($file['name'], '.') + 1)))] += $file['length'];
	}
	$prio = get_prioritized($extensions);
	$news ++;
	mysql_query("UPDATE description SET category = '" . $prio . "' WHERE info_hash = '${row['info_hash']}'");
}

printf("Categorizing new items... %d items\n", $news);

// Censor processing
if(isset($_GET['force']))
	$result = mysql_query("SELECT * FROM description WHERE 1");
else
	$result = mysql_query("SELECT * FROM description WHERE censor = 0");

$blocked = 0;

while($row = mysql_fetch_assoc($result)) {
	$uflag = false;
	$khack = preg_split("/[\s\_\-\.\,\&]+/", lc($row['info_text']));
	foreach($khack as $my_word) {
		foreach($stop_words as $stop_word) {
			if( $my_word == $stop_word ) {
				$uflag = true;
				break;
			}
		}
	}
	if($uflag == false) {
		mysql_query("UPDATE description SET censor = 1 WHERE info_hash = '${row['info_hash']}'");
	} else {
		$blocked ++;
		mysql_query("UPDATE description SET censor = 0 WHERE info_hash = '${row['info_hash']}'");
	}
}

echo "Adult contol: $blocked blocks\n";

if(isset($_GET['force'])) {
	print "\nTorrents cache size: ";
	$cache = files_in_dir($conf['torrents_path']);
	mysql_query("REPLACE INTO etc (setting, value) VALUE ('torrents_cache', '$cache')");
	print HRFS($cache);

	print "\nCovers cache size: ";
	$cache = files_in_dir($conf['covers_path']);
	mysql_query("REPLACE INTO etc (setting, value) VALUE ('images_cache', '$cache')");
	print HRFS($cache);
	
}

if(isset($_GET['force'])) {
	$result = mysql_query("SELECT * FROM description WHERE flag = 1");
	while($row = mysql_fetch_assoc($result)) {
		if(! file_exists(torrent_path($row['info_hash'])) )
			mysql_query("DELETE FROM description WHERE id = ${row['id']} LIMIT 1");
	}
}

print "Garbage collectior working... ";
mysql_query("UPDATE description SET flag = 0 WHERE $time - time > ${conf['torrent_life_time']} AND info_description = ''");
mysql_query("UPDATE description SET seeders = 0, leechers = 0 WHERE $time - time > ${conf['seed_life_time']}");
print "OK\n";
print "\nDone!\n";

mysql_close($link);

exit();

// FUNCTIONS

function files_in_dir($path) {

	if(substr($path, -1) == '/')
		$path = substr($path, 0, strlen($path) - 1);
		
	$result = scandir($path);
	$size = 0;

	foreach($result as $file) {
		if(is_dir($path . '/' . $file) && $file != '.' && $file != '..') {
			$size += files_in_dir($path . '/' . $file);
		} else {
			$size += filesize($path . '/' . $file);
		}
	}
	return $size;
}

function get_category($ext) {
	global $file_types;
	foreach($file_types as $key => $val) {
		foreach($val as $v) {
			if($v == $ext) return $key;
		}
	}
	return 'soft';
}

function get_prioritized($array) {
	array_multisort($array, SORT_DESC, SORT_NUMERIC);
	if(isset($array))
		return array_shift(array_keys($array));
	else
		return 'soft';
}

function get_prioritized2($array) {
	global $cat_priority;
	foreach($cat_priority as $cat) {
		if(isset($array[$cat]))
			return $cat;
	}
	return 'soft';
}

mysql_close();

?>