<?php
/**
 * @author 张昌彪
 * @模块 查询查看 -- 查询用户
 * @功能 查询用户战报列表
 * @参数 $uid int 用户的uid
 * @返回 array 战报列表
 *       如果为空就返回no data
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid))exit("param_not_exist");
	
	if(isset($title)&&isset($startday)&&isset($endday))
		$sql = "select `id`,`origincid`,`origincity`,`happencid`,`happencity`,`title`,`time` as `origin_time`,FROM_UNIXTIME(`time`) as `time` 
				from sys_report where uid='$uid' and title = '$title' and time >= unix_timestamp('$startday') 
				and time < unix_timestamp('$endday')+86400 order by `time` desc";
	else
		$sql = "select `id`,`origincid`,`origincity`,`happencid`,`happencity`,`title`,`time` as `origin_time`,FROM_UNIXTIME(`time`) as `time` 
				from sys_report where uid='$uid' order by `time` desc";
	$ret = sql_fetch_rows($sql);
	$sql_error = mysql_error();
	if(empty($ret)||!empty($sql_error))$ret = 'no data';
?>