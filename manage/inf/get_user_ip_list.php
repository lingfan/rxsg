<?php
/**
 * @author 方鸿鹏
 * @模块 查询查看 --ip查询
 * @功能 如果查询时间为空，就查询最近3个月的登陆IP地址清单，否则通过玩家id，开始/结束时间查询登陆的ip信息
 * @参数 $uid 玩家id  $startTime查询的开始时间 $endTime查询的结束时间
 * @返回 
 * array(
 *  0=>array(
 *		uid=>'玩家id'
 *  	uname=>'君主名'
 *  	passport=>'帐号'
 *      time=>'登录时间'
 *  	sip=>'登陆ip'
 *  )
 * )
 */
    if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid))exit("param_not_exist");
	if (!isset($startTime)&&!isset($endTime)){
		$ret = sql_fetch_rows("select u.uid, u.name as uname, u.passport, from_unixtime(l.time) as time, l.sip 
							   from sys_user u, log_login l 
		                       where u.uid=l.uid and u.uid=$uid and l.time>=(unix_timestamp()-90*86400) and l.time<unix_timestamp() order by l.time desc;");
	}else{
		$ret = sql_fetch_rows("select u.uid, u.name as uname, u.passport, from_unixtime(l.time) as time, l.sip 
						       from sys_user u, log_login l 
	                           where u.uid=l.uid and u.uid=$uid and l.time>=unix_timestamp($startTime) and l.time<unix_timestamp($endTime)+86400 order by l.time desc;");
	}
	if(empty($ret))$ret = 'no data';

?>