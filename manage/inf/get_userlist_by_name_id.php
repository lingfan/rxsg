<?php
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($name))exit("param_not_exist");
	$ret = sql_fetch_rows("select u.nobility,u.officepos,u.uid as uid,name,passport,`group`,state,prestige,rank,union_id,money,lastupdate,lastcid,onlinetime from sys_sessions s,sys_online o,sys_user u where u.name='$name' and s.uid=u.uid and s.uid=o.uid");
	if(empty($ret))$ret = 'no data';
?>