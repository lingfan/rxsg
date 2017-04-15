<?php
require_once 'server/config/key.php';	
foreach ($defaultPassArray as $key =>$value) {
	copy("swf/pass/src/$key.swf","swf/pass/".getswfname($value).".swf");
}
?>