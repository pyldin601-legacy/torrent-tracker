<?php
include 'config.php';
include 'bricks/functions.php';

if(! is_admin() ) exit;
if(! $id = (int) $_GET['id'] ) exit;

$result = mysql_query("SELECT * FROM description WHERE id = $id");

if(mysql_num_rows($result) == 1) {
	$row = mysql_fetch_assoc($result);
	$tfile = torrent_path($row['info_hash']);
	if(file_exists($tfile)) 
		$res = unlink($tfile);
		if($res == true) mysql_query("UPDATE description SET hide = 1 WHERE id = $id");
}

mysql_close($link);

?>