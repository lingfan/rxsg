<?php
	//修改战场状态
	//参数：战场状态数组
	//返回是否正确执行
	//array[]:result
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($selection)){exit("param_not_exist");}
	if (!isset($adds)){exit("param_not_exist");}

	if (!empty($selection)){
		foreach ($selection as $select){
			if (isset($select['delete'])){
				$ret[] = $select['delete'];
				sql_query("delete from sys_user_battle_field where id='$select[delete]'");
			}
			else {
				sql_query("update sys_user_battle_field set state='$select[value]' where id='$select[id]' and state<>'$select[value]'");
			}
		}
	}
	if (!empty($adds)){

		sql_query("insert into sys_user_battle_field (`bid`,`createuid`,`level`,`maxpeople`,`state`,`type`,`minpeople`) 
			values ('2001','0','$adds[level]','$adds[maxpeople]','$adds[state]','1','$adds[minpeople]')");
	}
	$ret[]='right';

	
?>