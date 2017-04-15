<?php
//修复建筑相关的任务
//参数列表：无
//返回
//array[]:result
if (! defined("MANAGE_INTERFACE"))
    exit();
    $uid = sql_fetch_one_cell("select uid from sys_user where passport='$passport'");
	sql_query("insert into sys_user_goal (uid,gid) (select c.uid, g.id from cfg_task_goal g,sys_building b, sys_city c where c.uid='$uid' and c.cid=b.cid and b.bid=g.type and g.sort=6 and g.count<=b.level) on duplicate key update gid=gid");
	$ret['message'] = "任务修复成功"; 
?>