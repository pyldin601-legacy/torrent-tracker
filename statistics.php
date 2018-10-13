<?php
$link = mysql_connect("localhost", "service", "service");
mysql_select_db("retracker", $link);
mysql_query("SET names 'utf8'");

echo "<HTML><HEAD>";
echo "<META HTTP-EQUIV='CONTENT-TYPE' CONTENT='text/html; charset=UTF-8'>";
echo "<TITLE>Микро битторрент трекер StarNET (бета-версия)</TITLE>";
echo "<link href='favicon.ico' type='image/x-icon' rel='icon' />";
echo "<link href='favicon.ico' type='image/x-icon' rel='shortcut icon' />";
echo "<script src='js/jquery-1.7.1.min.js'></script>";
echo "<script src='js/retracker.js'></script>";
echo "<link href='style.css' rel='stylesheet' type='text/css'>";
echo "</HEAD><BODY>";
echo "</BODY></HTML>";

mysql_close($link);
?>