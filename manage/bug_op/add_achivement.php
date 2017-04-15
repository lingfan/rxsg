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
    exit;
}
$achivement = sql_fetch_one("select * from cfg_achivement where name='$name'");
if (empty($uid)) {
    $ret['message'] = '没有该成就！';
    exit;
}
$log_achivement = sql_fetch_one_cell("select uid from sys_user_achivement where uid='$uid' and achivement_id='$achivement[id]'");
if (! empty($log_achivement)) {
    $ret['message'] = '玩家已完成该成就！';
    exit;
}
    sql_query("insert into sys_user_achivement(uid,achivement_id,`time`) value('$uid','$achivement[id]',UNIX_TIMESTAMP())");
    sql_query("update sys_user set achivement_count = achivement_count + 1,achivement_point = achivement_point + '$achivement[point]' where uid='$uid' limit 1");
    $ret['message'] = "修复成功";
?>
