<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($name)){exit("param_not_exist");}

	$ret['data'] = sql_fetch_rows("select s.id as id,s.name as union_name,u.name as leader_name,s.member,s.rank,s.prestige from sys_union s left join sys_user u on (s.leader=u.uid) where s.name like '%$name%' limit 10");
	if(empty($ret['data']))$ret['message'] = 'no result';
?>