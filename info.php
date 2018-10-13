<?php

include 'config.php';
include 'bricks/functions.php';
include 'tree.php';

$host = $_SERVER['HTTP_HOST'];
$cache = 'http://' . $host . '/retracker/cache';

header("Content-type:text/html; charset=utf-8");

$mytime = time();

$hash = isset($_GET['hash']) ? $_GET['hash'] : null;

if(!preg_match("/[0-9a-f]{40}/i", $hash)) die();

$result = mysql_query("SELECT * FROM description WHERE info_hash = '$hash' LIMIT 1");

$row = mysql_fetch_assoc($result);

preg_match_all("/\"(.+?)\"\;/", $row['files'], $files, PREG_PATTERN_ORDER);


$keys = array_combine(array_values($files[1]), array_values($files[1]));
$tree = explodeTree($keys, '/');

print "<div style='margin-left:4px; margin-bottom:4px;'><b>Хеш:</b> " . $hash . "</div>";
print "<div style='margin-left:4px; margin-bottom:4px;'><b>Файлы:</b></div>";
if($tree) {
	print r_get($tree);
} else {
	print '<i>(нет данных)</i>';
}

if($_SERVER['REMOTE_ADDR'] == '192.168.1.20') {
print "<div style='margin-left:4px; margin-bottom:4px;'><b>Пиры:</b></div>";
$result = mysql_query("SELECT ip,port FROM tracker WHERE info_hash = '$hash'");
print "<ul class='peerlist'>";
while($row = mysql_fetch_assoc($result)) {
	print "<li>".$row['ip'].":".$row['port']."</li>";
}
print "</ul>";
}

mysql_close($link);


?>