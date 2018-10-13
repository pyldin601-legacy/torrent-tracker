<?php

include 'config.php';
include 'bricks/functions.php';
include 'bricks/layout.php';

$errors = '';

if($_SERVER['REQUEST_METHOD'] = 'POST') {
	if(is_admin()) {
		foreach($_POST as $key => $val) {
			${"post_".$key} = $val;
			${"post_".$key."_sql"} = mysql_real_escape_string(trim($val));
		}
		if(isset($post_id, $post_title)) {
			$id = $post_id;
			if(!mysql_num_rows(mysql_query("SELECT * FROM description WHERE id = '${post_id_sql}'"))) $errors .= '<li>Нет такой раздачи</li>';
			if(!strlen($post_title)) $errors .= '<li>Не указано название раздачи</li>';
			if(empty($errors)) {
				mysql_query("UPDATE description SET info_text = '${post_title_sql}', info_description = '${post_desc_sql}', info_short = '${post_short_sql}', info_cover_url = '${post_cover_sql}' WHERE id = '${post_id_sql}'");
				$post_hash = mysql_result(mysql_query("SELECT info_hash FROM description WHERE id = '${post_id_sql}'"), 0, 0);

				if(file_exists(cover_path($post_hash, 'thumb_'))) 
					unlink(cover_path($post_hash, 'thumb_'));
				if(file_exists(cover_path($post_hash, 'full_')))
					unlink(cover_path($post_hash, 'full_'));

				header("Location: show.php?id=" . $post_id);
				exit;
			}
		}
	} else {
		$errors .= '<li>Недостаточно прав!</li>';
	}
}

if(empty($id))
	$id = isset($_GET['id']) ? mysql_real_escape_string($_GET['id']) : null;

if(!$id) 
	exit;

list($title, $descr, $short, $cover) = mysql_fetch_array(mysql_query("SELECT info_text, info_description, info_short, info_cover_url FROM description WHERE id = '$id'"));

$get_category = isset($_GET['category'], $category_hash[$_GET['category']]) ? $_GET['category'] : "all";

$category_array = array();
foreach($category_hash as $key => $value)
	array_push($category_array, "<span class='unsel round'><a href='.?category=$key'>$value</a></span>");

array_push($category_array, "<span class='unsel round'><a href='settings.php'>Настройки</a></span>");
$category = implode(" | ", $category_array);

show_header();

echo "<table class='toptable'><tr><td style='text-align:left; vertical-align:middle;'>$category</td><td style='text-align:right;'>".show_query()."</td></tr></table>";
echo $errors;
echo "<div class='capt'>Редактирование описания</div>";

echo "
<div class='editform'>
<form method='post'>
<table class='editclass'>
<colgroup><col style='width:100px;'/><col style='width:auto;'/></colgroup>
<input type='hidden' name='id' value='$id'>
<tr><td>Название:</td><td><input size='72' type='text' name='title' value='".htmlspecialchars($title, ENT_QUOTES)."' required></td></tr>
<tr><td>Обложка:</td><td><input size='72' type='text' name='cover' value='".htmlspecialchars($cover, ENT_QUOTES)."'></td></tr>
<tr><td>Короткое описание:</td><td><textarea class='itext_short' name='short'>".htmlspecialchars($short, ENT_QUOTES)."</textarea></td></tr>
<tr><td>Описание:</td><td><textarea class='itext' name='desc'>".htmlspecialchars($descr, ENT_QUOTES)."</textarea></td></tr>
<tr><td></td><td style='text-align:right'><input type='button' onclick='autof();' value='Автоформатирование'><input type='submit' value='Сохранить'></td></tr>
</table>
</form>
</div>
";


show_stats();
show_footer();

mysql_close($link);

exit;

/* Functions */

function words($str) {
	$reps = array('_', ",", " ", "-");
	foreach($reps as $rep) {
		$str = str_replace($rep, '%', $str);
	}
	return $str;
}



?>