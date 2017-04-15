<?php
/**
 * @模块：查询查看 -- 当前用户的荣誉记录 
 * @功能：查询当前当前用户的荣誉记录
 * @参数：uid
 * @返回：
 *    array荣誉记录
 */
	if (!defined("MANAGE_INTERFACE")) exit;	
	if (!isset($uid))exit("param_not_exist");
	$ret = sql_fetch_rows("select l.honour,from_unixtime(l.quittime) as `time`,c.name from log_battle_honour l left join cfg_battle_field c on c.id=l.battleid where uid='$uid'");
	
	if (empty($ret)){
		$ret = "no data";
	}

?>