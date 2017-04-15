<?php
	//获得玩家装备强化日志
	//参数列表：
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid))exit("param_not_exist");
	$ret = sql_fetch_rows("select l.* from log_armor_combine l where l.uid='$uid' order by l.time desc");
	if(empty($ret))$ret = 'no data';
?>