<?php

include 'config.php';
include 'bricks/functions.php';

if(!is_local()) {
	header("HTTP/1.0 403 Forbidden");
	echo "<html>";
	echo "<head>";
	echo "<title>403 Forbidden</title>";
	echo "<META HTTP-EQUIV='CONTENT-TYPE' CONTENT='text/html; charset=UTF-8'>";
	echo "</head>";
	echo "<body>";
	echo "<h1>403 Forbidden</h1>";
	echo "Скачивание торрентов доступно только внутри сети StarNET";
	echo "</body>";
	echo "</html>";
	mysql_close($link);
	exit;
}

$id = mysql_real_escape_string($_GET['id']);
$res = mysql_query("SELECT info_hash, info_text, discovered FROM description WHERE id = '$id' LIMIT 1");
$host = $_SERVER["HTTP_HOST"];

if(mysql_num_rows($res) == 1) {
	list($hash, $tname, $disc_date) = mysql_fetch_array($res);
	$lfile = torrent_path($hash);
}

if(isset($lfile) && file_exists($lfile)) {
	$torrent = bdecode(file_get_contents($lfile));
	//if($_SESSION['inet'] == 0) $torrent['private'] = 1;
	$torrent['announce-list'] = array(array("http://retracker.local/announce"));
	$torrent['announce'] = "http://retracker.local/announce";
	$torrent['publisher'] = "Micro Bittorrent Tracker";
	$torrent['comment'] = "http://www.retracker.local/show.php?id=" . $id;
	$torrent['created by'] = "aria2/1.14.1";
	$torrent['creation date'] = (int) $disc_date;
	$data = bencode($torrent);
	header( 'Content-type: application/x-bittorrent' );
	header( 'Content-Disposition: attachment; filename="' . $torrent['info']['name'] . '.torrent"' );
	echo $data;
	flush();
	mysql_query("UPDATE description SET downloads = downloads + 1 WHERE id = '$id'");
} else {
	header('HTTP/1.0 404 not found');  
}

mysql_close($link);

?>