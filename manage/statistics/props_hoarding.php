<?php
/**
 * @作者：张昌彪
 * @模块：产品数据 -- 道具囤积
 * @功能 道具拥有情况（除去声望低于3000的用户，剩余用户参与数据提取，并且这个条件要在后台有注明）
 * @返回：array(
 * 			gid => 道具id
 *			count => 道具数量
 *			)
 */
	if (!defined("MANAGE_INTERFACE")) exit;
	
	$ret[] = sql_fetch_rows("select g.gid, sum(g.count) as count from sys_goods g,sys_user u where u.prestige>3000 and u.uid = g.uid group by g.gid");
	$ret[] = sql_fetch_rows("select t.tid, sum(t.count) as count from sys_things t,sys_user u where u.prestige>3000 and u.uid = t.uid group by t.tid");
?>