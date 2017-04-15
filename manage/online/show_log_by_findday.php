<?php
	//玩家在线时间
	//参数find_day，某一日期
	//返回截止到find_day的在线时间
	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($find_day)){exit("param_not_exist");}
	
	$ret = sql_fetch_rows("select * from `log_user_".$find_day."` order by onlinetime desc","bloodwarlog");

?>