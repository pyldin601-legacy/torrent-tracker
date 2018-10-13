<HTML>
<BODY><CENTER>
<?php


echo urlencode(hex2bin($_GET['h']));

function hex2bin($h)
  {
    if (!is_string($h)) return null;
    $r='';
    for ($a=0; $a<strlen($h); $a+=2) { 
        $r.=chr(hexdec($h{$a}.$h{($a+1)})); 
     }
    return $r;
  }
 
?>
<BR><BR>
<FORM METHOD='GET'>
	<INPUT TYPE='text' NAME='h' SIZE='70' VALUE='<?php echo $_GET['h']; ?>'>
	<INPUT TYPE='submit' VALUE='GO'>
</FORM>
</CENTER></BODY>
</HTML>