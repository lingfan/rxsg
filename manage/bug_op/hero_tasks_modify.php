<?php
/**
 * @author 方鸿鹏
 * @method 修复玩家无名将专属任务
 * @param $uid 玩家uid  $tid 任务tid
 * @return 
 */

if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($uid)) {
    exit("param_not_exist");
}
if (! isset($tid)) {
    exit("param_not_exist");
}
if (! isset($hid)) {
    exit("param_not_exist");
}
$num = sql_fetch_one_cell("select count(*) from sys_user_task where uid = '$uid' and state=0 and tid in (select t.id from sys_city_hero h
	   left join  cfg_task t on h.npcid*10+20001=t.`group` where t.`id`>400000  and uid='$uid')");
if($num >= 3){
	$ret = '该用户已经有'.$num.'个名将专属任务，不能再开启。';
}
else{
	sql_query("insert into sys_user_task values ('$uid','$tid',0) on duplicate key update `state`=0");
	sql_query("update mem_user_schedule set last_release_hero = $hid where uid = $uid limit 1");
	$ret = '名将专属任务开启成功。';
}
?>