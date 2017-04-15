<?php
/**
 * @author 张昌彪
 * @模块 公告管理 -- 左下角滚动公告
 * @功能 删除该服务器上的左下角滚动公告
 * @参数 $delete_opts 1,2,3,4...需要删除的公告编码列表
 * @返回 string 
 *       删除成功返回 delete_succ
 *       删除失败返回 delete_failed
 */
if (!defined("MANAGE_INTERFACE"))
    exit;

if (!isset($delete_opts)) {
    exit("param_not_exist");
}

sql_query("delete from sys_activity where `id` in (" . implode(',', $delete_opts) .
    ")");
if (mysql_error()) {
    $ret = "delete_failed";
} else {
    $ret = "delete_succ";
}
?>
