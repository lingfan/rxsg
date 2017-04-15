<?php
/**
 * @author 张昌彪
 * @模块 游戏操作 -- 周末开服
 * @功能 判断该服务器的运行状态
 * @return 运行 返回
 */
if (!defined("MANAGE_INTERFACE"))
    exit;

$state = sql_fetch_one("select value from mem_state");
$ret = $state['value'];

?>