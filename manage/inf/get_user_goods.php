<?php
/**
 * @author 张昌彪
 * @模块 查询查看 -- 查询用户
 * @功能 查询用户道具列表
 * @参数 $uid int 用户的uid
 * @返回 array 道具列表
 *       如果为空就返回no data
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid))exit("param_not_exist");
	$ret = sql_fetch_rows("select c.name as name,s.`count` as `count`,c.value as price from sys_goods s,cfg_goods c where s.gid=c.gid and s.uid='$uid'");
	if(empty($ret))$ret = 'no data';
?>