<?php
/**
 * @author 方鸿鹏
 * @模块 公告管理 -- 浮动公告
 * @功能 删除所有服务器上的浮动公告
 * @返回 string 
 *       删除成功返回 delete_succ
 *       删除失败返回 delete_failed
 */
if (!defined("MANAGE_INTERFACE"))
    exit;

if (!isset($del_content)) {
    exit("param_not_exist");
}

sql_query("delete from sys_inform where `msg` like '%$del_content%'");
if (mysql_error()) {
    $ret = "delete_failed";
} else {
    $ret = "delete_succ";
}
?>
