<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($passport)) {
    exit("param_not_exist");
}
if (! isset($name)) {
    exit("param_not_exist");
}
$uid = sql_fetch_one_cell("select uid from sys_user where passport='$passport'");
if (empty($uid)) {
    $ret['message'] = '没有该玩家！';
    exit();
}
$tid = sql_fetch_one_cell("select id from cfg_task where name='$name'");
if (empty($uid)) {
    $ret['message'] = '系统没有该任务！';
    exit();
}
$task = sql_fetch_one("select * from sys_user_task where tid='$tid' and uid='$uid'");
if (empty($task)) {
    $ret['message'] = '玩家没有该任务！';
    exit();
}
$row = sql_fetch_one("select * from sys_attack_position where tid='$tid' and uid='$uid'");
if (!empty($row)) {
    sql_query("update sys_attack_position set state = 1 where tid='$tid' and uid='$uid'");
}else{
	sql_query("insert into sys_attack_position(uid,tid,state) values('$uid','$tid',1)");	
}
$ret['message'] = "修复成功";
?>
