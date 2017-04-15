<?php
	//修改联盟名
	//参数列表：
	//cid:cid
	//oldname:原名
	//newname:新名
	//返回
	//array[]:result
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($union_id)){exit("param_not_exist");}
	if (!isset($oldname)){exit("param_not_exist");}
	if (!isset($newname)){exit("param_not_exist");}
	$union_id = intval($union_id);
	$newname = addslashes($newname);
/*	$union = sql_fetch_one("select * from sys_union where id='$union_id'");
	if ($union['name'] != $oldname)
	{
		$ret[] = "联盟原名不对";
	}
	else
	{*/
		sql_query("update sys_union set name='$newname' where id='$union_id'");
		$ret[] = "联盟 [$oldname] 已经成功改名为 [$newname]";
//	}
	
?>