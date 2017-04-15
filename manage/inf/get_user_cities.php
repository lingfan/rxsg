<?php
/**
 * @author 张昌彪
 * @模块 查询查看 -- 查询用户
 * @功能 查询用户城市详细信息列表
 * @参数 $uid int 用户的uid
 * @返回 array 城市详情
 *       如果为空就返回no data
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	if (!isset($uid))exit("param_not_exist");
	$ret = sql_fetch_rows("select c.cid as cid,c.name as name,c.type as type,c.state as state,people,gold,morale,complaint,food,wood,rock,iron,CONCAT(c.cid%1000,',',floor(c.cid/1000)) as position from sys_city c,mem_city_resource r where c.uid=$uid and c.cid=r.cid");
	if(empty($ret))$ret = 'no data';
?>