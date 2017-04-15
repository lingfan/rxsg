<?php
/**
 * @author 张昌彪
 * @模块 公告管理 -- 左下角滚动公告
 * @功能 更新该服务器上的左下角滚动公告
 * @参数 $data_list 更新列表
 * @返回 string
 *       成功返回 delete_succ
 *       失败返回 delete_failed
 */
if (!defined("MANAGE_INTERFACE"))
    exit;

if (!isset($data_list)) {
    exit("param_not_exist");
}

foreach ($data_list as & $lists) {
	$ret[] = "update sys_activity set `inuse`='$lists[inuse]',`content`='$lists[content]',`link`='$lists[link]',`interval`='$lists[interval]' where id='$lists[id]'";
    sql_query("update sys_activity set `inuse`='$lists[inuse]',`content`='$lists[content]',`link`='$lists[link]',`interval`='$lists[interval]' where id='$lists[id]'");
}
if (mysql_error()) {
    $ret[] = "delete_failed";
} else {
    $ret[] = "delete_succ";
}

?>
