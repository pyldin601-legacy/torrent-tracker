<?php
function bbcode($text, $mode) {
  $str_search = array(
  "#\n#is",
  "#\[center\](.+?)\[\/center\]#is",
  "#\[t\](.+?)\[\/t\]#is",
  "#\[p\](.+?)\[\/p\]#is",
  "#\[b\](.+?)\[\/b\]#is",
  "#\[i\](.+?)\[\/i\]#is",
  "#\[s\](.+?)\[\/s\]#is",
  "#\[u\](.+?)\[\/u\]#is",
  "#\[url=(.+?)\](.+?)\[\/url\]#is",
  "#\[url\](.+?)\[\/url\]#is",
  "#\[img\](.+?)\[\/img\]#is",
  "#\[size=(.+?)\](.+?)\[\/size\]#is",
  "#\[color=(.+?)\](.+?)\[\/color\]#is",
  "#\[list\](.+?)\[\/list\]#is",
  "#\[list=(1|a|I)\](.+?)\[\/list\]#is",
  "#\[\*\](.*)#",
  "#\[h(1|2|3|4|5|6)\](.+?)\[\/h(1|2|3|4|5|6)\]#is");
  $str_replace = array(
  "<br>",
  "<center>\\1</center>",
  "<span style='font-size:14pt'>\\1</span>",
  "<p>\\1</p>",
  "<strong>\\1</strong>",
  "<span style='font-style:italic'>\\1</span>",
  "<span style='text-decoration:line-through'>\\1</span>",
  "<span style='text-decoration:underline'>\\1</span>",
  "<a href='\\1'>\\2</a>",
  "<a href='\\1'>\\1</a>",
  "<img src='\\1' />",
  "<span style='font-size:\\1pt'>\\2</span>",
  "<span style='color:\\1'>\\2</span>",
  "<ul>\\1</ul>",
  "<ol type='\\1'>\\2</ol>",
  "<li>\\1</li>",
  "<h \\1>\\2</h>");
  if ($mode=="html")
  return preg_replace($str_search, $str_replace, $text);
  else
  return preg_replace($str_replace, $str_search, $text);
}

?>