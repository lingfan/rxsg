<?php

	if (!defined("MANAGE_INTERFACE")) exit;
	
	if (!isset($op_result)){exit("param_not_exist");}
	if (!isset($name)){exit("param_not_exist");}	
	foreach($op_result as $result)
	{
		$ticket_content = $result[1];
		$ticket_name = $result[5];
		$serial_list = $result[2];
		$opration_content = $result[3];
		$tid = sql_fetch_one_cell("select `id` from sys_ticket_content where content='$ticket_content'");
		if(empty($tid))
		{
	        $tid = sql_insert("insert into sys_ticket_content (`content`,`name`) values ('$ticket_content','$ticket_name')");
	    }
		foreach($serial_list as $serial)
		{
			sql_query("insert into sys_ticket (`code`,`contentid`) values ('$serial','$tid')");
		}
		sql_query("insert into adm_log (`adm_name`,`opration`,`opration_content`,`oprate_time`) values ('$name','verify_ticket','$opration_content',unix_timestamp())");
		
	}
	$ret = 'success';
?>