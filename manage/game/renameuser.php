<?php
	//修改君主名
	//参数列表：
	//uid:uid
	//oldname:原名
	//newname:新名
	//返回
	//array[]:result
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($uid)){exit("param_not_exist");}
	if (!isset($oldname)){exit("param_not_exist");}
	if (!isset($newname)){exit("param_not_exist");}
	$uid = intval($uid);
	$newname = addslashes($newname);
	$user = sql_fetch_one("select * from sys_user where uid='$uid'");
	if ($user['name'] != $oldname)
	{
		$ret[] = "玩家原名不对";
	}
	else
	{
		sql_query("update sys_user set name='$newname' where uid='$uid'");
		$ret[] = "玩家 [$oldname] 已经成功改名为 [$newname]";
	}
	
?>