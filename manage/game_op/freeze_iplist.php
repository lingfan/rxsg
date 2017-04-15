<?php
	//查找禁用ip列表
	//参数列表：
	//返回
	//array[]:result
	if (!defined("MANAGE_INTERFACE")) exit;


	$ret = sql_fetch_rows("select b.ip as ip, s.uid as uid, u.name as name, u.passport as passport from sys_sessions s, cfg_baned_ip b, sys_user u where s.ip = b.ip and s.uid = u.uid");
?>