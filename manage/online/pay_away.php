<?php
/**
 * @author 张昌彪
 * @模块 运营数据 -- 付费用户流失
 * @功能 获得当前游戏服务器上付费用户流失
 * @返回 付费用户流失量
 * string
 */
if (!defined("MANAGE_INTERFACE"))
    exit;

if (!isset($startday)) {
    exit("param_not_exist");
}
if (!isset($endday)) {
    exit("param_not_exist");
}

$all_count = sql_fetch_one_cell("select count(uid) from (select uid from log_money where time<unix_timestamp('$endday')  group by uid) a");
$login_count = sql_fetch_one_cell("");


?>