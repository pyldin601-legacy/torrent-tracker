<?php

include 'config.php';
include 'bricks/functions.php';
include 'bricks/layout.php';

$ip = $_SERVER['REMOTE_ADDR'];
if(preg_match("/192\.168\.1\./", $ip)) { $ip = '10.1.231.21'; }

if($_SERVER['REQUEST_METHOD'] == 'POST') {
	// session params
	
	session_start();
	$_SESSION['descripted'] = (isset($_POST['descripted']) && $_POST['descripted'] == '1') ? 1 : 0;
	$_SESSION['flag'] = (isset($_POST['flag']) && $_POST['flag'] == '1') ? 1 : 0;
	session_write_close();
	
	$deny = isset($_POST['deny']) ? (int)$_POST['deny'] : 0;
	if($deny)
		mysql_query("REPLACE INTO disable (ip) VALUES ('$ip')");
	else
		mysql_query("DELETE FROM disable WHERE ip = '$ip'");

}

$category_array = array();
foreach($category_hash as $key => $value)
	array_push($category_array, "<span class='unsel round'><a href='.?category=$key'>$value</a></span>");

array_push($category_array, "<span class='sel round'><a href='settings.php'>Настройки</a></span>");
$category = implode(" | ", $category_array);

show_header();

echo "<table class='toptable'><tr><td style='text-align:left; vertical-align:middle;'>$category</td><td style='text-align:right;'>".show_query()."</td></tr></table>";
echo "<div class='topic'>";
?>
<div class='title'>Опции</div>
<p>Тут можно произвести дополнительные настройки трекера:</p>
<form class="settings" method="post">
	<label><input type="checkbox" name="descripted" value="1" <?php if((int)$_SESSION['descripted'] == 1) echo "checked"; ?>> Отображать раздачи только с полным описанием</label><br>
	<label><input type="checkbox" name="flag" value="1" <?php if((int)$_SESSION['flag'] == 1) echo "checked"; ?>> Отображать просроченные раздачи</label><br>
	<label><input type="checkbox" name="deny" value="1" <?php if(mysql_num_rows(mysql_query("SELECT * FROM disable WHERE ip = '$ip' LIMIT 1")) == 1) echo "checked"; ?>> Отключтить доступ к аннонсеру <b>retracker.local</b> с этого IP (поиск локальных пиров будет недоступен)</label><br>
	<br>
	<input type="submit" value="Сохранить">
</form>
<p>Поисковая система: <a href='#' onclick='return addEngine();'>добавить трекер в поисковую строку браузера</a></p>
<?php
echo "</div>";

show_stats();

mysql_close($link);

show_footer();


?>

