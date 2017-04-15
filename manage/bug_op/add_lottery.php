<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($passport)) {
    exit("param_not_exist");
}

$uid = sql_fetch_one_cell("select uid from sys_user where passport='$passport'");
if (empty($uid)) {
    $ret['message'] = '没有该玩家！';
    exit;
}

$restart_count = sql_fetch_one_cell("select restart_count from mem_lottery_goods where uid='$uid'");
if (0 == $restart_count) {
    $ret['message'] = '状态正常！';
    exit;
}
    
    sql_query("update mem_lottery_goods set restart_count=0 where uid='$uid' and restart_count=1 limit 1");
    $ret['message'] = "修复成功";
?>
