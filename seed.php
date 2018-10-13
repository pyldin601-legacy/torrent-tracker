<?php

include 'config.php';
include 'bricks/functions.php';

header("Content-type: text/plain; charset=utf-8");

$cleanup = 10 * 60;

$link = mysql_connect("localhost", "root", "");

mysql_select_db("retracker", $link);
mysql_query("SET NAMES 'utf8'", $link);

set_time_limit(0);
$time = time();

// sync with server 1
$copy = mysql_query("SELECT * FROM tracker", $link);
while($row = mysql_fetch_array($copy)) {
	$hash = $row['info_hash'];
	$ar_hash{$hash} = array(0, 0);
	if($row['ileft'] > 0) $ar_hash{$hash}[1] ++; else $ar_hash{$hash}[0] ++;
}

foreach($ar_hash as $key => $value) {
	$req = "
		UPDATE 
			description 
		SET 
			seeders = ${value[0]},
			leechers = ${value[1]},
			time = $time,
			flag = 1 
		WHERE
			info_hash = '${key}'
	";
	//print $req . "\n";
	mysql_query($req, $link);
}

mysql_close($link);
?>