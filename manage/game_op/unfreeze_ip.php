<?php
	//查找禁用ip列表
	//参数列表：
	//返回
	//array[]:result
	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($uids)){exit("param_not_exist");}

	$ip_list = sql_fetch_rows("select b.ip as ip from sys_sessions s, cfg_baned_ip b where s.uid in ($uids) and s.ip = b.ip");
	$_ips = array();
	foreach($ip_list as $ip)
	{
		array_push($_ips,$ip['ip']);
	}
	$ips = implode(',',$_ips);
	sql_query("delete from cfg_baned_ip where ip in ($ips)");

?>