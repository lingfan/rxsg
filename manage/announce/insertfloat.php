<?php
/**
 * @author 张昌彪
 * @模块 公告管理 -- 浮动公告
 * @功能 获得当前游戏服务器上的浮动公告列表
 * @参数 array $add_array 增加的一条浮动公告列表
 * @返回 string
 *       删除成功返回 插入的id
 *       删除失败返回 delete_failed
 */
if (!defined("MANAGE_INTERFACE"))
    exit;

if (!isset($add_array)) {
    exit("param_not_exist");
}
$add_array[0]=html_entity_decode($add_array[0]);
if (!empty($add_array[3])) {
    $id = sql_insert("insert into sys_inform set `msg`='$add_array[0]',`type`='$add_array[1]',`color`='$add_array[2]',`starttime`=UNIX_TIMESTAMP('$add_array[3]'),`endtime`=UNIX_TIMESTAMP('$add_array[4]'),`scrollcount`='$add_array[5]',`inuse`='$add_array[6]',`interval`='$add_array[7]'");
    if (mysql_error()) {
    $ret = "delete_failed";
} else {
    $ret = $id;
}
}

?>
