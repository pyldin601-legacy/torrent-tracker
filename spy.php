<?php
include 'config.php';
include 'bricks/functions.php';
?>
<HTML>
<HEAD>
	<STYLE>
		body { font-family:monospace; }
		span.error { color:red; }
	</STYLE>
</HEAD>
<?php
if(isset($_GET['hash'], $_GET['url'])) {
	$hash = (string) $_GET['hash'];
	$url =  (string) $_GET['url'];
	$sep = strpos($url, "?") == false ? '?' : '&';
	$url_string = "${url}${sep}uploaded=0&downloaded=0&left=0&corrupt=0&key=42E51071&compact=1&no_peer_id=1&peer_id=-UT3120-%95h%22%b6%05%e7%dc%fe%82%5c%14%a5&port=11000&info_hash=" . urlencode(hex2bin($hash));
	$html = file_get_contents($url_string);
	$answer = bdecode($html);
	if(isset($answer['failure reason'])) {
		$result = '<span class="error">' . $answer['failure reason'] . '</span>';
	} else {
		$result = "Complete: <b>" . $answer['complete'] . "</b><br>";
		$result .= "Incomplete: <b>" . $answer['incomplete'] . "</b><br>";
		$result .= "<br>Peers list:<br>";
		$peers = str_split($answer['peers'], 6);
		foreach($peers as $peer) {
			$host = unpack("Naddr/nport", $peer);
			$result .= "<li>" . long2ip($host['addr']) . ":" . $host['port'] . "</li>";
		}
	}
} else {
	$hash = '';
	$url = '';
	$result = '<span class="error">input parameters not set!</span>';
}
?>
<BODY>
	<DIV class="prompt">
		<FORM METHOD="GET">
			<TABLE>
				<TR><TD>info_hash</TD><TD><INPUT TYPE="text" NAME="hash" VALUE="<?php echo htmlspecialchars($hash, ENT_QUOTES); ?>" SIZE="80"></TD></TR>
				<TR><TD>announce_url:</TD><TD><INPUT TYPE="text" NAME="url" VALUE="<?php echo htmlspecialchars($url, ENT_QUOTES); ?>" SIZE="80"></TD></TR>
				<TR><TD COLSPAN="2" ALIGN="right"><INPUT TYPE="submit" VALUE="Spy!"></TD></TR>
				<TR><TD COLSPAN="2">result:<BR><BR><?php echo $result; ?></TD></TR>
			</TABLE>
		</FORM>
	</DIV>
</BODY>
</HTML>
