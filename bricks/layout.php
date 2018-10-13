<?php
function show_header($title = 'Микро битторрент трекер StarNET') {
global $get_category;
echo "<!doctype html>\n";
echo "<HTML><HEAD>";
echo "<META HTTP-EQUIV='CONTENT-TYPE' CONTENT='text/html; charset=UTF-8'>";
echo "<TITLE>${title}</TITLE>";
echo "<link href='favicon.ico' type='image/x-icon' rel='icon' />";
echo "<link href='favicon.ico' type='image/x-icon' rel='shortcut icon' />";
//echo "<link rel='alternate' type='application/rss+xml' title='RSS' href='rss.php?category=$get_category'>";
echo "<script src='js/jquery-1.7.1.min.js'></script>";
echo "<script src='js/retracker.js'></script>";
echo "<link href='style.css' rel='stylesheet' type='text/css'>";
echo "<link href='footer.css' rel='stylesheet' type='text/css'>";
echo "<link href='comments.css' rel='stylesheet' type='text/css'>";
echo "</HEAD><BODY>";
//echo "<DIV class='clogin'>";
//echo "<DIV class='ar'>"; login_bar(); echo "</DIV>";
//echo "</DIV>";
//echo "<img src='images/logo.png'><br>";
}
function show_footer() {
echo "</BODY></HTML>";
}
function show_stats() {
	global $link, $start_time;
	$info_torrents_count = norm(mysql_num_rows(mysql_query("SELECT * FROM tracker WHERE 1")));
	$info_peers_count = norm(mysql_num_rows(mysql_query("SELECT * FROM tracker GROUP BY ip")));
	$info_notunique_count = norm(mysql_num_rows(mysql_query("SELECT * FROM tracker GROUP BY info_hash HAVING COUNT(*) > 1")));
	if(empty($_SESSION['descripted']) or $_SESSION['descripted'] == 0) {
		list($info_saved, $info_sumsize) = mysql_fetch_array(mysql_query("SELECT COUNT(*), SUM(size) FROM description WHERE 1"));
		list($info_getsize) = mysql_fetch_array(mysql_query("SELECT SUM(size) FROM description WHERE seeders > 0"));
	} else {
		list($info_saved, $info_sumsize) = mysql_fetch_array(mysql_query("SELECT COUNT(*), SUM(size) FROM description WHERE info_description != ''"));
		list($info_getsize) = mysql_fetch_array(mysql_query("SELECT SUM(size) FROM description WHERE seeders > 0 AND info_description != ''"));
	}

	$info_tor_cache = HRFS(mysql_result(mysql_query("SELECT value FROM etc WHERE setting = 'torrents_cache'"), 0, 0));
	$info_img_cache = HRFS(mysql_result(mysql_query("SELECT value FROM etc WHERE setting = 'images_cache'"), 0, 0));
	

	$info_saved = norm($info_saved);
	$info_db_size = HRFS(mysql_result(mysql_query("SELECT SUM(DATA_LENGTH) FROM information_schema.TABLES WHERE TABLE_SCHEMA = 'retracker';"), 0, 0));
	$info_sum_size = HRFS($info_sumsize);
	$info_get_size = HRFS($info_getsize);
	$info_cent_size = round(100 / $info_sumsize * $info_getsize) . '%';

	//	Запросов всего: <b>$info_requests</b>
	// <b>Внимание!</b> Сайт не распространяет и не хранит электронные версии произведений, а лишь предоставляет доступ<br>к создаваемому автоматически каталогу ссылок на торрент-файлы, которые содержат только списки хеш-сумм.


	echo "<div class='stats'>
	<h1>Статистика трекера</h1>
	Активных раздач: <b>$info_torrents_count</b>
	Уникальных ip: <b>$info_peers_count</b>
	Совпадающих раздач: <b>$info_notunique_count</b>
	<br>
	Общий объем файлов: <b>$info_sum_size</b>
	Объем доступных файлов: <b>$info_get_size ($info_cent_size)</b>
	<br>
	Торрентов в базе данных: <b>$info_saved</b>
	Размер базы данных: <b>$info_db_size</b>
	<br>
	Объем кэша торрент-файлов: <b>$info_tor_cache</b>
	Объем кэша обложек: <b>$info_img_cache</b>
	</div>";

	$time_array = getdate(time());

	$day_line = time() - $time_array['seconds'] - ($time_array['minutes'] * 60) - ($time_array['hours'] * 3600);

	$req_total = (int) mysql_result(mysql_query("SELECT COUNT(*) FROM access_log WHERE 1"), 0, 0);
	$req_today = (int) mysql_result(mysql_query("SELECT COUNT(*) FROM access_log WHERE time > $day_line"), 0, 0);
	$ips_today = (int) mysql_num_rows(mysql_query("SELECT ip FROM access_log WHERE time > $day_line GROUP BY ip"));

	$delta_time = round(microtime(true) - $start_time, 4);
	echo "<br><center><span class='vis round shadow'>[ $req_total / $req_today / $ips_today ], Время генерации: ${delta_time} мс.</span><br><br>Copyright (C) 2012 by roman_gemini</center><br>";

}

function show_query() {
	global $category_hash, $modes;

	$m = isset($_GET['m'], $modes[$_GET['m']]) ? $_GET['m'] : 'name';
	$q = isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_QUOTES) : '';
	$get_category = isset($_GET['category'], $category_hash[$_GET['category']]) ? $_GET['category'] : 'all';

	$query = "<form action='.' method='get'>Поиск: <input type='hidden' name='m' value='nm'><input type='hidden' name='category' value='${get_category}'><input required style='width:250px;' name='q' value=''>";
	$query .= "<input type='submit' value='Искать'></form>";

	return $query;
}

function show_query_2() {
	global $category_hash, $modes, $sort_mode, $sort_dir;

	$m = isset($_GET['m'], $modes[$_GET['m']]) ? $_GET['m'] : 'name';
	$q = isset($_GET['q']) ? htmlspecialchars($_GET['q'], ENT_QUOTES) : '';
	$hd = (isset($_GET['hd']) && $_GET['hd'] == 1) ? true : false;
	$get_category = isset($_GET['category'], $category_hash[$_GET['category']]) ? $_GET['category'] : 'all';
	$get_sort_mode = isset($_GET['order'], $sort_mode[$_GET['order']]) ? $_GET['order'] : 'date';
	$get_sort_dir = (isset($_GET['dir']) && $_GET['dir'] == '1') ? 1 : 0;

	$query = "<div class='subframe'><fieldset><legend>&nbsp;Расширенный поиск&nbsp;</legend>";
	$query .= "<form action='.' method='get'>";
	$query .= "<table>";
	$query .= "<tr><td>Поиск:</td><td><input required style='width:440px;' name='q' value='${q}'>";

	$query .= "<select name='m'>";
	foreach($modes as $key => $mode) {
		if($key == 'ip' && !is_admin()) continue;
		$ss = ($key == $m) ? "selected='selected'" : "";
		$query .= "<option ${ss} value='${key}'>&nbsp;${mode}</option>";
	}
	$query .= "</select></td></tr>";
	
	$query .= "<tr><td>Категория:</td><td>";
	$query .= "<select name='category'>";
	foreach($category_hash as $key => $value) {
		$ss = ($key == $get_category) ? "selected='selected'" : "";
		$query .= "<option ${ss} value='${key}'>${value}</option>";
	}
	$query .= "</select>  <label><input type='checkbox' name='hd' value='1' " . ($hd ? "checked" : "") . "> с полным описанием</label></td></tr>";

	$query .= "<tr><td>Упорядочить:</td><td><div class='ar'><input type='submit' value='Очень искать'></div>";
	$query .= "<select name='order'>";
	foreach($sort_mode as $key => $value) {
		$ss = ($key == $get_sort_mode) ? "selected='selected'" : "";
		$query .= "<option ${ss} value='${key}'>${value}</option>";
	}
	$query .= "</select>";

	$query .= " порядок <select name='dir'>";
	foreach($sort_dir as $key => $value) {
		$ss = ($key == $get_sort_dir) ? "selected='selected'" : "";
		$query .= "<option ${ss} value='${key}'>${value}</option>";
	}
	$query .= "</select>";
	
	$query .= "</td></tr>";
	$query .= "</table></form></fieldset></div>";

	return $query;
}

function login_bar() {
	global $user, $link;
	if(isset($_SESSION['login'], $_SESSION['password'])) {
		$sqlogin = mysql_real_escape_string($_SESSION['login']);
		$sqpassw = mysql_real_escape_string($_SESSION['passw']);
		$result = mysql_query("SELECT * FROM users WHERE ulogin = '$sqlogin' and upassw = '$sqpassw' LIMIT 1");
		if(mysql_num_rows($result) == 1) {
		} 
	}
	echo "Вы вошли как <strong>Гость</strong>, [ <a href='login.php'>Авторизоваться</a> ]";
}

?>