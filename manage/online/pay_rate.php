<?php
/**
 * @author 张昌彪
 * @模块 运营数据 -- 付费比例
 * @功能 获得当前游戏服务器上付费比例的四个参数
 *  array[0] 服务器的总充值人数
 *	array[1] 付费比例1：服务器的活跃人数（声望30000以上）
 *  array[2] 付费比例2：服务器的建号人数
 *  array[3] 付费比例3：上线次数超过3次的玩家数
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	
	$ret[0] = sql_fetch_one_cell("select count(distinct passport)  from  pay_log");
	$ret[1] = sql_fetch_one_cell("select count(*) from sys_user where prestige>30000 and uid>1000");
	$ret[2] = sql_fetch_one_cell("select count(*) from sys_user where uid>1000");
	$ret[3] = sql_fetch_one_cell("select count(*) from (select count(uid) as count from log_login group by uid) as p where count>3");
?>