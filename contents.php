<?php

include 'config.php';
include 'bricks/functions.php';
include 'tree.php';

$id = isset($_GET['id']) ? mysql_real_escape_string($_GET['id']) : NULL;

$result = mysql_query("SELECT * FROM description WHERE id = '$id' LIMIT 1");
if(mysql_num_rows($result) == 1) {

	$row = mysql_fetch_assoc($result);
	$files = bdecode($row['filelist']);
	usort($files, "cmp");

	$keys = array();

	foreach($files as $file) {
		$keys[$file['name'] . '{%sep}' . $file['length']] = $file['name'];
	}
	
	if($tree = explodeTree($keys, '/')) 
		print r_get($tree);
	else 
		print '<i>(нет данных)</i>';


}

mysql_close($link);

function cmp($a, $b) {
	return strcmp($a["name"], $b["name"]);
}

?>