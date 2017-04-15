<?php
/**
 * @author 张昌彪
 * @模块 游戏操作 -- 周末开服
 * @功能 开服
 * @return 成功返回 ‘start_succ’
 *         失败返回 ‘start_failed’
 */
if (!defined("MANAGE_INTERFACE"))
    exit;

sql_query("update mem_state set value=1 where state=2");

if (mysql_error()) {
    $ret = 'start_failed';
} else {
    $ret = 'start_succ';
}

?>