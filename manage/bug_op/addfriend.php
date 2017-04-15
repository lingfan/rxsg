<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($passport)) {
    exit("param_not_exist");
}
if (! isset($heroname)) {
    exit("param_not_exist");
}
if (! isset($count)) {
    exit("param_not_exist");
}
$uid = sql_fetch_one_cell("select uid from sys_user where passport='$passport'");
$hid = sql_fetch_one_cell("select hid from sys_city_hero where name='$heroname' and npcid>0");
if (empty($uid) || empty($hid)) {
    $ret = "用户或武将不存在";
} else {
	sql_query("update sys_lionize set friend = friend + '$count' where uid='$uid' and npcid='$hid' limit 1");
	$ret = "修复成功！";
}
?>