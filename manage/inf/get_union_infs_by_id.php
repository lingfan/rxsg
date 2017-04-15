<?php
	//获得君主联盟信息
	//参数列表：
	//id:联盟id
	//返回联盟信息
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($id))exit("param_not_exist");
	$ret = sql_fetch_rows("select s.id as id,s.name as union_name,u.name as leader_name,s.member,s.rank,s.prestige from sys_union s left join sys_user u on (s.leader=u.uid) where s.id='$id'");
	if(empty($ret))$ret = 'no data';
?>