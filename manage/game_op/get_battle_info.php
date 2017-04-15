<?php
	//返回开启的战场列表
	//参数列表：无
	//返回开启的战场
	//array[]:result
	if (!defined("MANAGE_INTERFACE")) exit;
	$ret[] = sql_fetch_rows("select id,level,maxpeople,state,from_unixtime(starttime) as starttime,from_unixtime(endtime) as endtime,
	from_unixtime(finishtime) as finishtime,minpeople from sys_user_battle_field where bid='2001' order by level");
		
	$ret[] = sql_fetch_rows("SELECT sys_user_battle_field.id,from_unixtime(sys_user_battle_field.starttime) as starttime,
	from_unixtime(sys_user_battle_field.endtime) as endtime,count(sys_user_battle_field.id) as num 
	FROM sys_user_battle_field Left Join sys_user_battle_state ON sys_user_battle_field.id = sys_user_battle_state.battlefieldid
	WHERE sys_user_battle_field.bid = '1001' AND sys_user_battle_field.state =0 group by sys_user_battle_field.id order by sys_user_battle_field.id");
	
	
	$result = sql_fetch_rows("select id,from_unixtime(starttime) as starttime,from_unixtime(endtime) as endtime,level,state 
	from sys_user_battle_field where bid='2001' and state='0'");

	if (!empty($result)){
		foreach ($result as &$resultvaule){
			$resultvaule['caonum'] = sql_fetch_one_cell("select count(*) from sys_user_battle_state where `battlefieldid`='$resultvaule[id]' and unionid='4'");
			$resultvaule['yuannum'] = sql_fetch_one_cell("select count(*) from sys_user_battle_state where `battlefieldid`='$resultvaule[id]' and unionid='3'");
		}
	}
	$ret[] = $result;
	

	
?>