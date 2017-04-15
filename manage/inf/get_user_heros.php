<?php
/**
 * @author 张昌彪
 * @模块 查询查看 -- 查询用户
 * @功能 查询用户武将列表
 * @参数 $uid int 用户的uid
 * @返回 array 武将列表
 *       如果为空就返回no data
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid))exit("param_not_exist");
	$ret = sql_fetch_rows("select * from sys_city_hero where uid='$uid' order by level desc");
	if(empty($ret))$ret = 'no data';
?>