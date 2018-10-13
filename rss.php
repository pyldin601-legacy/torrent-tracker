<?php

//header("Content-type:text/xml; Charset=utf-8");

include 'config.php';
include 'bricks/functions.php';

echo '<?xml version="1.0" encoding="utf-8" ?>
<!DOCTYPE torrent PUBLIC "-//bitTorrent//DTD torrent 0.1//EN" "http://www.retracker.local/rss.f">
<rss version="2.0">
	<channel>
		<title>Микро битторрент трекер StarNET</title>
		<ttl>15</ttl>
		<link>http://www.retracker.local/</link>
		<description>Tracker RSS feed</description>
';
	
$result = mysql_query("SELECT * FROM description WHERE flag = 1 AND censor = 1 ORDER BY discovered DESC LIMIT 100");
while($row = mysql_fetch_assoc($result)) {

	echo "<item>";
	echo "<title><![CDATA[${row['info_text']}]]></title>";
	echo "<link>http://www.retracker.local/show.php?id=${row['id']}</link>";
	echo "<pubDate>".date("r", $row['discovered'])."</pubDate>";
	echo "<description><![CDATA[${row['description']}]]></description>";
	echo "<enclosure url='http://www.retracker.local/get.php?id=${row['id']}' length='${row['size']}' type='application/x-bittorrent' />";
	echo "<guid>${row['id']}</guid>";
	echo "<torrent xmlns='http://www.retracker.local/torrent.xml'><fileName><![CDATA[${row['info_hash']}.torrent]]></fileName><contentLength>${row['size']}</contentLength><infoHash>".strtoupper($row['info_hash'])."</infoHash></torrent>";
	echo "</item>";

}

echo '</channel></rss>';

mysql_close($link);

exit;

?>