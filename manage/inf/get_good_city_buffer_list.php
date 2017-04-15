<?php
/**
 * @author 方鸿鹏
 * @模块 查询查看 -- 查询用户--道具使用-使用中的道具（针对城池）
 * @功能 通过玩家id查询道具信息
 * @参数 $uid 玩家id
 * @返回 
 * array(
 *  0=>array(
 *		uid=>'玩家id'
 *  	uname=>'君主名'
 *  	passport=>'帐号'
 *      cname=>'城池名'
 *  	buftype=>'道具类型'
 *  	endtime=>'结束时间'
 *  	remaintime=>'剩余时间'
 *  )
 * )
 */
    if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid))exit("param_not_exist");
	$ret = sql_fetch_rows("select u.uid,u.name as uname,u.passport,c.name as cname,m.buftype,from_unixtime(endtime) as endtime,timediff(from_unixtime(endtime),from_unixtime(unix_timestamp())) as remaintime 
	                       from sys_user u,sys_city c,mem_city_buffer m 
	                       where u.uid=c.uid and c.cid=m.cid and u.uid='$uid';");
	if(empty($ret))$ret = 'no data';

?>