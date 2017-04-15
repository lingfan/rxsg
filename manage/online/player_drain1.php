<?php
	//注册用户流失
	//返回
	if (!defined("MANAGE_INTERFACE")) exit;
	
	//计算开服起每周的注册人数uid列表
//	$first_week = sql_fetch_one_cell("select from_unixtime((floor(min(time)/86400/7)*86400*7),'%y-%m-%d') as week from log_login");
	$uids_all = sql_fetch_rows("select uid, from_unixtime((floor(regtime/86400/7)*86400*7),'%y-%m-%d') as week from sys_user where uid>1000 and regtime<>0 and regtime>unix_timestamp($startday) group by uid");
	if(empty($uids_all))exit;
	foreach($uids_all as $uid_row)
	{
		$uid_list[$uid_row['week']][]=$uid_row['uid'];
	
	}
	foreach($uid_list as $week=>$uid_list)
	{
		$uids = implode(',',$uid_list);
		$drain_list = sql_fetch_rows("select count(uid) as count,week from (select uid,from_unixtime((floor(time/86400/7)*86400*7),'%y-%m-%d') as week from log_login where uid in ($uids) and time>unix_timestamp($startday) group by week,uid) as p where p.week>=$week group by week");
		$ret[$week] = $drain_list;
	}
?>