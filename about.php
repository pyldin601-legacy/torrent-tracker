<?php

include 'bricks/functions.php';
include 'bricks/layout.php';

$category_hash = array_combine(
	explode("|", "all|audio|video|images|iso|soft"), 
	explode("|", "Все|Аудио|Видео|Изображения|Образы дисков|Софт")
);
	
$get_category = isset($_GET['category'], $category_hash[$_GET['category']]) ? $_GET['category'] : "all";

$category_array = array();
foreach($category_hash as $key => $value) {
	array_push($category_array, "<span class='unsel round'><a href='.?category=$key'>".$value."</a></span>");
}
// custom menu
array_push($category_array, "<span class='unsel round'><a href='settings.php'>Настройки</a></span>");
array_push($category_array, "<span class='sel round'><a href='about.php'>О сервисе</a></span>");

$category = implode(" | ", $category_array);

show_header2();

echo "<table class='toptable'><tr><td style='text-align:left; vertical-align:middle;'>$category</td><td style='text-align:right;'><form action='.' method='get'>Поиск: ";
echo "<input type='hidden' name='category' value='all'><input required style='width:250px;' name='q' value=''><input type='submit' value='Искать'>";
echo "</form></td></tr></table>";

echo "<div class='topic'>";
?>
<div class='title'>Описание</div>
<p>Данный сервис представляет собой локальный микро битторрент трекер сети <b>StarNET</b>. Целью сервиса является поиск раздач, доступных для скачивания внутри сети <b>StarNET</b>, а также увеличение скорости загрузки раздач, скачиваемых из Интернета путем нахождения локальных пиров. Вся информация о раздачах на трекере добавляется и обновляется автоматически в зависимости от активности на нашем локальном <a target='_blank' href='http://ru.wikipedia.org/wiki/%D0%A0%D0%B5%D1%82%D1%80%D0%B5%D0%BA%D0%B5%D1%80'>ретрекере</a>.</p>
<p>Чтобы раздача появилась в каталоге, необходимо внести в cписок трекеров в торрент-файле наш аннонсер <b>http://retracker.local/announce</b> и оставаться на раздаче. Раздача появится в каталоге в течении <b>5 минут</b>. Если в скачанном из Интернета торренте аннонсер <b>http://retracker.local/announce</b> прописан изначально, то ничего вносить не надо. Большинство из популярных торрент трекеров добавляют <b>http://retracker.local/announce</b> в свои торренты автоматически.</p>
<p>Раздачи хранятся в каталоге в течении <b>30 дней</b> с момента последней активности, после чего удаляются.</p>
<?php
echo "</div>";

show_stats();

mysql_close($link);

show_footer();

exit;

?>

