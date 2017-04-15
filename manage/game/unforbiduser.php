<?php
	//设置登录公告
	//参数列表：
	//passports:通行证列表
	//names:君主名列表
	//title:标题
	//content:内容
	//返回
	//array[]:results of send_sys_mail
	if (!defined("MANAGE_INTERFACE")) exit;
	function UnForbidUser($uid)
    {
		sql_query("update sys_user_state set forbistart=0,forbiend=0 where uid='$uid'");
		sql_query("update mem_city_resource set forbidden=0 where cid in (select cid from sys_city where uid='$uid')");
		return sql_fetch_one("select * from sys_user where uid='$uid'");
    }

	if (!isset($uids)){exit("param_not_exist");}
	
	$uids = explode(",",$uids);
	foreach($uids as $uid)
	{
		$user = UnForbidUser($uid);
		$ret[] = $user['passport']."[".$user['name']."]解除封禁成功。";
	}
?>