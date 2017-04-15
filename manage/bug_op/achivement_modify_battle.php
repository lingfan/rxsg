<?php
/**
 * @author 方鸿鹏
 * @method 修复玩家跨服逐鹿中原无法完成的成就
 * @param $uid 用户id $aid 成就id
 * @return 
 *  error
 * 
 */


	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($uid)){exit("param_not_exist");}
	if (!isset($aid)){exit("param_not_exist");}
	
	$sql = "insert into sys_user_achivement(uid,achivement_id,time) values ($uid,$aid,unix_timestamp())";
	sql_insert($sql,"battlenet");
	$ret = mysql_error();
	
?>