<?php
if (! defined("MANAGE_INTERFACE"))
    exit();
if (! isset($passport)) {
    exit("param_not_exist");
}
$uid = sql_fetch_one_cell("select uid from sys_user where passport='$passport'");
if (empty($uid)) {
    $ret['message'] = '没有该玩家！';
}
else{
    sql_query("delete from sys_user_battle_state where in_cross_battle=1 and uid='$uid' limit 1");  
    $ret['message'] = "修复成功"; 
}

?>
