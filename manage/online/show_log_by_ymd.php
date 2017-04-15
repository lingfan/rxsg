<?php
/**
 * @author 张昌彪
 * @模块 运营数据 -- 在线时间统计
 * @功能 获得当前游戏服务器上，某年某月某日的用户在线时间统计信息
 * @参数 yearnow:年
 * 		 monthnow:月
 *		 daynow:日
 * @返回 array()
 */	 



	if (!defined("MANAGE_INTERFACE")) exit;

	if (!isset($yearnow)){exit("param_not_exist");}
	if (!isset($monthnow)){exit("param_not_exist");}
	if (!isset($daynow)){exit("param_not_exist");}
	
	$ret = sql_fetch_rows("select * from `log_user_".$yearnow."-".$monthnow."-".$daynow."` order by onlinetime desc","bloodwarlog");
?>