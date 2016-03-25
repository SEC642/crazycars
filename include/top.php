<?php
function randomAlphaNum($length){
	$s = str_shuffle('!@#$%^&*(){}[]<>.?~`-_+=,/\|\'\":;abcdefghijklmnopqrstuvwxyz0123456789');
	return substr($s,0,$length);
} 


if (!defined('WEB_ROOT')) {
	exit;
}
header("X-Crazy-Cars-Tracker: " . base64_encode(randomAlphaNum(8)));
?>
<p>&nbsp;</p>
<p align="center"><a href="/"><img src="images/logo.jpg"></a></p>
<p>&nbsp;</p>
