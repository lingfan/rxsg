<?php
/**
 * @author 张昌彪
 * @模块 运营数据 -- 在线人数
 * @功能 获得当前游戏服务器上，某个时间范围内的每日在线人数信息
 * @参数 type:1=>每日实时在线人数列表,2=>每日10分钟在线人数列表,3=>每日30分钟在线人数列表
 * 		 todaystart:开始日期
 * @返回 
 * array
 * 		(
 * 		'0'=>array(
 *					'formattime'=>'某日的时刻',
 *					'online'=>'在线人数',
 *					),
 * 		'1'=>array(
 *		  			'formattime'=>'某日的时刻',
 *					'online'=>'在线人数',
 *					),
 * ...
 * )
 */	 
	if (!defined("MANAGE_INTERFACE")) exit;
//     $ret[] = sql_fetch_one_cell("select now()");
//	 $ret[] =  sql_fetch_one_cell("select count(0) AS `count(*)` from `sys_online` where ((unix_timestamp() - `lastupdate`) < 60) "); 
//     $ret[] =  sql_fetch_one_cell("select count(0) AS `count(*)` from `sys_online` where ((unix_timestamp() - `lastupdate`) < 600) ");  
//     $ret[] =  sql_fetch_one_cell("select count(0) AS `count(*)` from `sys_online` where ((unix_timestamp() - `lastupdate`) < 1800) ");  
     
//	$unix_time = sql_fetch_one_cell("select unix_timestamp()");  
//	$time_between = ($unix_time+8*3600)%86400; 
//	$ret[] =  sql_fetch_one_cell("select count(0) AS `count(*)` from `sys_online` where ((unix_timestamp() - `lastupdate`) < $time_between) ");

	if (!isset($type)){exit("param_not_exist");}	
	if (!isset($todaystart)){exit("param_not_exist");}
	if ($type==1){
		$ret=sql_fetch_rows("select from_unixtime(time) as formattime,online from `log_online` where time>=$todaystart and time<$todaystart+86400 order by time desc","bloodwarlog");
	}
	elseif ($type==2){
		$ret=sql_fetch_rows("select from_unixtime(time) as formattime,online from `log_online10` where time>=$todaystart and time<$todaystart+86400 order by time desc","bloodwarlog");
	}
	elseif ($type==3){
		$ret=sql_fetch_rows("select from_unixtime(time) as formattime,online from `log_online30` where time>=$todaystart and time<$todaystart+86400 order by time desc","bloodwarlog");
	}

?>