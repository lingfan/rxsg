<?php
/**
 * @author 方鸿鹏
 * @method 修复玩家跨服逐鹿中原无法完成的成就
 * @param $uid 用户id $aid 成就id
 * @return 
 * 
 */


	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($uid)){exit("param_not_exist");}
	if (!isset($aid)){exit("param_not_exist");}
	
	$point = sql_fetch_one_cell("select point from cfg_achivement where id=$aid");
	$sql = "insert into sys_user_achivement(uid,achivement_id,time) values ($uid,$aid,unix_timestamp())";
	sql_insert($sql);
	$sql_error = mysql_error();
	if(empty($sql_error)){
		sql_query("update sys_user set achivement_count = achivement_count + 1,achivement_point = achivement_point + '$point' where uid='$uid' limit 1");
		$ret = mysql_error();
	}
	else{
		$ret = $sql_error;
	}	
?>