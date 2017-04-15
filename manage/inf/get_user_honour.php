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
	$ret = sql_fetch_rows("select * from ((SELECT l.uid,c.name,-1*l.`count`*c.creditPrice as honour,from_unixtime(l.`time`) as `time` 
			FROM `cfg_shop` c,`log_goods` l where l.uid=$uid and (c.`group`=6 or c.battleshop=1) 
			and (l.type=2 or  l.type=10 or l.type=9 or l.type=11 or l.type=12) and c.gid=l.gid) 
	union 
			(SELECT log_battle_honour.uid,CONVERT(log_battle_honour.battleid using utf8) as name,
			log_battle_honour.honour,from_unixtime(log_battle_honour.starttime) AS `time` 
			FROM `log_battle_honour` where uid=$uid ORDER BY `time`)) as a order by time");
	
	if (empty($ret)){
		$ret = "no data";
	}

?>