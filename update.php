<?php

include 'config.php';
include 'bricks/functions.php';
include 'bricks/layout.php';


$host = $_SERVER["HTTP_HOST"];
$ip = $_SERVER["REMOTE_ADDR"];
$mytime = time();
$datesn = date("dmy", $mytime);

if(! $after = $_GET['after']) exit();


update_all_torrent_peers();

$lmtime = (int)mysql_result(mysql_query("SELECT COUNT(*) FROM description"), 0, 0);

$dt = $lmtime - $after;
$result = mysql_query("SELECT * FROM description ORDER BY discovered DESC LIMIT $dt");

echo "<table class='newitems'><tbody>";
while($row = mysql_fetch_assoc($result)) {
	if(($row['flag'] == '0') or ($row['censor'] == '0')) continue;
	$after ++;
	echo "<tr class='newtr'>";
	echo "<td style='text-align:center;'>" . mydate($row['discovered']) . "</td>";
	echo "<td><div class='explode' onclick='fileexpl(\"".$row['info_hash']."\");'></div>";
	echo "<div class='link'>";
	echo "<a target='_blank' href='http://www.google.com/search?q=".scr($row['info_text'])."'><img title='Искать описание в Google' src='google.png'></a>";
	echo "<a target='_blank' href='magnet:?xt=urn:btih:".$row['info_hash']."&dn=".scr($row['info_text'])."&tr=http%3a//retracker.local/announce'><img title='Magnet-ссылка' src='magnet.png'></a>";

	echo "<a href='get.php?h=" . $row['info_hash'] . "'><img title='Скачать торрент' src='download.png'>" . $row['info_text'] . "</a> <span class='inact'>(нов.)</span>";
		
	echo "</div>";
	echo "<div class='filecont' id='hash_".$row['info_hash']."'></div>";
	echo "</td>";
	echo "<td style='text-align:center;'>" . $row['downloads'] . "</td>";
	echo "<td style='text-align:center;'><span class='seed'>↑ " . $row['seeders'] . "</span> <span class='leech'>↓ " . $row['leechers'] . "</span></td>";
	echo "<td style='text-align:right;'>" . HRFS($row['size']) . "</td>";
	echo "</tr>";
}
echo "</tbody></table>";
echo "<div class='newtime'>$after</div>";
mysql_close($link);

exit;

/* Functions */

function mydate($time) {
	global $datesn;
	$rmonth = array("янв","фев","мар","апр","мая","июн","июл","авг","сен","окт","ноя","дек");
	$tdate = getdate($time);
	if(date("dmy", $time) == $datesn)
		return sprintf("сегодня, %02d:%02d", $tdate['hours'], $tdate['minutes']);
	else
		return sprintf("%d %s %d, %02d:%02d", $tdate['mday'], $rmonth[$tdate['mon']-1], $tdate['year']%100, $tdate['hours'], $tdate['minutes']);
}

function scr($in) {
	return str_replace("'", "&#39;", $in);
}

function encodestring($str) {
    $tr = array(
        "А"=>"A","Б"=>"B","В"=>"V","Г"=>"G",
        "Д"=>"D","Е"=>"E","Ж"=>"J","З"=>"Z","И"=>"I",
        "Й"=>"J","К"=>"K","Л"=>"L","М"=>"M","Н"=>"N",
        "О"=>"O","П"=>"P","Р"=>"R","С"=>"S","Т"=>"T",
        "У"=>"U","Ф"=>"F","Х"=>"H","Ц"=>"TS","Ч"=>"CH",
        "Ш"=>"SH","Щ"=>"SCH","Ъ"=>"","Ы"=>"Y","Ь"=>"'",
        "Э"=>"E","Ю"=>"YU","Я"=>"YA","а"=>"a","б"=>"b",
        "в"=>"v","г"=>"g","д"=>"d","е"=>"e","ж"=>"j",
        "з"=>"z","и"=>"i","й"=>"j","к"=>"k","л"=>"l",
        "м"=>"m","н"=>"n","о"=>"o","п"=>"p","р"=>"r",
        "с"=>"s","т"=>"t","у"=>"u","ф"=>"f","х"=>"h",
        "ц"=>"ts","ч"=>"ch","ш"=>"sh","щ"=>"sch","ъ"=>"y",
        "ы"=>"y","ь"=>"'","э"=>"e","ю"=>"yu","я"=>"ya"
    );
    return strtr($str,$tr);
}

function words($str) {
	$reps = array('_', ",", " ", "-");
	foreach($reps as $rep) {
		$str = str_replace($rep, '%', $str);
	}
	return $str;
}

function display_pages($pages, $page) {

	if($pages <= 1) return 0;

	$pages_max = 16;
	$pages_nice = 14;
	echo "Страницы: ";

	if($page > 1) { echo "<a href='".urlreplace(array('page'=>$page-1))."'>←</a> "; }
	if($pages<=$pages_max) {
		for($i=1;$i<=$pages;$i++) {
			if($page==$i) 
				echo "<a id='current'>$i</a> ";
			else 
				echo "<a href='".urlreplace(array('page'=>$i))."'>$i</a> ";
		}
	} else {
		if($page==1) 
			echo "<a id='current'>1</a> ";
		else 
			echo "<a href='".urlreplace(array('page'=>1))."'>1</a> ";
		$min = $page - (int)($pages_nice / 2);
		$max = $min + $pages_nice;
		if($min < 2) {
			$min = 2; 
			$max = $min + $pages_nice; 
		} elseif($max > $pages - 1) {
			$max = $pages - 1; 
			$min = $max - $pages_nice; 
		}

		if($min != 2) { echo "... "; }
		for($i=$min;$i<=$max;$i++) {
			if($page == $i) { echo "<a id='current'>$i</a> "; }
			else { echo "<a href='".urlreplace(array('page'=>$i))."'>$i</a> "; }
		}
		if($max != $pages - 1) { echo "... "; }
		if($page == $pages) 
			echo "<a id='current'>$pages</a>";
		else 
			echo "<a href='".urlreplace(array('page'=>$pages))."'>$pages</a> ";
	}
    if($page < $pages) { echo "<a href='".urlreplace(array('page'=>$page+1))."'>→</a>"; } 
}

function is_hash($str) {
	$hashval = "/^[0-9a-fA-F]{40}$/";
	return preg_match($hashval, $str);
}

function is_ipaddr($ip) {
	$ipval = "/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/";
	return preg_match($ipval, $ip);
}

function fetch_rows($query) {
	global $link;
	$rows = array();
	$result = mysql_query($query);
	if(! $result) return null;
	while($row = mysql_fetch_assoc($result)) {
		$rows[$row['info_hash']] = $row;
	}
	return $rows;
}

?>

