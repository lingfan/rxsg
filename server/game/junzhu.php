<?php
require_once("./interface.php");
require_once("./utils.php");

$heros = sql_fetch_rows("select uid,hid,cid from sys_city_hero where herotype=1000");
foreach ($heros as $hero) {
	if (!sql_check("select 1 from sys_city where uid={$hero['uid']} and cid={$hero['cid']}")) {
		//echo print_r($hero,true)."\n";
		$cid=sql_fetch_one_cell("select cid from sys_city where uid={$hero['uid']} limit 1");
		if (empty($cid)) $cid=0; 
		sql_query("update sys_city_hero set cid=$cid where hid={$hero['hid']}");
	}
}



