<?php

include 'config.php';
include 'bricks/functions.php';

$hash = isset($_GET['h']) ? mysql_real_escape_string($_GET['h']) : null;
$big = isset($_GET['big']) ? true : false;

if(!$hash) exit();

$row = mysql_fetch_assoc(mysql_query("SELECT * FROM description WHERE info_hash = '$hash'"));

mysql_close($link);

if(!$row) exit();

$cover_hash = cover_path($hash, 'thumb_');
$cover_hash_full = cover_path($hash, 'full_');

header('Content-Type: image/png');
header('Content-Disposition: filename="' . $hash . '.png"');

if($big ? file_exists($cover_hash_full) : file_exists($cover_hash)) {
 	echo file_get_contents($big ? $cover_hash_full : $cover_hash);
} else {
	if($stream = file_get_contents($row['info_cover_url'])){
		list($width, $height) = getimagesizefromstring($stream);
		$im = imagecreatefromstring($stream);

		if($width > 200) {
			$newheight = $height / $width * 200;
			$newwidth = 200;
		} else {
			$newheight = $height;
			$newwidth = $width;
		}

		if($width > 500) {
			$newheightt = $height / $width * 500;
			$newwidtht = 500;
		} else {
			$newheightt = $height;
			$newwidtht = $width;
		}

		$thumb = imagecreatetruecolor($newwidth, $newheight);
		$full = imagecreatetruecolor($newwidtht, $newheightt);

		$back = imagecolorallocatealpha($thumb, 255, 255, 255, 0);

		imagefilledrectangle($thumb, 0, 0, $newwidth, $newheight, $back);
		imagefilledrectangle($full, 0, 0, $newwidtht, $newheightt, $back);

		imagecopyresampled($thumb, $im, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
		imagecopyresampled($full, $im, 0, 0, 0, 0, $newwidtht, $newheightt, $width, $height);

		if(!is_dir(dirname($cover_hash))) mkdir(dirname($cover_hash), 0777, true);

		imagepng($thumb, $cover_hash);
		imagepng($full, $cover_hash_full);

		imagepng($big ? $full : $thumb, NULL);
		
		imagedestroy($thumb);	
		imagedestroy($full);	
	}
}


?>