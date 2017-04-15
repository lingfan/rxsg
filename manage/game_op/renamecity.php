<?php
	//修改城池名
	//参数列表：
	//cid:cid
	//oldname:原名
	//newname:新名
	//返回
	//array[]:result
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($cid)){exit("param_not_exist");}
	if (!isset($oldname)){exit("param_not_exist");}
	if (!isset($newname)){exit("param_not_exist");}
	$cid = intval($cid);
	$newname = addslashes($newname);
	$city = sql_fetch_one("select * from sys_city where cid='$cid'");
	if ($city['name'] != $oldname)
	{
		$ret[] = "城池原名不对";
	}
	else
	{
		sql_query("update sys_city set name='$newname' where cid='$cid'");
		$ret[] = "城池 [$oldname] 已经成功改名为 [$newname]";
	}
	
?>