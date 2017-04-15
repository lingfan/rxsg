<?php
/**
 * @author 张昌彪
 * @模块 公告管理 -- 浮动公告
 * @功能 修改该服务器上的浮动公告
 * @参数 $data_list修改过的公告列表
 * @返回 string
 *       删除成功返回 delete_succ
 *       删除失败返回 delete_failed
 */
if (!defined("MANAGE_INTERFACE"))
    exit;

if (!isset($data_list)) {
    exit("param_not_exist");
}
$lists['msg']=html_entity_decode($lists['msg']);
foreach ($data_list as $lists)
    sql_query("update sys_inform set `msg`='$lists[msg]',`type`='$lists[type]',`color`='$lists[color]',`starttime`=UNIX_TIMESTAMP('$lists[starttime]'),`endtime`=UNIX_TIMESTAMP('$lists[endtime]'),`scrollcount`='$lists[scrollcount]',`inuse`='$lists[inuse]',`interval`='$lists[interval]' where id='$lists[id]'");
if (mysql_error()) {
    $ret = "delete_failed";
} else {
    $ret = "delete_succ";
}
?>
