<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($name)){exit("param_not_exist");}
	if (!isset($content)){exit("param_not_exist");}
	if (!isset($time_now)){exit("param_not_exist");}
	if (!isset($uid)){exit("param_not_exist");}
	$ret=sql_fetch_one("select * from sys_user where uid='$uid'");
	sql_query("insert into adm_log (`adm_name`,`opration`,`opration_content`,`oprate_time`) values ('$name','apply_yuanbao','$content','$time_now')"); 
?>