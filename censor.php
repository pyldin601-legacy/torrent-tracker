<?php

header("Content-type: text/plain; charset=UTF-8");

if(empty($_GET['q']))
	exit();
else
	$qs = $_GET['q'];

include 'config.php';
include 'bricks/functions.php';

$no = array(
'Ну вот, опять... Множество девченок на стены лезут без секса, а ты дрочить собрался?',
'Ой, да ну. Мне стыдно такое искать в базе данных. Извини.',
'Данная информация доступна только для совершеннолетних. Подтвердите, что Вам уже исполнилось 18 лет. Для этого прислоните первую страницу паспорта к экрану монитора и подержите 30 секунд.',
'На нашем трекере секса нет!',
'%s? Не, не слышал...');

$badquery = false;

foreach($conf['stop_words'] as $word) {
	$khack = preg_split("/[\s\_\-\.\,\&]+/", strtolower($qs));
	foreach($khack as $me) {
		if($me == $word) {
			$badquery = true;
			break 2;
		}
	}
}

if($badquery)
	printf($no[rand(0, count($no)-1)], $qs);
else
	print 'OK';

?>