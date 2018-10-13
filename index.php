<?php

include 'config.php';
include 'bricks/functions.php';
include 'bricks/layout.php';
include 'bricks/bbcode.php';

$host = $_SERVER["HTTP_HOST"];
$ip = $_SERVER["REMOTE_ADDR"];
$mytime = time();

update_all_torrent_peers();

/* Query initial constants */
$pre_query = "SELECT a.* FROM description a WHERE";
$pre_cnt_query = "SELECT COUNT(*) FROM description a WHERE";

$target_0 = "a.censor = 1 AND a.hide = 0" . ((isset($_GET['hd']) && $_GET['hd'] == '1') ? " AND a.info_description != ''" : "");

if(isset($_SESSION['descripted']) and $_SESSION['descripted'] == 1) $target_0 .= " AND a.info_description != ''";
/* Query init end */

/* Query format section begin */
$q = isset($_GET['q']) ? $_GET['q'] : null;

if($q) {
	$words = words($q);
	$qs = strtolower(mysql_real_escape_string($words));
	$qs_tr = encodestring($qs);
	$m = isset($_GET['m'], $modes[$_GET['m']]) ? $_GET['m'] : 'nm';
	switch($m) {
		case 'nm' : { 
			if($qs_tr == $qs)
				$target = "AND a.info_text LIKE '%${qs}%'";	
			else 
				$target = "AND (a.info_text LIKE '%${qs}%' OR a.info_text LIKE '%${qs_tr}%')";
			break; 	
		}
		case 'hash' : {	$target = "AND info_hash = '${qs}'"; break; }
		case 'fn' : { if($qs_tr == $qs)	$target = "AND a.filelist LIKE '%${qs}%'"; else $target = "AND (a.filelist LIKE '%${qs}%' OR a.filelist LIKE '%${qs_tr}%')"; break; }
		case 'ds' : { if($qs_tr == $qs) $target = "AND (a.info_description LIKE '%${qs}%')"; else $target = "AND (a.info_description LIKE '%${qs}%' OR a.info_description LIKE '%${qs_tr}%')"; break; }
		case 'ip' : { 
			if(is_admin()) {
				$pre_query = "SELECT a.* FROM description a, tracker b WHERE";
				$pre_cnt_query = "SELECT COUNT(*) FROM description a, tracker b WHERE";
				$target = "AND a.info_hash = b.info_hash AND b.ip = '${qs}'";
			} else {
				$target = "AND 0";
			}
			break; 
		}
	}
} else {
	$target = '';
}
/* Query format section end */

/* sort order section */
$order = isset($_GET['order'], $orders[$_GET['order']]) ? $_GET['order'] : 'date';
$dir   = (isset($_GET['dir']) && ($_GET['dir'] == '1')) ? 1 : 0;
$sort = $orders[$order] . ($dir ? 'ASC' : 'DESC');
/* sort order section end */

/* category block begin */
$get_category = isset($_GET['category'], $category_hash[$_GET['category']]) ? $_GET['category'] : 'all';
$category_array = array();
foreach($category_hash as $key => $value) {
	$sl = ($key == $get_category) ? 'sel' : 'unsel';
	array_push($category_array, "<span class='${sl} round'><a href='".urlreplace(array('hd'=>'', 'm'=>'', 'order'=>'', 'q'=>'', 'page'=>'1', 'category'=>$key))."'>$value</a></span>");
}
array_push($category_array, "<span class='unsel round'><a href='settings.php'>Настройки</a></span>");
$category = implode(" | ", $category_array);
$where_category = ($get_category != 'all') ? " AND a.category = '$get_category'" : "";
/* category block end */

show_header();


$count_query_compiled = "${pre_cnt_query} ${target_0} ${target} ${where_category}";
$rs_count = mysql_result(mysql_query($count_query_compiled), 0, 0);

$pages = ceil($rs_count / $conf['items_per_page']);
$page = (isset($_GET['page']) && ((int)$_GET['page'] <= $pages) && ((int)$_GET['page'] > 0)) ? (int)$_GET['page'] : 1;
$marker = ($page - 1) * $conf['items_per_page'];
$suffix = "LIMIT $marker, " . $conf['items_per_page'];
$query_compiled = "${pre_query} ${target_0} ${target} ${where_category} ${sort} ${suffix}";

$result = mysql_query($query_compiled);

echo "<table class='toptable'><tr><td style='text-align:left; vertical-align:middle;'>$category</td><td style='text-align:right;'>".show_query()."</td></tr></table>";
if($q) {
	echo show_query_2();
}
echo "<div class='pages'>"; display_pages($pages, $page); echo "</div>";

echo "<table class='result'>";
echo "<colgroup><col style='width:120px;'/><col style='width:auto;'/><col style='width:85px;'/><col style='width:100px;'/><col style='width:80px;'/></colgroup>";

echo "<thead><tr class='head'>";
foreach($headers as $key => $value) {
	if($key==$order) {
		$value = (($dir == 0) ? '↓ ' : '↑ ') . $value;
		$ord = (1 - $dir); 
	} else {
		$ord = $dir;
	}
	echo "<th><a href='".urlreplace(array('order' => $key, 'dir' => $ord))."'>$value</a></th>";
}
echo "</tr></thead>";

$n = 0;
echo "<tbody>";

while($row = mysql_fetch_assoc($result)) {
	$n++;
	echo "<tr>";
	echo "<td style='text-align:center;'>" . mydate($row['discovered']) . "</td>";
	
	echo "<td>";
	echo "<div class='ar'>";
	if(is_admin()) {
		echo "<a title='Редактировать описание' href='edit.php?id=" . $row['id'] . "'><img src='images/iconEdit.png'></a>";
		echo " <a title='Удалить торрент и блокировать раздачу' href='' onclick='delete_torrent(${row['id']}); return false;'><img src='images/delete.png'></a>";
	}
	echo " <a target='_blank' href='magnet:?xt=urn:btih:".$row['info_hash']."&dn=".scr($row['info_text'])."&tr=http%3a//retracker.local/announce'><img title='Magnet-ссылка' src='magnet.png'></a>";
	echo " <a href='get.php?id=" . $row['id'] . "'><img title='Скачать торрент' src='download.png'></a>";
	echo "</div>";
	echo "<div class='link'>";

/*	if(strlen($row['info_description']) > 0 && strlen($row['info_cover_url']) > 0) {
		echo "<table>";
		echo "<tr><td><a href='show.php?id=".$row['id']."'><img src='cover.php?h=${row['info_hash']}' width='64px'></a></td><td><a href='show.php?id=".$row['id']."'><b>".$row['info_text']."</b></a><br><br>".bbcode($row['info_short'], 'html')."</td></tr>";
		echo "</table>";
	} else { */
		echo "<a target='_blank' href='http://www.google.com/search?q=".scr($row['info_text'])."'><img title='Искать описание в Google' src='google.png'></a> ";
		$dif = time() - (int)$row['time'];
		if($dif < $conf['torrent_inact_delay'])
			echo "<a href='show.php?id=".$row['id']."'>" . $row['info_text'] . "</a>";
		else
			echo "<a class='oldlnk' href='show.php?id=".$row['id']."'>" . $row['info_text'] . "</a> <span class='inact'>(небыло " . ago($dif) . ")</span>";
//	}
	
	echo "</div>";

	echo "</td>";
	echo "<td style='text-align:center;'>" . $row['downloads'] . "</td>";
	echo "<td style='text-align:center;'><span class='seed'>↑ " . $row['seeders'] . "</span> <span class='leech'>↓ " . $row['leechers'] . "</span></td>";
	echo "<td style='text-align:right;'>" . HRFS($row['size']) . "</td>";
	echo "</tr>";
}

echo "</tbody>";
echo "<tfoot><tr class='head'><th colspan='5'>Найдено раздач: <b>".norm($rs_count)."</b></th></tr></tfoot>";
echo "</table>";

echo "<div class='pages'>"; display_pages($pages, $page); echo "</div>";

show_stats();

show_footer();

mysql_close($link);

exit;

/* Functions */

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

	//if($pages <= 1) return 0;

	$pages_max = 16;
	$pages_nice = 14;

	if($page > 1) { 
		echo "<a class='button' href='".urlreplace(array('page'=>$page-1))."'>Назад</a> "; 
	} else {
		echo "<a class='pressed'>Назад</a> "; 
	}

	if($pages<=$pages_max) {
		for($i=1;$i<=$pages;$i++) {
			if($page==$i) 
				echo "<a class='pressed'>$i</a> ";
			else 
				echo "<a class='button' href='".urlreplace(array('page'=>$i))."'>$i</a> ";
		}
	} else {
		if($page==1) 
			echo "<a class='pressed'>1</a> ";
		else 
			echo "<a class='button' href='".urlreplace(array('page'=>1))."'>1</a> ";
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
			if($page == $i) { echo "<a class='pressed'>$i</a> "; }
			else { echo "<a class='button' href='".urlreplace(array('page'=>$i))."'>$i</a> "; }
		}
		if($max != $pages - 1) { echo "... "; }
		if($page == $pages) 
			echo "<a class='pressed'>$pages</a> ";
		else 
			echo "<a class='button' href='".urlreplace(array('page'=>$pages))."'>$pages</a> ";
	}
    if($page < $pages) { 
		echo "<a class='button' href='".urlreplace(array('page'=>$page+1))."'>Вперед</a>"; 
	}  else {
		echo "<a class='pressed'>Вперед</a>"; 
	}
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

