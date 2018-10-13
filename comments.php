<?php
if($_SERVER['REQUEST_METHOD'] == 'POST') {
	exit;
}

function get_comments_post($id) {
	echo "<div class='comments_warp'>";
	$result = mysql_query("SELECT * FROM comments WHERE src = '$id' ORDER BY time DESC LIMIT 100");
	while($row = mysql_fetch_assoc($result)) {
		echo "<div class='comment_warp blue round'>";
		echo "<table style='width:100%'>";
		echo "<tr>";
		echo "<td><b>". $row['nick'] . "</b><br>" . mydate($row['time']) . "</td>";
		echo "<td><div class='message'>" . $row['comment'] . "</td>";
		echo "</tr>";
		echo "</table>";
		echo "</div>";
	}
	echo "</div>";
}

?>