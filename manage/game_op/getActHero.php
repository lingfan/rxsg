<?php
	if (!defined("MANAGE_INTERFACE")) exit;
	if(!isset($hero)){exit("param_not_exist");}
	$sql = "select b.npcid,a.name username,b.name heroname,b.cid,b.state from sys_city_hero b left join sys_user a on a.uid=b.uid where b.name='$hero' and b.npcid>0";
	$ret = sql_fetch_rows($sql);
?>