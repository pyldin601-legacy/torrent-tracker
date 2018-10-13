<?php

global $link, $start_time;
$start_time = microtime(true);

session_start();
session_write_close();

$link = mysql_connect("localhost", "admin", "Razwi=D");



mysql_select_db("retracker", $link);
mysql_query("SET names 'utf8'");
$script_name = basename($_SERVER['SCRIPT_NAME']);

if(($script_name != "sortbot.php") && ($script_name != "update.php") && ($script_name != "seed.php")) 
	mysql_query("INSERT INTO access_log (ip, query, time) VALUES ('" . $_SERVER['REMOTE_ADDR'] . "','".mysql_real_escape_string($_SERVER['REQUEST_URI'])."','".time()."')");

	
date_default_timezone_set('Europe/Kiev');

global $orders;
$orders = array('date'  => 'ORDER BY a.discovered ', 
				'name'  => 'ORDER BY a.info_text ',
				'downloads'  => 'ORDER BY a.downloads ',
				'seeds' => 'ORDER BY a.seeders ',
				'size'  => 'ORDER BY a.size '
				);

global $headers;
$headers = array(	'date' => 'Дата', 
					'name' => 'Название', 
					'downloads' => 'Загрузки', 
					'seeds' => 'Сиды/Личи', 
					'size' => 'Размер'
				);

global $sort_mode, $sort_dir;
$sort_mode = array('date' => 'по дате добавления', 'name' => 'по названию', 'downloads' => 'по загрузкам', 'seeds' => 'по сидам', 'size' => 'по размеру');
$sort_dir = array('1' => 'по возрастанию', '0' => 'по убыванию');

global $modes;
$modes = array('nm' => 'по названию', 'hash' => 'по info_hash', 'fn' => 'по имени файла', 'ds' => 'по описанию', 'ip' => 'по ip');

global $category_hash;
$category_hash = array_combine(
	explode("|", "all|audio|video|image|iso|soft"), 
	explode("|", "Все|Аудио|Видео|Изображения|Образы|Софт")
);

global $types;
$types = array(
	'audio' 	=> array('mp3', 'ogg', 'm4a', 'tta', 'ape', 'wma', 'flac', 'wav'),
	'video' 	=> array('avi', 'mkv', 'flv', 'mp4', 'vob', 'wmv', 'mpeg', 'mpg', '3gp'),
	'image' 	=> array('jpg', 'jpeg', 'png', 'bmp', 'gif', 'tif'),
	'archive'	=> array('rar', 'zip', '7z', 'tar', 'gz'),
	'iso'     	=> array('iso', 'isz', 'mds', 'mdf', 'nrg', 'bin', 'cue'),
	'playlist' 	=> array('m3u', 'pls')
);

function lc($str) {
	$str = mb_strtolower($str, 'UTF-8');
	//$str = strtr($str, 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЬЫЪЭЮЯІЇЄ', 'абвгдеёжзийклмнопрстуфхцчшщьыъэюяіїє');
	return $str;
}

function is_local() {
	$cli = $_SERVER["REMOTE_ADDR"];

	$nets = array('192.168.', '10.');
	foreach($nets as &$net) $net = preg_quote($net);

	$nets_exp = implode($nets, "|");
	return preg_match("#${nets_exp}#", $cli);
}

function file_icon($file) {
	global $types;
	$pwd = $_SERVER['DOCUMENT_ROOT'];
	error_log($pwd);
	$ext = strtolower(substr($file, strrpos($file, ".") + 1));
	foreach($types as $key => $value) {
		foreach($value as $subvalue) {
			if($subvalue == $ext) {
				return "<img src='/images/${key}.png'>";
			}
		}
	}
	return "<img src='/images/file.png'>";
}

function is_admin() {
	$adm = array('192.168.1.1', '192.168.1.20');
	foreach($adm as $host) {
		if($host == $_SERVER['REMOTE_ADDR']) return true;
	}
	return false;
}


function mydate($time) {
	$datesn = date("dmy", time());
	$rmonth = array("янв","фев","мар","апр","мая","июн","июл","авг","сен","окт","ноя","дек");
	$tdate = getdate($time);
	if(date("dmy", $time) == $datesn)
		return sprintf("сегодня, %02d:%02d", $tdate['hours'], $tdate['minutes']);
	else
		return sprintf("%d %s %d, %02d:%02d", $tdate['mday'], $rmonth[$tdate['mon']-1], $tdate['year']%100, $tdate['hours'], $tdate['minutes']);
}

function r_get($path) {
	$files_html = "<DIV CLASS='filelist'>";
	foreach($path as $key => $file) {
		if(is_array($file)) {
			$files_html .= "<img src='folder.png'>$key<br><div style='margin-left:10px'>" . r_get($file) . "</div>";
		}
	}
	foreach($path as $key => $file) {
		if(!is_array($file)) {
			list($fn, $sz) = explode("{%sep}", $key);
			$files_html .= "<DIV style='border-bottom:1px dotted grey'><DIV class='ar'>" . LRFS($sz) . "</DIV><DIV>".file_icon($fn)."$fn</DIV></DIV>";
		}
	}
	$files_html .= "</DIV>";
	return $files_html;
}

function scr($in) {
	$in = str_replace("'", "&#39;", $in);
	
	return $in;
}

function goo($in) {
	$in = str_replace("'", "&#39;", $in);
	$in = str_replace("_", " ", $in);
	$in = str_replace(".", " ", $in);
	return $in;
}

function HRFS($size) {
 
    $mod = 1024;
 
    $units = explode(' ','Б КБ МБ ГБ ТБ ПБ ЭБ ЗБ ЙБ');
    for ($i = 0; $size > $mod; $i++) {
        $size /= $mod;
    }
 
    return sprintf("%1.2f", $size) . ' ' . $units[$i];
}

function LRFS($size) {
 
    $mod = 1024;
 
    $units = explode(' ','Б КБ МБ ГБ ТБ ПБ ЭБ ЗБ ЙБ');
    for ($i = 0; $size > $mod; $i++) {
        $size /= $mod;
    }
 
    return round($size, 2) . ' ' . $units[$i];
}


function HRFS2($size) {
 
	$fullsize = $size;
    $mod = 1024;
 
    $units = explode(' ','Байт КБайт МБайт ГБайт ТБайт ПБайт ЭБайт ЗБайт ЙБайт');
    for ($i = 0; $size > $mod; $i++) {
        $size /= $mod;
    }
 
    return sprintf("%1.2f", $size) . ' ' . $units[$i] . " (" . number_format($fullsize, 0, '.', ' ' ) . " байт)";
}


function norm($val) {
	return number_format($val, 0, ".", " ");
}

function urlreplace($values) {
	$temp = array_merge($_GET, $values);
	$url = array();
	foreach($temp as $key => $value) {
		if(!$value) continue;
		if(($key == 'page') && ($value == 1)) continue;
		if(($key == 'order') && ($value == 'date')) continue;
		if(($key == 'category') && ($value == 'all')) continue;
		array_push($url, $key . '=' . $value);
	}
	return '?' . implode('&', $url);
}

function bdecode($torrent) { 
   $i = 0; 
   return bdecode_element($torrent, $i); 
} 

function bdecode_element($s, &$i) { 
   switch($ch = substr($s, $i++, 1)) { 
   case 'd': 
      $out = array(); 
      while(substr($s, $i, 1) != 'e') { 
		 //trigger_error ( $s , E_USER_NOTICE );
         $key = bdecode_element($s, $i); 
         $out[$key] = bdecode_element($s, $i); 
      } 
      $i++; 
      return $out; 
       
   case 'l': 
      $out = array(); 
      while(substr($s, $i, 1) != 'e') 
         $out[] = bdecode_element($s, $i); 
      $i++; 
      return $out; 
       
   case 'i': 
      $out = ''; 
      while (($ch = substr($s, $i++, 1)) != 'e') { 
         if(!(ctype_digit($ch) || $ch == '-')) 
            trigger_error("non-digit in integer value: '". 
               $ch."'", E_USER_ERROR); 
         $out .= $ch; 
      } 
      return intval($out); 
       
   default: if (ctype_digit($ch)) { 
         $len = $ch; 
         while (($ch = substr($s, $i++, 1)) != ':') { 
            if(!ctype_digit($ch)) 
               trigger_error("non-digit in string length: '". 
                  $ch."'", E_USER_ERROR); 
            $len .= $ch; 
         } 
         $len = intval($len); 
         $out = substr($s, $i, $len); 
         $i += $len; 
         return $out; 
       
      } else {
         trigger_error("unknown bencoded data type: '". substr($s, $i, 1)."'", E_USER_ERROR); 
		 //return '';
		}
   } 
} 


function bencode($element) { 
   $out = ""; 
   if (is_int($element)) { 
      $out = 'i'.$element.'e'; 
   } else if (is_string($element)) { 
      $out = strlen($element).':'.$element; 
   } else if (is_array($element)) { 
      ksort($element); 
      if (is_string(key($element))) { 
         $out ='d'; 
         foreach($element as $key => $val) 
            $out .= bencode($key).bencode($val); 
         $out .= 'e'; 
      } else { 
         $out ='l'; 
         foreach($element as $val) 
            $out .= bencode($val); 
         $out .= 'e'; 
      } 
   } else { 
      trigger_error("unknown element type: '". 
         gettype($element)."'", E_USER_ERROR); 
      exit(); 
   } 
   return $out; 
}

/*
function hex2bin($h)
  {
    if (!is_string($h)) return null;
    $r='';
    for ($a=0; $a<strlen($h); $a+=2) { 
        $r.=chr(hexdec($h{$a}.$h{($a+1)})); 
     }
    return $r;
  }
*/
 
function ago($seconds) {
	if($seconds < 60)
		return $seconds . ' сек.';
	elseif($seconds < 3600)
		return floor($seconds / 60) . ' мин.';
	elseif($seconds < 86400)
		return floor($seconds / 3600) . ' час.';
	else
		return floor($seconds / 86400) . ' дн.';
}

function get_torrent_peers($hash) {
	global $link;
	$seed_num = mysql_num_rows(mysql_query("SELECT * FROM tracker WHERE ileft=0 AND info_hash = '$hash'"));
	$leech_num = mysql_num_rows(mysql_query("SELECT * FROM tracker WHERE ileft>0 AND info_hash = '$hash'"));
	return array($seed_num, $leech_num);
}

function update_torrent_peers($hash) {
	global $link;
	$seed_num = mysql_num_rows(mysql_query("SELECT * FROM tracker WHERE ileft=0 AND info_hash = '$hash'"));
	$leech_num = mysql_num_rows(mysql_query("SELECT * FROM tracker WHERE ileft>0 AND info_hash = '$hash'"));
	mysql_query("UPDATE description SET seeders = '$seed_num', leechers = '$leech_num' WHERE info_hash = '$hash'");
	return true;
}

function update_all_torrent_peers() {
	return true;
}

function torrent_path($hash) {
	global $conf;
	return $conf['torrents_path'] . substr($hash, 0, 1) . "/" . substr($hash, 0, 2) . "/" . $hash . ".torrent";
}

function cover_path($hash, $prefix = '') {
	global $conf;
	return $conf['covers_path'] . substr($hash, 0, 1) . "/" . substr($hash, 0, 2) . "/" . $prefix . $hash . ".png";
}

function url_exists($url) {
    if (!$fp = curl_init($url)) return false;
    return true;
}

/*
function getimagesizefromstring($string) {
	$rand = '/tmp/' . md5(rand(0,1000000));
	file_put_contents($rand, $string);
	$size = getimagesize($rand);
	unlink($rand);
	return $size;
}
*/

function is_hash($str) {
	$hashval = "/^[0-9a-fA-F]{40}$/";
	return preg_match($hashval, $str);
}

function is_ipaddr($ip) {
	$ipval = "/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/";
	return preg_match($ipval, $ip);
}


?>