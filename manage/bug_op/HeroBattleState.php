<?php
if (! defined( "MANAGE_INTERFACE" ))
	exit();
$ret = sql_fetch_rows( "select name from sys_city_hero where state=3 and npcid>0 and hid not in (select hid from sys_troops) limit 100" );
?>