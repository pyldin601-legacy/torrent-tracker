<?php

include 'config.php';
include 'bricks/functions.php';
include 'bricks/layout.php';
include 'bricks/bbcode.php';
include 'comments.php';

$ip = $_SERVER['REMOTE_ADDR'];
$id = isset($_GET['id']) ? mysql_real_escape_string($_GET['id']) : null;

//update_all_torrent_peers();

$result = mysql_query("SELECT * FROM description WHERE id = '$id' LIMIT 1");
if(mysql_num_rows($result) == 1) $row = mysql_fetch_assoc($result);



$category_array = array();
foreach($category_hash as $key => $value) {
	$sl = ($key == $row['category']) ? 'sel' : 'unsel';
	array_push($category_array, "<span class='$sl round'><a href='.?category=$key'>$value</a></span>");
}

array_push($category_array, "<span class='unsel round'><a href='settings.php'>Настройки</a></span>");
$category = implode(" | ", $category_array);

show_header();

echo "<table class='toptable'><tr><td style='text-align:left; vertical-align:middle;'>$category</td><td style='text-align:right;'>".show_query()."</td></tr></table>";
echo "<div class='topic'>";

if(isset($row) and file_exists(torrent_path($row['info_hash']))) {


	$tname = bdecode(file_get_contents(torrent_path($row['info_hash'])));
	$fcount = count(bdecode($row['filelist']));
	
	echo "<div class='round blue'>"; 
		echo "<img style='padding-right:5px' align='left' src='images/download_torrent.png'>";
		echo "<div class='hi'><nobr>";
			echo "<a title='Скачать торрент' href='get.php?id=" . $row['id'] . "'>Скачать \"" . $tname['info']['name'] . ".torrent\"</a>";
		echo "</nobr></div>";
		echo "info_hash: " . $row['info_hash'];
	echo "</div>";


	if(strlen($row['info_description']) > 0) {
		echo "<div class='info'>";
			echo "<table><tr>";
			if((strlen($row['info_cover_url']) > 0))
				echo "<td><a target='_blank' href='cover.php?h=${row['info_hash']}&big=1'><img src='cover.php?h=${row['info_hash']}' width='200px'></a></td>";
			echo "<td style='text-align:left'>" . bbcode($row['info_description'], 'html') . "</td>";

			echo "</tr></table>";
		echo "</div>";
	}
	echo "<br>";
	
//	echo "<div class='title'>Детали торрент-файла</div>";
	echo "<table class='result' style='width:700px;position:relative;left:50%;margin-left:-350px'>";
	echo "<colgroup><col style='width:150px;'/><col style='width:auto;'/></colgroup>";
	echo "<thead class='double'><tr class='head'><th colspan='2'>Детали торрент-файла</th></tr></thead>";
	echo "<tbody>";

	echo "<tr>";
		echo "<td>Файл</td>";
		echo "<td>";
			echo "<div style='float:right'>";
				if(is_admin()) echo "<a title='Редактировать описание' href='edit.php?id=" . $row['id'] . "'><img src='images/iconEdit.png'></a>";
				echo " <a title='Скачать торрент' href='get.php?id=" . $row['id'] . "'><img src='download.png'></a>";
				echo " <a title='Magnet-ссылка' target='_blank' href='magnet:?xt=urn:btih:".$row['info_hash']."&dn=".scr($row['info_text'])."&tr=http%3a//retracker.local/announce'><img src='magnet.png'></a>";
				//echo " <a title='Скачать торрент с torrage.com' href='http://torrage.com/torrent/".strtoupper($row['info_hash']).".torrent'><img src='http://torrage.com/favicon.ico'></a>";
			echo "</div>";
		echo " <a title='Скачать торрент' href='get.php?id=" . $row['id'] . "'>".$tname['info']['name'].".torrent</a>";
		echo "</td>";
	echo "</tr>";

	echo "<tr><td>Название</td><td><div class='ar'><a target='_blank' href='http://www.google.com/search?q=".goo($row['info_text'])."'><img title='Искать описание в Google' src='google.png'></a></div>${row['info_text']}</td></tr>";
	echo "<tr><td>Хэш раздачи</td><td><div class='ar'><a target='_blank' href='http://www.google.com/search?q=".scr($row['info_hash'])."'><img title='Искать описание в Google' src='google.png'></a></div>${row['info_hash']}</td></tr>";
	echo "<tr><td>Категория</td><td><a href='.?category=${row['category']}'>" . $category_hash[$row['category']] . "</a></td></tr>";
	echo "<tr><td>Размер</td><td>" . HRFS2($row['size']) . "</td></tr>";
	echo "<tr><td>Размер торрент-файла</td><td>" . HRFS2(filesize(torrent_path($row['info_hash']))) . "</td></tr>";

	echo "<tr><td>Скачано раз</td><td>${row['downloads']}</td></tr>";
	echo "<tr><td>Раздающие</td><td>${row['seeders']} + DHT</td></tr>";
	echo "<tr><td>Качающие</td><td>${row['leechers']}</td></tr>";
	echo "<tr><td>Опубликовано</td><td>" . mydate($row['discovered']) . "</td></tr>";
	echo "<tr><td>Сид замечен</td><td>" . mydate($row['time']) . "</td></tr>";
	if(isset($tname['comment'])) {
		echo "<tr><td>Комментарий</td><td>" . $tname['comment'] . "</td></tr>";
	}
	echo "<tr><td>Файлов в раздаче</td><td>" . norm($fcount) . "</td></tr>";
	echo "<tr><td>Содержимое раздачи:</td><td>";
	echo "<input type='button' onclick='showfiles(this, $id)' value='Показать'>";
	echo "</td></tr>";
	echo "<tr><td>Пиры</td>";
	echo "<td>";

	if(is_admin()) {
		$result = mysql_query("SELECT * FROM tracker WHERE info_hash = '${row['info_hash']}'  ORDER BY update_time DESC");
		while($rw = mysql_fetch_assoc($result)) {
			$cent = 100 - floor(100 / $row['size'] * $rw['ileft']);
			print "<b>" . $rw['ip'] . "</b> : " . $rw['port']." /${rw['peer_id']}/ (" . ago(time()-(int)$rw['update_time']) . ") has: ${cent}%<br>";
		}
	} else {
		print "Скрыто";
	}

	echo "</td></tr>";
	echo "</tbody>";
	echo "<tfoot><tr class='head'><th colspan='2'>${row['info_text']}</th></tr></tfoot>";
	echo "</table>";
	//get_comments_post($id);
} else {
	echo "<center>Торрент не зарегистрирован не трекере!</center>";
}

echo "</div>";

show_stats();

mysql_close($link);

show_footer();


?>

