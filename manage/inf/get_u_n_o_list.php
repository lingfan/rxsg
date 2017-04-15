<?php
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($user_post))exit("param_not_exist");
	foreach($user_post as &$user)
	{
		$user['unionlist'] = sql_fetch_one_cell("select name from sys_union where id='$user[union_id]'");
    	$user['nobility'] = sql_fetch_one_cell("select name from cfg_nobility where id='$user[nobility]'");
    	$user['officepos'] = sql_fetch_one_cell("select name from cfg_office_pos where id='$user[officepos]'");
    	$user['timenow'] = sql_fetch_one_cell("select unix_timestamp()");
	}
	$ret = $user_post;
	if(empty($ret))$ret = 'no data';
?>